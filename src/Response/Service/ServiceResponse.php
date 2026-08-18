<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

use DateTimeImmutable;

final readonly class ServiceResponse
{
    public ?AmountResponse $amount;

    public ?AuthResponse $auth;

    public ?DateTimeImmutable $createdAt;

    public ?DateTimeImmutable $startedAt;

    public ?DateTimeImmutable $expiredAt;

    /**
     * @param array{amount: int, unit: string}|null $amount
     * @param array{method: string}|null $auth
     */
    public function __construct(
        public string $code,
        public string $orderId,
        public string $status,
        public ?string $type = null,
        ?array $amount = null,
        ?array $auth = null,
        ?string $createdAt = null,
        ?string $startedAt = null,
        ?string $expiredAt = null,
    ) {
        $this->amount = $amount === null
            ? null
            : new AmountResponse($amount['amount'], $amount['unit']);
        $this->auth = $auth === null ? null : new AuthResponse($auth['method']);
        $this->createdAt = $createdAt === null ? null : new DateTimeImmutable($createdAt);
        $this->startedAt = $startedAt === null ? null : new DateTimeImmutable($startedAt);
        $this->expiredAt = $expiredAt === null ? null : new DateTimeImmutable($expiredAt);
    }
}
