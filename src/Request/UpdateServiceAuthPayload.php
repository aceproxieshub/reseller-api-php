<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request;

use Aceproxies\ResellerApi\Validation\Assert;

final readonly class UpdateServiceAuthPayload
{
    public function __construct(public string $method)
    {
        Assert::nonEmptyString($this->method, 'service auth method');
    }

    /**
     * @return array{method: string}
     */
    public function toArray(): array
    {
        return ['method' => $this->method];
    }
}
