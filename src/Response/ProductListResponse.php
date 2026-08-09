<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

final readonly class ProductListResponse
{
    /**
     * @var list<ProductResponse>
     */
    public array $items;

    /**
     * @param list<array{addons: mixed, durations: list<array{durationDays: int, id: string, name: string, price: float|int}>|null, id: string, name: string, options: array<string, mixed>, price: float|int|null, type: string}> $data
     */
    public function __construct(array $data)
    {
        $this->items = array_map(
            static fn (array $product): ProductResponse => new ProductResponse(
                addons: $product['addons'],
                durations: $product['durations'],
                id: $product['id'],
                name: $product['name'],
                options: $product['options'],
                price: $product['price'],
                type: $product['type'],
            ),
            $data,
        );
    }
}
