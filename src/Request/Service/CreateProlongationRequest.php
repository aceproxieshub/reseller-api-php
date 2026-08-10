<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request\Service;

use Aceproxies\ResellerApi\Validation\Assert;

final readonly class CreateProlongationRequest
{
    public function __construct(
        public string $durationId,
        public int $quantity,
    ) {
        Assert::nonEmptyString($this->durationId, 'duration ID');
        Assert::positiveInteger($this->quantity, 'quantity');
    }

    /**
     * @return array{durationId: string, quantity: int}
     */
    public function toArray(): array
    {
        return [
            'durationId' => $this->durationId,
            'quantity' => $this->quantity,
        ];
    }
}
