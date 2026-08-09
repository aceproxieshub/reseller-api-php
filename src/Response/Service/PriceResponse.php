<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class PriceResponse
{
    public function __construct(
        public float $amount,
        public string $currency,
    ) {
    }
}
