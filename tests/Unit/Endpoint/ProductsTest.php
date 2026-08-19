<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Products;
use Aceproxies\ResellerApi\Enum\ProductType;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\Product\ProductListResponse;
use Aceproxies\ResellerApi\Response\Product\ProductTypesResponse;
use InvalidArgumentException;
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

    public function testRequestsProductListFilteredByType(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/products?type=residential_proxy',
                ProductListResponse::class,
            )
            ->willReturn(new ProductListResponse([]));

        $result = (new Products($httpClient, 'https://example.test/'))->list(ProductType::ResidentialProxy);

        self::assertSame([], $result->items);
    }

    public function testEncodesProductTypeQueryParameter(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/products?type=dedicated_proxy',
                ProductListResponse::class,
            )
            ->willReturn(new ProductListResponse([]));

        $result = (new Products($httpClient, 'https://example.test/'))->list(ProductType::DedicatedProxy);

        self::assertSame([], $result->items);
    }

    public function testDeprecatedStringProductTypeIsSupported(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/products?type=mobile_proxy',
                ProductListResponse::class,
            )
            ->willReturn(new ProductListResponse([]));

        $this->expectUserDeprecationMessage(
            'Passing product or service types as strings is deprecated. Use ProductType enum cases instead.',
        );

        $result = (new Products($httpClient, 'https://example.test/'))->list('mobile_proxy');

        self::assertSame([], $result->items);
    }

    public function testRejectsUnsupportedProductType(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::never())->method('request');

        $this->expectUserDeprecationMessage(
            'Passing product or service types as strings is deprecated. Use ProductType enum cases instead.',
        );
        $this->expectExceptionObject(new InvalidArgumentException('Unsupported product or service type "unsupported".'));

        (new Products($httpClient, 'https://example.test/'))->list('unsupported');
    }

    public function testRejectsEmptyProductType(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::never())->method('request');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The product type must not be empty.');

        (new Products($httpClient, 'https://example.test/'))->list('  ');
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
