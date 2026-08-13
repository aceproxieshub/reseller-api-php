<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Http;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClient;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\EmptyResponse;
use Aceproxies\ResellerApi\Response\Health\HealthResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\HttpClient\Exception\TransportException as SymfonyTransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HttpClientTest extends TestCase
{
    private ?SymfonyHttpClientInterface $client = null;

    private HttpClient $httpClient;

    /** @var list<int> */
    private array $delays = [];

    public function testSuccessfulRequestSerializesResponseAndAddsAcceptHeader(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client()
            ->expects(self::once())
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

    public function testSuccessfulEmptyResponseCanBeParsed(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '{"data":{}}');
        $this->client()->expects(self::once())->method('request')->willReturn($response);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_PATCH,
            url: 'https://example.test/services/service-1',
            responseClass: EmptyResponse::class,
        );

        self::assertInstanceOf(EmptyResponse::class, $result);
    }

    public function testDeleteMethodIsPassedToTransport(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client()
            ->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_DELETE,
                'https://example.test/resource/1',
                self::callback(static fn (array $options): bool => $options['headers'] === [
                    HttpClientInterface::HEADER_ACCEPT => HttpClientInterface::HEADER_ACCEPT_JSON,
                ]),
            )
            ->willReturn($response);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_DELETE,
            url: 'https://example.test/resource/1',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
    }

    public function testAuthenticatedRequestAddsBearerAuthorizationHeader(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client()
            ->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/health',
                self::callback(static function (array $options): bool {
                    return $options['headers'] === [
                        HttpClientInterface::HEADER_ACCEPT => HttpClientInterface::HEADER_ACCEPT_JSON,
                        HttpClientInterface::HEADER_AUTHORIZATION => 'Bearer api-token',
                    ];
                }),
            )
            ->willReturn($response);

        (new HttpClient($this->client(), token: 'api-token'))->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );
    }

    public function testSuccessfulRequestReadsResponseContentWithoutThrowing(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())->method('getStatusCode')->willReturn(HttpClientInterface::HTTP_OK);
        $response->expects(self::once())->method('getContent')->with(false)->willReturn('{"data":{"status":"ok"}}');
        $this->client()
            ->expects(self::once())
            ->method('request')
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
        $this->client()
            ->expects(self::once())
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
        $this->client()
            ->expects(self::once())
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
        $this->client()
            ->expects(self::once())
            ->method('request')
            ->willReturn($response);

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

    public function testStatusCode300IsAnApiError(): void
    {
        $body = '{"error":{"message":"Redirect"}}';
        $response = $this->response(300, $body);
        $this->client()
            ->expects(self::once())
            ->method('request')
            ->willReturn($response);

        try {
            $this->httpClient->request(
                method: HttpClientInterface::METHOD_GET,
                url: 'https://example.test/health',
                responseClass: HealthResponse::class,
            );
            self::fail('Expected ApiException.');
        } catch (ApiException $exception) {
            self::assertSame('Redirect', $exception->getMessage());
        }
    }

    public function testServerErrorIsRetriedAndCanSucceed(): void
    {
        $failed = $this->response(HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR, '', ['retry-after' => ['0']]);
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client()->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $successful);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
        self::assertSame([0], $this->delays);
    }

    public function testDefaultRetryDelayRetriesWithoutInjectedHooks(): void
    {
        $failed = $this->response(HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR, '');
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $client = $this->createMock(SymfonyHttpClientInterface::class);
        $client->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $successful);

        $result = (new HttpClient($client))->request(
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
        $this->client()->expects(self::exactly(3))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $failed, $successful);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
        self::assertSame([100_000, 200_000], $this->delays);
    }

    public function testServerErrorWithInvalidRetryAfterHeaderIsRetried(): void
    {
        $failed = $this->response(HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR, '', [HttpClientInterface::HEADER_RETRY_AFTER => ['invalid-date']]);
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client()->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $successful);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
        self::assertSame([100_000], $this->delays);
    }

    public function testServerErrorHonorsNumericRetryAfterHeader(): void
    {
        $failed = $this->response(
            HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR,
            '',
            [HttpClientInterface::HEADER_RETRY_AFTER => ['2']],
        );
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client()->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $successful);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
        self::assertSame([2_000_000], $this->delays);
    }

    public function testRetryAfterDelayIsBounded(): void
    {
        $failed = $this->response(
            HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR,
            '',
            [HttpClientInterface::HEADER_RETRY_AFTER => ['60']],
        );
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client()->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $successful);

        $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame([HttpClientInterface::MAX_RETRY_AFTER_MICROSECONDS], $this->delays);
    }

    public function testServerErrorWithDateRetryAfterHeaderIsRetried(): void
    {
        $retryAfter = gmdate('D, d M Y H:i:s \\G\\M\\T', time() - 1);
        $failed = $this->response(HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR, '', [HttpClientInterface::HEADER_RETRY_AFTER => [$retryAfter]]);
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client()->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($failed, $successful);

        $result = $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame('ok', $result->status);
        self::assertSame([0], $this->delays);
    }

    public function testRetryAfterUsesTheFirstHeaderValue(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(HttpClientInterface::HTTP_INTERNAL_SERVER_ERROR);
        $response->method('getContent')->willReturnCallback(
            static function (bool $throw): string {
                self::assertFalse($throw);

                return '';
            },
        );
        $response->expects(self::once())->method('getHeaders')->willReturnCallback(
            static function (bool $throw): array {
                self::assertFalse($throw);

                return [HttpClientInterface::HEADER_RETRY_AFTER => ['0', '1']];
            },
        );
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $this->client()->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($response, $successful);

        $this->httpClient->request(
            method: HttpClientInterface::METHOD_GET,
            url: 'https://example.test/health',
            responseClass: HealthResponse::class,
        );

        self::assertSame([0], $this->delays);
    }

    public function testNumericRetryAfterIsConvertedToMicroseconds(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '', [
            HttpClientInterface::HEADER_RETRY_AFTER => ['2'],
        ]);

        self::assertSame(2_000_000, $this->invokeRetryAfterMicroseconds($response));
    }

    public function testInvalidRetryAfterReturnsNull(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '', [
            HttpClientInterface::HEADER_RETRY_AFTER => ['not-a-date'],
        ]);

        self::assertNull($this->invokeRetryAfterMicroseconds($response));
    }

    public function testPastDateRetryAfterReturnsZeroMicroseconds(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '', [
            HttpClientInterface::HEADER_RETRY_AFTER => [gmdate('D, d M Y H:i:s \\G\\M\\T', time() - 1)],
        ]);

        self::assertSame(0, $this->invokeRetryAfterMicroseconds($response));
    }

    public function testFutureDateRetryAfterReturnsAPositiveMicrosecondDelay(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_OK, '', [
            HttpClientInterface::HEADER_RETRY_AFTER => [gmdate('D, d M Y H:i:s \\G\\M\\T', time() + 2)],
        ]);

        $delay = $this->invokeRetryAfterMicroseconds($response);

        self::assertGreaterThanOrEqual(1_000_000, $delay);
        self::assertLessThanOrEqual(2_000_000, $delay);
        self::assertSame(0, $delay % 1_000_000);
    }

    public function testErrorWithoutStringMessageUsesDefaultApiExceptionMessage(): void
    {
        $responses = array_map(
            fn (string $body): ResponseInterface => $this->response(HttpClientInterface::HTTP_UNAUTHORIZED, $body),
            ['{"error":"invalid"}', '{"error":{"message":123}}'],
        );
        $this->client()->expects(self::exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(...$responses);

        foreach (['{"error":"invalid"}', '{"error":{"message":123}}'] as $expectedBody) {
            try {
                $this->httpClient->request(
                    method: HttpClientInterface::METHOD_GET,
                    url: 'https://example.test/health',
                    responseClass: HealthResponse::class,
                );
                self::fail('Expected ApiException.');
            } catch (ApiException $exception) {
                self::assertNull($exception->apiMessage);
                self::assertSame($expectedBody, $exception->body);
                self::assertSame('The API returned an error response.', $exception->getMessage());
            }
        }
    }

    public function testMutatingServerErrorIsNotRetried(): void
    {
        $response = $this->response(
            HttpClientInterface::HTTP_SERVICE_UNAVAILABLE,
            '{"error":{"message":"Unavailable"}}',
        );
        $this->client()->expects(self::once())->method('request')->willReturn($response);

        try {
            $this->httpClient->request(
                method: HttpClientInterface::METHOD_POST,
                url: 'https://example.test/orders',
                responseClass: EmptyResponse::class,
            );
            self::fail('Expected ApiException.');
        } catch (ApiException) {
            self::assertSame([], $this->delays);
        }
    }

    public function testExhaustedServerErrorsThrowApiException(): void
    {
        $response = $this->response(HttpClientInterface::HTTP_SERVICE_UNAVAILABLE, '{"error":{"message":"Unavailable"}}', ['retry-after' => ['0']]);
        $this->client()->expects(self::exactly(HttpClientInterface::MAX_ATTEMPTS))
            ->method('request')
            ->willReturn($response);

        try {
            $this->httpClient->request(
                method: HttpClientInterface::METHOD_GET,
                url: 'https://example.test/health',
                responseClass: HealthResponse::class,
            );
            self::fail('Expected ApiException.');
        } catch (ApiException $exception) {
            self::assertSame('Unavailable', $exception->getMessage());
            self::assertSame([0, 0], $this->delays);
        }
    }

    public function testTransportFailureIsRetriedAndCanSucceed(): void
    {
        $successful = $this->response(HttpClientInterface::HTTP_OK, '{"data":{"status":"ok"}}');
        $attempts = 0;
        $this->client()->expects(self::exactly(2))
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
        self::assertSame([100_000], $this->delays);
    }

    public function testExhaustedTransportFailuresThrowTransportException(): void
    {
        $transportException = new SymfonyTransportException('Connection failed.');
        $this->client()->expects(self::exactly(HttpClientInterface::MAX_ATTEMPTS))
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
            self::assertSame($transportException, $exception->getPrevious());
            self::assertSame([100_000, 200_000], $this->delays);
        }
    }

    public function testMutatingTransportFailureIsNotRetried(): void
    {
        $transportException = new SymfonyTransportException('Connection failed.');
        $this->client()->expects(self::once())->method('request')->willThrowException($transportException);

        try {
            $this->httpClient->request(
                method: HttpClientInterface::METHOD_POST,
                url: 'https://example.test/orders',
                responseClass: EmptyResponse::class,
            );
            self::fail('Expected TransportException.');
        } catch (TransportException $exception) {
            self::assertSame($transportException, $exception->getPrevious());
            self::assertSame([], $this->delays);
        }
    }

    protected function setUp(): void
    {
        $this->delays = [];
        $this->httpClient = $this->httpClient($this->createStub(SymfonyHttpClientInterface::class));
    }

    private function client(): MockObject&SymfonyHttpClientInterface
    {
        if ($this->client === null) {
            $this->client = $this->createMock(SymfonyHttpClientInterface::class);
            $this->httpClient = $this->httpClient($this->client);
        }

        self::assertInstanceOf(MockObject::class, $this->client);

        return $this->client;
    }

    private function httpClient(SymfonyHttpClientInterface $client): HttpClient
    {
        return new HttpClient(
            $client,
            sleeper: function (int $delay): void {
                $this->delays[] = $delay;
            },
            jitter: static fn (int $delay): int => $delay,
        );
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

    private function invokeRetryAfterMicroseconds(ResponseInterface $response): ?int
    {
        $method = new ReflectionMethod(HttpClient::class, 'retryAfterMicroseconds');
        $httpClient = new HttpClient($this->createStub(SymfonyHttpClientInterface::class));

        $delay = $method->invoke($httpClient, $response);

        if ($delay !== null && !is_int($delay)) {
            throw new RuntimeException('Unexpected retry delay type.');
        }

        return $delay;
    }
}
