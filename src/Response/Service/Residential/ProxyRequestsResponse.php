<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service\Residential;

final readonly class ProxyRequestsResponse
{
    /**
     * @var list<ProxyRequestResponse>
     */
    public array $items;

    /**
     * @param list<array{countryId: int, createdAt: string, id: string, proxyCount: int, rotationInterval: string, status: string, updatedAt: string}> $data
     */
    public function __construct(array $data)
    {
        $this->items = array_map(
            static fn (array $item): ProxyRequestResponse => new ProxyRequestResponse(
                countryId: $item['countryId'],
                createdAt: $item['createdAt'],
                id: $item['id'],
                proxyCount: $item['proxyCount'],
                rotationInterval: $item['rotationInterval'],
                status: $item['status'],
                updatedAt: $item['updatedAt'],
            ),
            $data,
        );
    }
}
