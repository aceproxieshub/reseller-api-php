<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class IpReplacementsResponse
{
    /**
     * @var list<IpReplacementResponse>
     */
    public array $items;

    /**
     * @param list<array{createdAt: string, replacedAt?: string|null, status: string, uuid: string}> $data
     */
    public function __construct(array $data)
    {
        $this->items = array_map(
            static fn (array $item): IpReplacementResponse => new IpReplacementResponse(
                createdAt: $item['createdAt'],
                replacedAt: $item['replacedAt'] ?? null,
                status: $item['status'],
                uuid: $item['uuid'],
            ),
            $data,
        );
    }
}
