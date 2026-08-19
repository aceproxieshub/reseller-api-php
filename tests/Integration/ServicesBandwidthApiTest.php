<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Response\Service\BandwidthResponse;

final class ServicesBandwidthApiTest extends StagingTestCase
{
    public function testGetsServiceBandwidth(): void
    {
        $result = $this->findService(
            fn (string $code): ?BandwidthResponse => $this->client->services()->getBandwidth($code),
            'service bandwidth',
        );

        self::assertInstanceOf(BandwidthResponse::class, $result['response']);
    }
}
