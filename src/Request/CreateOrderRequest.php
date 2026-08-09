<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request;

use Aceproxies\ResellerApi\Validation\Assert;

final readonly class CreateOrderRequest
{
    /**
     * @param list<CreateOrderItem> $items
     */
    public function __construct(public array $items)
    {
        Assert::nonEmptyArray($this->items, 'order items');
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
