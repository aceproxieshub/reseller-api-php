<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

final readonly class ProductDurationResponse
{
    public function __construct(
        public int $durationDays,
        public string $id,
        public string $name,
        public float $price,
    ) {
    }
}
