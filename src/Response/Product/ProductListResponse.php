<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Product;

final readonly class ProductListResponse
{
    /**
     * @var list<ProductResponse>
     */
    public array $items;

    /**
     * @param list<array{id: string, name: string, type: string, options: array<string, mixed>, price: float|int|null, addons: mixed, durations: list<array{id: string, name: string, durationDays: int, price: float|int}>|null}> $data
     */
    public function __construct(array $data)
    {
        $this->items = array_map(
            static fn (array $product): ProductResponse => new ProductResponse(
                id: $product['id'],
                name: $product['name'],
                type: $product['type'],
                options: $product['options'],
                price: $product['price'],
                addons: $product['addons'],
                durations: $product['durations'],
            ),
            $data,
        );
    }
}
