<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class AmountResponse
{
    public function __construct(
        public int $amount,
        public string $unit,
    ) {
    }
}
