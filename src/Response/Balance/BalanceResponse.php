<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Balance;

final readonly class BalanceResponse
{
    public function __construct(
        public float $balance,
        public string $currency,
    ) {
    }
}
