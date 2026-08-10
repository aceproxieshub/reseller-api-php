<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class IpReplacementCountResponse
{
    public function __construct(public int $count)
    {
    }
}
