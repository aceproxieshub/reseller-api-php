<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class ProlongationResponse
{
    public function __construct(
        public int $durationDays,
        public string $durationId,
        public string $name,
        public float|int $price,
    ) {
    }
}
