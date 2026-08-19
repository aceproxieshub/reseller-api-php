<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Response\Service\Residential\RotationIntervalsResponse;

final class ResidentialRotationIntervalsApiTest extends StagingTestCase
{
    public function testListsResidentialRotationIntervals(): void
    {
        $response = $this->client->services()->residential()->rotationIntervals();

        self::assertInstanceOf(RotationIntervalsResponse::class, $response);
        self::assertNotEmpty($response->intervals);
    }
}
