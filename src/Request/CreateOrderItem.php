<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request;

use InvalidArgumentException;

final readonly class CreateOrderItem
{
    /**
     * @param array<string, mixed> $addons
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $productId,
        public int $quantity,
        public ?string $durationId = null,
        public array $addons = [],
        public array $options = [],
    ) {
        if ($this->productId === '') {
            throw new InvalidArgumentException('The product ID must not be empty.');
        }

        if ($this->quantity < 1) {
            throw new InvalidArgumentException('The order item quantity must be greater than zero.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'productId' => $this->productId,
                'quantity' => $this->quantity,
                'durationId' => $this->durationId,
                'addons' => $this->addons,
                'options' => $this->options,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
