<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Services;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\ServiceListResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ServicesTest extends TestCase
{
    public function testListsServicesWithOptionalPagination(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services?page=2&limit=50',
                ServiceListResponse::class,
            )
            ->willReturn(new ServiceListResponse([], 50, 2));

        $result = (new Services($httpClient, 'https://example.test///'))->list(2, 50);

        self::assertSame(2, $result->page);
        self::assertSame(50, $result->limit);
    }

    public function testListsServicesWithoutPagination(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services',
                ServiceListResponse::class,
            )
            ->willReturn(new ServiceListResponse([], 50, 1));

        $result = (new Services($httpClient, 'https://example.test/'))->list();

        self::assertSame(1, $result->page);
    }

    public function testPaginationMustBePositive(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The page must be greater than zero.'));

        $services->list(0);
    }

    public function testLimitMustBePositive(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The limit must be greater than zero.'));

        $services->list(limit: 0);
    }
}
