<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Product;

final readonly class ProductDurationResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public int $durationDays,
        public float $price,
    ) {
    }
}
