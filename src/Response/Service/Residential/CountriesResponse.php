<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service\Residential;

final readonly class CountriesResponse
{
    /**
     * @var list<CountryResponse>
     */
    public array $items;

    /**
     * @param list<array{id: int, name: string, rotationIntervals: list<string>}> $data
     */
    public function __construct(array $data)
    {
        $this->items = array_map(
            static fn (array $country): CountryResponse => new CountryResponse(
                id: $country['id'],
                name: $country['name'],
                rotationIntervals: $country['rotationIntervals'],
            ),
            $data,
        );
    }
}
