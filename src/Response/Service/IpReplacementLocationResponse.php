<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class IpReplacementLocationResponse
{
    public function __construct(
        public string $country,
        public string $id,
        public string $location,
    ) {
    }
}
