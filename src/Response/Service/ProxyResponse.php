<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class ProxyResponse
{
    public function __construct(
        public string $ip,
        public string $password,
        public int $port,
        public string $username,
    ) {
    }
}
