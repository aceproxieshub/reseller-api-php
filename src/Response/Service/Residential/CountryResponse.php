<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service\Residential;

final readonly class CountryResponse
{
    /**
     * @param list<string> $rotationIntervals
     */
    public function __construct(
        public int $id,
        public string $name,
        public array $rotationIntervals,
    ) {
    }
}
