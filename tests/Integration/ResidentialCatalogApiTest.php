<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Response\Service\Residential\CountriesResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\RotationIntervalsResponse;

final class ResidentialCatalogApiTest extends StagingTestCase
{
    public function testListsResidentialCountries(): void
    {
        $response = $this->client->services()->residential()->countries();

        self::assertInstanceOf(CountriesResponse::class, $response);
        self::assertNotEmpty($response->items);
    }

    public function testListsResidentialRotationIntervals(): void
    {
        $response = $this->client->services()->residential()->rotationIntervals();

        self::assertInstanceOf(RotationIntervalsResponse::class, $response);
        self::assertNotEmpty($response->intervals);
    }
}
