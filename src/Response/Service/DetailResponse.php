<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

use DateTimeImmutable;

final readonly class DetailResponse
{
    public AmountResponse $amount;

    public AuthResponse $auth;

    public DateTimeImmutable $createdAt;

    public ?DateTimeImmutable $startedAt;

    public ?DateTimeImmutable $expiresAt;

    public PriceResponse $price;

    /**
     * @param array{amount: int, unit: string} $amount
     * @param array{method: string} $auth
     * @param array{amount: float|int, currency: string} $price
     */
    public function __construct(
        array $amount,
        array $auth,
        public string $code,
        string $createdAt,
        ?string $startedAt,
        ?string $expiresAt,
        public bool $isRecurring,
        public int $orderId,
        public string $orderUuid,
        public string $protocol,
        array $price,
        public string $serviceType,
        public string $status,
        public string $userId,
    ) {
        $this->amount = new AmountResponse($amount['amount'], $amount['unit']);
        $this->auth = new AuthResponse($auth['method']);
        $this->createdAt = new DateTimeImmutable($createdAt);
        $this->startedAt = $startedAt === null ? null : new DateTimeImmutable($startedAt);
        $this->expiresAt = $expiresAt === null ? null : new DateTimeImmutable($expiresAt);
        $this->price = new PriceResponse($price['amount'], $price['currency']);
    }
}
