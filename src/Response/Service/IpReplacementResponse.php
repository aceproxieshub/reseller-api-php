<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

use DateTimeImmutable;

final readonly class IpReplacementResponse
{
    public DateTimeImmutable $createdAt;

    public ?DateTimeImmutable $replacedAt;

    public function __construct(
        string $createdAt,
        ?string $replacedAt,
        public string $status,
        public string $uuid,
    ) {
        $this->createdAt = new DateTimeImmutable($createdAt);
        $this->replacedAt = $replacedAt === null ? null : new DateTimeImmutable($replacedAt);
    }
}
