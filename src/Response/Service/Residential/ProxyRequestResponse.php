<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service\Residential;

use DateTimeImmutable;

final readonly class ProxyRequestResponse
{
    public DateTimeImmutable $createdAt;

    public DateTimeImmutable $updatedAt;

    public function __construct(
        public string $id,
        public int $countryId,
        public int $proxyCount,
        public string $rotationInterval,
        public string $status,
        string $updatedAt,
        string $createdAt,
    ) {
        $this->createdAt = new DateTimeImmutable($createdAt);
        $this->updatedAt = new DateTimeImmutable($updatedAt);
    }
}
