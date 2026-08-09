<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

final readonly class ServiceAmountResponse
{
    public function __construct(
        public int $amount,
        public string $unit,
    ) {
    }
}
