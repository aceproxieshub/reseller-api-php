<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

final readonly class VersionResponse
{
    public function __construct(
        public string $name,
        public string $version,
    ) {
    }
}
