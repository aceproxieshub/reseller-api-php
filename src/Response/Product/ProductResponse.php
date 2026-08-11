<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Product;

final readonly class ProductResponse
{
    /**
     * @var list<ProductDurationResponse>|null
     */
    public ?array $durations;

    /**
     * @param list<array{id: string, name: string, durationDays: int, price: float|int}>|null $durations
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $id,
        public string $type,
        public string $name,
        public array $options,
        public ?float $price,
        public mixed $addons,
        ?array $durations,
    ) {
        $this->durations = $durations === null
            ? null
            : array_map(
                static fn (array $duration): ProductDurationResponse => new ProductDurationResponse(
                    durationDays: $duration['durationDays'],
                    id: $duration['id'],
                    name: $duration['name'],
                    price: $duration['price'],
                ),
                $durations,
            );
    }
}
