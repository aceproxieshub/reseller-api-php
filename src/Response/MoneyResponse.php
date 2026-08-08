<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

final readonly class MoneyResponse
{
    public function __construct(
        public float $amount,
        public string $currency,
    ) {
    }
}
