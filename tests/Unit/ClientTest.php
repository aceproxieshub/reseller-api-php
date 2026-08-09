<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit;

use Aceproxies\ResellerApi\Client;
use Aceproxies\ResellerApi\Endpoint\BalanceInterface;
use Aceproxies\ResellerApi\Endpoint\HealthInterface;
use Aceproxies\ResellerApi\Endpoint\OrdersInterface;
use Aceproxies\ResellerApi\Endpoint\ProductsInterface;
use Aceproxies\ResellerApi\Endpoint\ResidentialInterface;
use Aceproxies\ResellerApi\Endpoint\ServicesInterface;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\BalanceResponse;
use Aceproxies\ResellerApi\Response\HealthResponse;
use Aceproxies\ResellerApi\Response\VersionResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testEmptyTokenThrowsInvalidArgumentException(): void
    {
        try {
            new Client('');
            self::fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The API token must not be empty.', $exception->getMessage());
        }
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
        $response = $health->getHealth();

        self::assertInstanceOf(HealthInterface::class, $health);
        self::assertSame('ok', $response->status);
    }

    public function testHealthPropagatesTransportException(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException(new TransportException('The HTTP request failed.'));

        try {
            (new Client('token', $httpClient, 'https://example.test/'))->health()->getHealth();
            self::fail('Expected TransportException.');
        } catch (TransportException $exception) {
            self::assertSame('The HTTP request failed.', $exception->getMessage());
        }
    }

    public function testBalanceReturnsBalanceResponse(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/balance',
                BalanceResponse::class,
            )
            ->willReturn(new BalanceResponse(18.59, 'USD'));

        $balance = (new Client('token', $httpClient, 'https://example.test/'))->balance();
        $response = $balance->getBalance();

        self::assertInstanceOf(BalanceInterface::class, $balance);
        self::assertSame(18.59, $response->balance);
    }

    public function testOrdersReturnsOrdersEndpoint(): void
    {
        $client = new Client('token', $this->createStub(HttpClientInterface::class), 'https://example.test/');

        self::assertInstanceOf(OrdersInterface::class, $client->orders());
    }

    public function testProductsReturnsProductsEndpoint(): void
    {
        $client = new Client('token', $this->createStub(HttpClientInterface::class), 'https://example.test/');

        self::assertInstanceOf(ProductsInterface::class, $client->products());
    }

    public function testServicesReturnsServicesEndpoint(): void
    {
        $client = new Client('token', $this->createStub(HttpClientInterface::class), 'https://example.test/');

        self::assertInstanceOf(ServicesInterface::class, $client->services());
    }

    public function testServicesReturnsResidentialEndpoint(): void
    {
        $client = new Client('token', $this->createStub(HttpClientInterface::class), 'https://example.test/');

        self::assertInstanceOf(ResidentialInterface::class, $client->services()->residential());
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

        $version = (new Client('token', $httpClient, 'https://example.test///'))->getApiVersion();

        self::assertSame('0.3.5+3509d25', $version);
    }

    public function testGetApiVersionPropagatesTransportException(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException(new TransportException('The HTTP request failed.'));

        try {
            (new Client('token', $httpClient))->getApiVersion();
            self::fail('Expected TransportException.');
        } catch (TransportException $exception) {
            self::assertSame('The HTTP request failed.', $exception->getMessage());
        }
    }
}
