<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class AuthResponse
{
    public function __construct(
        public string $method,
    ) {
    }
}
