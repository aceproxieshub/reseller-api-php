<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

final readonly class ProductResponse
{
    /**
     * @var list<ProductDurationResponse>|null
     */
    public ?array $durations;

    /**
     * @param list<array{durationDays: int, id: string, name: string, price: float|int}>|null $durations
     * @param array<string, mixed> $options
     */
    public function __construct(
        public mixed $addons,
        ?array $durations,
        public string $id,
        public string $name,
        public array $options,
        public ?float $price,
        public string $type,
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
