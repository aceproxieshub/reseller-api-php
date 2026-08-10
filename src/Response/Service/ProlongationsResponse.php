<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class ProlongationsResponse
{
    /**
     * @var list<ProlongationResponse>
     */
    public array $items;

    /**
     * @param list<array{durationDays: int, durationId: string, name: string, price: float|int}> $data
     */
    public function __construct(array $data)
    {
        $this->items = array_map(
            static fn (array $item): ProlongationResponse => new ProlongationResponse(
                durationDays: $item['durationDays'],
                durationId: $item['durationId'],
                name: $item['name'],
                price: $item['price'],
            ),
            $data,
        );
    }
}
