<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Products;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\ProductListResponse;
use Aceproxies\ResellerApi\Response\ProductTypesResponse;
use PHPUnit\Framework\TestCase;

final class ProductsTest extends TestCase
{
    public function testRequestsProductList(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/products',
                ProductListResponse::class,
            )
            ->willReturn(new ProductListResponse([]));

        $result = (new Products($httpClient, 'https://example.test///'))->list();

        self::assertSame([], $result->items);
    }

    public function testRequestsProductTypes(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/products/types',
                ProductTypesResponse::class,
            )
            ->willReturn(new ProductTypesResponse(['residential', 'datacenter']));

        $result = (new Products($httpClient, 'https://example.test/'))->types();

        self::assertSame(['residential', 'datacenter'], $result->types);
    }
}
