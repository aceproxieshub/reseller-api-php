<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class WhitelistedIpResponse
{
    public function __construct(
        public string $ip,
        public ?string $description = null,
    ) {
    }
}
