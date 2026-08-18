<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Response\Product\ProductListResponse;
use Aceproxies\ResellerApi\Response\Product\ProductTypesResponse;

final class ProductsApiTest extends StagingTestCase
{
    public function testListsProducts(): void
    {
        $response = $this->client->products()->list();

        self::assertInstanceOf(ProductListResponse::class, $response);
        self::assertNotEmpty($response->items);
    }

    public function testListsProductTypes(): void
    {
        $response = $this->client->products()->types();

        self::assertInstanceOf(ProductTypesResponse::class, $response);
        self::assertNotEmpty($response->types);
    }
}
