<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class ServiceBandwidthResponse
{
    public function __construct(
        public float|int $available,
        public int $total,
        public string $unit,
        public float|int $used,
    ) {
    }
}
