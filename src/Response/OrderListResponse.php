<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

final readonly class OrderListResponse
{
    /**
     * @var list<OrderSummary>
     */
    public array $items;

    /**
     * @param list<array{createdAt: string, description: string, id: string, status: string, total: array{amount: float|int, currency: string}}> $items
     */
    public function __construct(
        array $items,
        public int $limit,
        public int $page,
    ) {
        $this->items = array_map(
            static fn (array $item): OrderSummary => new OrderSummary(
                createdAt: $item['createdAt'],
                description: $item['description'],
                id: $item['id'],
                status: $item['status'],
                total: $item['total'],
            ),
            $items,
        );
    }
}
