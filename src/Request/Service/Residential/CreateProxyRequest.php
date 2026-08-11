<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request\Service\Residential;

use Aceproxies\ResellerApi\Enum\RotationInterval;
use Aceproxies\ResellerApi\Validation\Assert;

final readonly class CreateProxyRequest
{
    public function __construct(
        public int $countryId,
        public int $proxyCount,
        public RotationInterval $rotationInterval,
    ) {
        Assert::positiveInteger($this->countryId, 'country ID');
        Assert::positiveInteger($this->proxyCount, 'proxy count');
    }

    /**
     * @return array{countryId: int, proxyCount: int, rotationInterval: string}
     */
    public function toArray(): array
    {
        return [
            'countryId' => $this->countryId,
            'proxyCount' => $this->proxyCount,
            'rotationInterval' => $this->rotationInterval->value,
        ];
    }
}
