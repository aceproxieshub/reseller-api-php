<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class WhitelistedIpsResponse
{
    /**
     * @var list<WhitelistedIpResponse>
     */
    public array $items;

    /**
     * @param list<array{ip: string, description?: string|null}> $data
     */
    public function __construct(array $data)
    {
        $this->items = array_map(
            static fn (array $item): WhitelistedIpResponse => new WhitelistedIpResponse(
                ip: $item['ip'],
                description: $item['description'] ?? null,
            ),
            $data,
        );
    }
}
