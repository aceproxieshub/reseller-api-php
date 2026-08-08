<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

use DateTimeImmutable;

final readonly class CreateOrderResponse
{
    public readonly DateTimeImmutable $createdAt;

    public function __construct(
        string $createdAt,
        public string $id,
        public string $status,
    ) {
        $this->createdAt = new DateTimeImmutable($createdAt);
    }
}
