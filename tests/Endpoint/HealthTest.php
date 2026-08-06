<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Endpoint;

use Aceproxies\ResellerApi\Client;
use Aceproxies\ResellerApi\Endpoint\Health;
use Aceproxies\ResellerApi\Endpoint\HealthInterface;
use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClient;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\HealthResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException as SymfonyTransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class HealthTest extends TestCase
{
    public function testHealthResponseIsMappedToDto(): void
    {
        $request = [];
        $requestOptions = '';
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$request, &$requestOptions): MockResponse {
            $request = ['method' => $method, 'url' => $url];
            $requestOptions = json_encode($options, JSON_THROW_ON_ERROR);

            return new MockResponse('{"data":{"status":"ok"}}');
        });

        $endpoint = (new Client('token', new HttpClient($httpClient), 'https://example.test/'))->health();
        $health = $endpoint->getHealth();

        self::assertInstanceOf(HealthResponse::class, $health);
        self::assertInstanceOf(HealthInterface::class, $endpoint);
        self::assertSame('ok', $health->status);
        self::assertSame('GET', $request['method']);
        self::assertSame('https://example.test/api/v1/health', $request['url']);
        self::assertStringContainsString('Accept: application\\/json', $requestOptions);
    }

    public function testBaseUrlWithTrailingSlashIsNormalized(): void
    {
        $request = [];
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$request): MockResponse {
            $request = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('{"data":{"status":"ok"}}');
        });

        (new Client('token', new HttpClient($httpClient), 'https://example.test///'))->health()->getHealth();

        self::assertSame('https://example.test/api/v1/health', $request['url']);
    }

    public function testApiErrorIncludesStatusMessageAndBody(): void
    {
        $body = '{"error":{"message":"Unavailable"}}';
        $httpClient = new MockHttpClient(static function () use ($body): MockResponse {
            return new MockResponse($body, ['http_code' => 503]);
        });

        $exception = null;
        try {
            (new Client('token', httpClient: new HttpClient($httpClient)))->health()->getHealth();
        } catch (ApiException $caught) {
            $exception = $caught;
        }

        self::assertInstanceOf(ApiException::class, $exception);
        self::assertSame(503, $exception->statusCode);
        self::assertSame('Unavailable', $exception->apiMessage);
        self::assertSame($body, $exception->body);
    }

    public function testInvalidPayloadThrowsInvalidResponseException(): void
    {
        $body = '{"data":{}}';
        $httpClient = new MockHttpClient(new MockResponse($body));

        $this->expectException(InvalidResponseException::class);
        (new Client('token', httpClient: new HttpClient($httpClient)))->health()->getHealth();
    }

    public function testTransportFailureThrowsTransportException(): void
    {
        $attempts = 0;
        $httpClient = new MockHttpClient(static function () use (&$attempts): never {
            $attempts++;
            throw new SymfonyTransportException('Connection failed.');
        });

        $exception = null;
        try {
            (new Client('token', httpClient: new HttpClient($httpClient)))->health()->getHealth();
        } catch (TransportException $caught) {
            $exception = $caught;
        }

        self::assertInstanceOf(TransportException::class, $exception);
        self::assertSame(3, $attempts);
    }

    public function testServerFailureIsRetriedBeforeSuccessfulResponse(): void
    {
        $attempts = 0;
        $httpClient = new MockHttpClient(static function () use (&$attempts): MockResponse {
            $attempts++;

            return $attempts === 1
                ? new MockResponse('', ['http_code' => 500])
                : new MockResponse('{"data":{"status":"ok"}}');
        });

        $health = (new Client('token', httpClient: new HttpClient($httpClient)))->health()->getHealth();

        self::assertSame('ok', $health->status);
        self::assertSame(2, $attempts);
    }

    public function testHealthAcceptsLibraryHttpClientInterface(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willReturn(new HealthResponse('ok'));

        $health = (new Health($httpClient, 'https://example.test/'))->getHealth();

        self::assertSame('ok', $health->status);
    }
}
