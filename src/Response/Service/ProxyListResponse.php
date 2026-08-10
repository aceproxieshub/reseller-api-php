<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class ProxyListResponse
{
    /**
     * @var list<ProxyResponse>
     */
    public array $items;

    /**
     * @param list<array{ip: string, password: string, port: int, username: string}> $data
     */
    public function __construct(array $data)
    {
        $this->items = array_map(
            static fn (array $item): ProxyResponse => new ProxyResponse(
                ip: $item['ip'],
                password: $item['password'],
                port: $item['port'],
                username: $item['username'],
            ),
            $data,
        );
    }
}
