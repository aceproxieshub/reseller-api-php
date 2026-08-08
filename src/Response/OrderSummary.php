<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

use DateTimeImmutable;

final readonly class OrderSummary
{
    public DateTimeImmutable $createdAt;

    public MoneyResponse $total;

    /**
     * @param array{amount: float|int, currency: string} $total
     */
    public function __construct(
        public string $id,
        public string $status,
        public string $description,
        array $total,
        string $createdAt,
    ) {
        $this->createdAt = new DateTimeImmutable($createdAt);
        $this->total = new MoneyResponse($total['amount'], $total['currency']);
    }
}
