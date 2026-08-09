<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request\Order;

use Aceproxies\ResellerApi\Validation\Assert;

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
        Assert::nonEmptyString($this->productId, 'product ID');
        Assert::positiveInteger($this->quantity, 'order item quantity');
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
