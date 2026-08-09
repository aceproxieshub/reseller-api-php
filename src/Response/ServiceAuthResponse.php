<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

final readonly class ServiceAuthResponse
{
    public function __construct(
        public string $method,
    ) {
    }
}
