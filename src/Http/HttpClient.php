<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Http;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Response\EmptyResponse;
use Aceproxies\ResellerApi\Response\ResponseFactory;
use Closure;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class HttpClient implements HttpClientInterface
{
    private const float DEFAULT_IDLE_TIMEOUT_SECONDS = 10.0;
    private const float DEFAULT_MAX_DURATION_SECONDS = 30.0;

    /** @var Closure(int): void */
    private Closure $sleeper;

    /** @var Closure(int): int */
    private Closure $jitter;

    public function __construct(
        private SymfonyHttpClientInterface $client,
        private ResponseFactory $responseFactory = new ResponseFactory(),
        private ?string $token = null,
        ?Closure $sleeper = null,
        ?Closure $jitter = null,
    ) {
        $this->sleeper = $sleeper ?? static function (int $microseconds): void {
            usleep($microseconds);
        };
        $this->jitter = $jitter ?? static fn (int $delay): int => random_int((int) ($delay / 2), $delay);
    }

    /**
     * @template T of object
     * @param class-string<T> $responseClass
     * @param array<string, mixed> $options
     * @return ($responseClass is class-string<EmptyResponse> ? EmptyResponse : T)
     */
    public function request(
        string $method,
        string $url,
        string $responseClass,
        array $options = [],
    ): object {
        $options += [
            'timeout' => self::DEFAULT_IDLE_TIMEOUT_SECONDS,
            'max_duration' => self::DEFAULT_MAX_DURATION_SECONDS,
        ];

        $headers = $options['headers'] ?? [];

        if (!is_array($headers)) {
            $headers = [];
        }

        $defaultHeaders = [
            HttpClientInterface::HEADER_ACCEPT => HttpClientInterface::HEADER_ACCEPT_JSON,
        ];

        if ($this->token !== null) {
            $defaultHeaders[HttpClientInterface::HEADER_AUTHORIZATION] = 'Bearer ' . $this->token;
        }

        $options['headers'] = array_merge($defaultHeaders, $headers);


        $attempt = 1;

        while (true) {
            try {
                $response = $this->client->request($method, $url, $options);
                $statusCode = $response->getStatusCode();
                $body = $response->getContent(false);

                if ($this->shouldRetry($method, $statusCode, $attempt)) {
                    $this->waitBeforeRetry($attempt, $response);
                    $attempt++;
                    continue;
                }
            } catch (TransportExceptionInterface $exception) {
                if (!$this->canRetry($method, $attempt)) {
                    throw new TransportException('The HTTP request failed.', $exception);
                }

                $this->waitBeforeRetry($attempt);
                $attempt++;
                continue;
            }

            if ($statusCode < 200 || $statusCode >= 300) {
                throw $this->createApiException($statusCode, $body);
            }

            if ($responseClass === EmptyResponse::class) {
                return new EmptyResponse();
            }

            return $this->responseFactory->create($body, $responseClass, $statusCode);
        }
    }

    private function shouldRetryStatus(int $statusCode): bool
    {
        return $statusCode === HttpClientInterface::HTTP_TOO_MANY_REQUESTS
            || $statusCode >= HttpClientInterface::HTTP_SERVER_ERROR_MIN;
    }

    private function shouldRetry(string $method, int $statusCode, int $attempt): bool
    {
        return $this->canRetry($method, $attempt) && $this->shouldRetryStatus($statusCode);
    }

    private function canRetry(string $method, int $attempt): bool
    {
        return $method === HttpClientInterface::METHOD_GET
            && $attempt < HttpClientInterface::MAX_ATTEMPTS;
    }

    private function waitBeforeRetry(int $attempt, ?ResponseInterface $response = null): void
    {
        $shift = $attempt - 1;
        $delay = min(
            HttpClientInterface::INITIAL_BACKOFF_MICROSECONDS << $shift,
            HttpClientInterface::MAX_BACKOFF_MICROSECONDS,
        );

        if ($response !== null) {
            $retryAfter = $this->retryAfterMicroseconds($response);

            if ($retryAfter !== null) {
                $delay = min($retryAfter, HttpClientInterface::MAX_RETRY_AFTER_MICROSECONDS);
            } else {
                $delay = ($this->jitter)($delay);
            }
        } else {
            $delay = ($this->jitter)($delay);
        }

        ($this->sleeper)($delay);
    }

    private function retryAfterMicroseconds(ResponseInterface $response): ?int
    {
        $retryAfter = $response->getHeaders(false)[HttpClientInterface::HEADER_RETRY_AFTER][0] ?? null;

        if ($retryAfter === null) {
            return null;
        }

        if (ctype_digit($retryAfter)) {
            return (int) $retryAfter * 1_000_000;
        }

        $timestamp = strtotime($retryAfter);

        if ($timestamp === false) {
            return null;
        }

        return max(0, $timestamp - time()) * 1_000_000;
    }

    private function createApiException(int $statusCode, string $body): ApiException
    {
        $decoded = json_decode($body, true);
        $error = is_array($decoded) ? ($decoded['error'] ?? null) : null;
        $message = is_array($error) ? ($error['message'] ?? null) : null;

        return new ApiException($statusCode, is_string($message) ? $message : null, $body);
    }
}
