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
     * @param list<array{id: string, countryId: int, proxyCount: int, rotationInterval: string, status: string, updatedAt: string, createdAt: string}> $data
     */
    public function __construct(array $data)
    {
        $this->items = array_map(
            static fn (array $item): ProxyRequestResponse => new ProxyRequestResponse(
                id: $item['id'],
                countryId: $item['countryId'],
                proxyCount: $item['proxyCount'],
                rotationInterval: $item['rotationInterval'],
                status: $item['status'],
                updatedAt: $item['updatedAt'],
                createdAt: $item['createdAt'],
            ),
            $data,
        );
    }
}
