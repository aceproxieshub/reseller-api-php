<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Response\Service\Residential\CountriesResponse;

final class ResidentialLocationsApiTest extends StagingTestCase
{
    public function testListsResidentialCountries(): void
    {
        $response = $this->client->services()->residential()->countries();

        self::assertInstanceOf(CountriesResponse::class, $response);
        self::assertNotEmpty($response->items);
    }
}
