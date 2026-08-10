<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service\Residential;

use Aceproxies\ResellerApi\Response\AssociativeDataResponseInterface;

final readonly class RotationIntervalsResponse implements AssociativeDataResponseInterface
{
    /**
     * @param array<string, string> $intervals
     */
    public function __construct(public array $intervals)
    {
    }
}
