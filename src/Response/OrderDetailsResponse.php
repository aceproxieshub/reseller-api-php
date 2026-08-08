<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

use DateTimeImmutable;

final readonly class OrderDetailsResponse
{
    public DateTimeImmutable $createdAt;

    public MoneyResponse $total;

    /**
     * @param array{amount: float|int, currency: string} $total
     */
    public function __construct(
        string $createdAt,
        public string $description,
        public string $id,
        public bool $isRecurring,
        public string $status,
        array $total,
    ) {
        $this->createdAt = new DateTimeImmutable($createdAt);
        $this->total = new MoneyResponse($total['amount'], $total['currency']);
    }
}
