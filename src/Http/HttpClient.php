<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Http;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Response\ResponseFactory;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class HttpClient implements HttpClientInterface
{
    public function __construct(
        private SymfonyHttpClientInterface $client,
        private ResponseFactory $responseFactory = new ResponseFactory(),
        private ?string $token = null,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function request(
        string $method,
        string $url,
        string $responseClass,
        array $options = [],
    ): object {
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

        for ($attempt = 1; $attempt <= HttpClientInterface::MAX_ATTEMPTS; $attempt++) {
            try {
                $response = $this->client->request($method, $url, $options);
                $statusCode = $response->getStatusCode();
                $body = $response->getContent(false);

                if ($this->shouldRetryStatus($statusCode) && $attempt < HttpClientInterface::MAX_ATTEMPTS) {
                    $this->waitBeforeRetry($attempt, $response);
                    continue;
                }
            } catch (TransportExceptionInterface) {
                $this->waitBeforeRetry($attempt);
                continue;
            }

            if ($statusCode < 200 || $statusCode >= 300) {
                throw $this->createApiException($statusCode, $body);
            }

            return $this->responseFactory->create($body, $responseClass, $statusCode);
        }

        throw new TransportException('The HTTP request failed.');
    }

    private function shouldRetryStatus(int $statusCode): bool
    {
        return $statusCode === HttpClientInterface::HTTP_TOO_MANY_REQUESTS
            || $statusCode >= HttpClientInterface::HTTP_SERVER_ERROR_MIN;
    }

    private function waitBeforeRetry(int $attempt, ?ResponseInterface $response = null): void
    {
        $delay = min(
            HttpClientInterface::INITIAL_BACKOFF_MICROSECONDS * (2 ** ($attempt - 1)),
            HttpClientInterface::MAX_BACKOFF_MICROSECONDS,
        );

        if ($response !== null) {
            $retryAfter = $this->retryAfterMicroseconds($response);

            if ($retryAfter !== null) {
                $delay = min($retryAfter, HttpClientInterface::MAX_BACKOFF_MICROSECONDS);
            }
        }

        usleep((int) $delay);
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
        $message = null;

        if (is_array($error) && isset($error['message']) && is_string($error['message'])) {
            $message = $error['message'];
        }

        return new ApiException($statusCode, $message, $body);
    }
}
