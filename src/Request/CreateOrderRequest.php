<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request;

use InvalidArgumentException;

final readonly class CreateOrderRequest
{
    /**
     * @param list<CreateOrderItem> $items
     */
    public function __construct(public array $items)
    {
        if ($this->items === []) {
            throw new InvalidArgumentException('An order must contain at least one item.');
        }
    }

    /**
     * @return array{items: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(
                static fn (CreateOrderItem $item): array => $item->toArray(),
                $this->items,
            ),
        ];
    }
}
