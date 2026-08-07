<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit;

use Aceproxies\ResellerApi\Client;
use Aceproxies\ResellerApi\Endpoint\HealthInterface;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\HealthResponse;
use Aceproxies\ResellerApi\Response\VersionResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testEmptyTokenThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The API token must not be empty.');

        new Client('');
    }

    public function testHealthReturnsHealthResponse(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/health',
                HealthResponse::class,
            )
            ->willReturn(new HealthResponse('ok'));

        $health = (new Client('token', $httpClient, 'https://example.test/'))->health();

        self::assertInstanceOf(HealthInterface::class, $health);
        self::assertSame('ok', $health->getHealth()->status);
    }

    public function testHealthPropagatesTransportException(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException(new TransportException('The HTTP request failed.'));

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('The HTTP request failed.');

        (new Client('token', $httpClient, 'https://example.test/'))->health()->getHealth();
    }

    public function testGetApiVersionReturnsVersion(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/version',
                VersionResponse::class,
            )
            ->willReturn(new VersionResponse('reseller-api', '0.3.5+3509d25'));

        self::assertSame(
            '0.3.5+3509d25',
            (new Client('token', $httpClient, 'https://example.test///'))->getApiVersion(),
        );
    }

    public function testGetApiVersionPropagatesTransportException(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException(new TransportException('The HTTP request failed.'));

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('The HTTP request failed.');

        (new Client('token', $httpClient))->getApiVersion();
    }
}
