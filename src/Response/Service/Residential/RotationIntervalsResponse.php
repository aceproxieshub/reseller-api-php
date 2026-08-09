<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service\Residential;

use Aceproxies\ResellerApi\Response\AssociativeDataResponseInterface;

final readonly class RotationIntervalsResponse implements AssociativeDataResponseInterface
{
    /**
     * @var array<string, string>
     */
    public array $intervals;

    /**
     * @param array<string, string> $data
     */
    public function __construct(array $data)
    {
        $this->intervals = $data;
    }
}
