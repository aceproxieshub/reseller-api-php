<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request\Service\Residential;

use Aceproxies\ResellerApi\Validation\Assert;
use InvalidArgumentException;

final readonly class CreateProxyRequest
{
    private const array ROTATION_INTERVALS = ['all', 'high', '1min', '10min', '30min'];

    public function __construct(
        public int $countryId,
        public int $proxyCount,
        public string $rotationInterval,
    ) {
        Assert::positiveInteger($this->countryId, 'country ID');
        Assert::positiveInteger($this->proxyCount, 'proxy count');

        if (!in_array($this->rotationInterval, self::ROTATION_INTERVALS, true)) {
            throw new InvalidArgumentException('The rotation interval must be one of: all, high, 1min, 10min, 30min.');
        }
    }

    /**
     * @return array{countryId: int, proxyCount: int, rotationInterval: string}
     */
    public function toArray(): array
    {
        return [
            'countryId' => $this->countryId,
            'proxyCount' => $this->proxyCount,
            'rotationInterval' => $this->rotationInterval,
        ];
    }
}
