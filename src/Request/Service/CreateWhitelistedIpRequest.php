<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request\Service;

use Aceproxies\ResellerApi\Validation\Assert;

final readonly class CreateWhitelistedIpRequest
{
    public function __construct(public string $ip)
    {
        Assert::ipAddress($this->ip, 'IP address');
    }

    /**
     * @return array{ip: string}
     */
    public function toArray(): array
    {
        return ['ip' => $this->ip];
    }
}
