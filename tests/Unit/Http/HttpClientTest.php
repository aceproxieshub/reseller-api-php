<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Http;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClient;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\HealthResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException as SymfonyTransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HttpClientTest extends TestCase
{
    private MockObject&SymfonyHttpClientInterface $client;

    private HttpClient $httpClient;

    public function testSuccessfulRequestSerializesResponseAndAddsAcceptHeader(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/health',
                self::callback(static function (array $options): bool {
                    return $options['headers'] === [
                        HttpClientInterface::HEADER_ACCEPT => HttpClientInterface::HEADER_ACCEPT_JSON,
                    ];
                }),
            )
            ->willReturn($response);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
    }

    public function testCallerHeadersArePreservedAndDefaultAcceptCanBeOverridden(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/health',
                self::callback(static function (array $options): bool {
                    return $options['headers'] === [
                        HttpClientInterface::HEADER_ACCEPT => 'text/plain',
                        'X-Test' => 'value',
                    ];
                }),
            )
            ->willReturn($response);

        $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
            options: ['headers' => [HttpClientInterface::HEADER_ACCEPT => 'text/plain', 'X-Test' => 'value']],
        );
    }

    public function testInvalidHeadersAreNormalizedToDefaultHeaders(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client->expects(self::once())
            ->method('request')
            ->with(HttpClientInterface::METHOD_GET, 'https://example.test/health', self::callback(static function (array $options): bool {
                return $options['headers'] === [
                    HttpClientInterface::HEADER_ACCEPT => HttpClientInterface::HEADER_ACCEPT_JSON,
                ];
            }))
            ->willReturn($response);

        $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
            options: ['headers' => 'invalid'],
        );
    }

    public function testClientErrorThrowsApiExceptionWithoutRetry(): void
    {
        $body = '{"error":{"message":"Unauthorized"}}';
        $response = $this->response(HttpClientInterface::HTTP_UNAUTHORIZED, $body);
        $this->client->expects(self::once())->method('request')->willReturn($response);

        try {
            $this->httpClient->request(
                method: HttpClientInterface::METHOD_GET,
                url: 'https://example.test/health',
                responseClass: HealthResponse::class,
            );
            self::fail('Expected ApiException.');
        } catch (ApiException $exception) {
            self::assertSame(HttpClientInterface::HTTP_UNAUTHORIZED, $exception->statusCode);
            self::assertSame('Unauthorized', $exception->apiMessage);
            self::assertSame($body, $exception->body);
        }
    }

    public function testServerErrorIsRetriedAndCanSucceed(): void
    {
        $failed = $this->response(HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR, '', ['retry-after' => ['0']]);
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $successful);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
    }

    public function testServerErrorWithoutRetryAfterHeaderIsRetried(): void
    {
        $failed = $this->response(HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR, '');
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $successful);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
    }

    public function testServerErrorWithInvalidRetryAfterHeaderIsRetried(): void
    {
        $failed = $this->response(HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR, '', [HttpClientInterface::HEADER_RETRY_AFTER => ['invalid-date']]);
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $successful);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
    }

    public function testServerErrorWithDateRetryAfterHeaderIsRetried(): void
    {
        $retryAfter = gmdate('D, d M Y H:i:s \\G\\M\\T', time() - 1);
        $failed = $this->response(HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR, '', [HttpClientInterface::HEADER_RETRY_AFTER => [$retryAfter]]);
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $successful);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
    }

    public function testExhaustedServerErrorsThrowApiException(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_SERVICE_UNAVAILABLE, '{"error":{"message":"Unavailable"}}', ['retry-after' => ['0']]);
        $this->client->expects(self::exactly(HttpClientInterface::MAX_ATTEMPTS))
            ->method('request')
            ->willReturn($response);

        $this->expectException(ApiException::class);
        $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );
    }

    public function testTransportFailureIsRetriedAndCanSucceed(): void
    {
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $attempts = 0;
        $this->client->expects(self::exactly(2))
            ->method('request')
            ->willReturnCallback(static function () use (&$attempts, $successful): ResponseInterface {
                $attempts++;

                if ($attempts === 1) {
                    throw new SymfonyTransportException('Connection failed.');
                }

                return $successful;
            });

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
    }

    public function testExhaustedTransportFailuresThrowTransportException(): void
    {
        $transportException = new SymfonyTransportException('Connection failed.');
        $this->client->expects(self::exactly(HttpClientInterface::MAX_ATTEMPTS))
            ->method('request')
            ->willThrowException($transportException);

        try {
            $this->httpClient->request(
                method: HttpClientInterface::METHOD_GET,
                url: 'https://example.test/health',
                responseClass: HealthResponse::class,
            );
            self::fail('Expected TransportException.');
        } catch (TransportException $exception) {
            self::assertSame('The HTTP request failed.', $exception->getMessage());
        }
    }

    protected function setUp(): void
    {
        $this->client = $this->createMock(SymfonyHttpClientInterface::class);
        $this->httpClient = new HttpClient($this->client);
    }

    /**
     * @param array<string, list<string>> $headers
     */
    private function response(int $statusCode, string $body, array $headers = []): ResponseInterface
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getContent')->willReturn($body);
        $response->method('getHeaders')->willReturn($headers);

        return $response;
    }
}
