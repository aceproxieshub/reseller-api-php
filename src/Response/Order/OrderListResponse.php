<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Order;

final readonly class OrderListResponse
{
    /**
     * @var list<OrderResponse>
     */
    public array $items;

    /**
     * @param list<array{createdAt: string, description: string, id: string, isRecurring: bool, status: string, total: array{amount: float|int, currency: string}}> $items
     */
    public function __construct(
        array $items,
        public int $limit,
        public int $page,
    ) {
        $this->items = array_map(
            static fn (array $item): OrderResponse => new OrderResponse(
                id: $item['id'],
                status: $item['status'],
                description: $item['description'],
                total: $item['total'],
                createdAt: $item['createdAt'],
                isRecurring: $item['isRecurring'],
            ),
            $items,
        );
    }
}
