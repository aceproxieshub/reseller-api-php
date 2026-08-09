<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class BandwidthResponse
{
    public ServiceBandwidthResponse $bandwidth;

    /**
     * @param array{available: float|int, total: int, unit: string, used: float|int} $bandwidth
     */
    public function __construct(array $bandwidth)
    {
        $this->bandwidth = new ServiceBandwidthResponse(
            available: $bandwidth['available'],
            total: $bandwidth['total'],
            unit: $bandwidth['unit'],
            used: $bandwidth['used'],
        );
    }
}
