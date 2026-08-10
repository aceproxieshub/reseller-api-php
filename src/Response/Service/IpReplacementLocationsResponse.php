<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

final readonly class IpReplacementLocationsResponse
{
    /**
     * @var list<IpReplacementLocationResponse>
     */
    public array $locations;

    /**
     * @param list<array{country: string, id: string, location: string}> $locations
     */
    public function __construct(array $locations)
    {
        $this->locations = array_map(
            static fn (array $location): IpReplacementLocationResponse => new IpReplacementLocationResponse(
                country: $location['country'],
                id: $location['id'],
                location: $location['location'],
            ),
            $locations,
        );
    }
}
