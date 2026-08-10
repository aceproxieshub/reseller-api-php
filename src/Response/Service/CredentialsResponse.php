<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class CredentialsResponse
{
    public function __construct(
        public string $username,
        public string $password,
    ) {
    }
}
