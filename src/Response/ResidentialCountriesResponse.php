<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

final readonly class ResidentialCountriesResponse
{
    /**
     * @var list<ResidentialCountryResponse>
     */
    public array $items;

    /**
     * @param list<array{id: int, name: string, rotationIntervals: list<string>}> $data
     */
    public function __construct(array $data)
    {
        $this->items = array_map(
            static fn (array $country): ResidentialCountryResponse => new ResidentialCountryResponse(
                id: $country['id'],
                name: $country['name'],
                rotationIntervals: $country['rotationIntervals'],
            ),
            $data,
        );
    }
}
