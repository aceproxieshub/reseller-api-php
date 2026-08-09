<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service\Residential;

use DateTimeImmutable;

final readonly class ProxyRequestResponse
{
    public DateTimeImmutable $createdAt;

    public DateTimeImmutable $updatedAt;

    public function __construct(
        public int $countryId,
        string $createdAt,
        public string $id,
        public int $proxyCount,
        public string $rotationInterval,
        public string $status,
        string $updatedAt,
    ) {
        $this->createdAt = new DateTimeImmutable($createdAt);
        $this->updatedAt = new DateTimeImmutable($updatedAt);
    }
}
