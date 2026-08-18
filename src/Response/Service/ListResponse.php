<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class ListResponse
{
    /**
     * @var list<ServiceResponse>
     */
    public array $items;

    /**
     * @param list<array{code: string, orderId: string, status: string, type?: string|null, amount?: array{amount: int, unit: string}|null, auth?: array{method: string}|null, createdAt?: string|null, startedAt?: string|null, expiredAt?: string|null}> $items
     */
    public function __construct(
        array $items,
        public int $limit,
        public int $page,
    ) {
        $this->items = array_map(
            static fn (array $item): ServiceResponse => new ServiceResponse(
                code: $item['code'],
                orderId: $item['orderId'],
                status: $item['status'],
                type: $item['type'] ?? null,
                amount: $item['amount'] ?? null,
                auth: $item['auth'] ?? null,
                createdAt: $item['createdAt'] ?? null,
                startedAt: $item['startedAt'] ?? null,
                expiredAt: $item['expiredAt'] ?? null,
            ),
            $items,
        );
    }
}
