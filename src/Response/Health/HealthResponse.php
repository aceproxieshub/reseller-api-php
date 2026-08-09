<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Health;

final readonly class HealthResponse
{
    public function __construct(
        public string $status,
    ) {
    }
}
