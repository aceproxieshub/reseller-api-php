<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Order;

use DateTimeImmutable;

final readonly class CreateOrderResponse
{
    public readonly DateTimeImmutable $createdAt;

    public function __construct(
        public string $id,
        public string $status,
        string $createdAt,
    ) {
        $this->createdAt = new DateTimeImmutable($createdAt);
    }
}
