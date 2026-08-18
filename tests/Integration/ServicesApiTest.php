<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Response\Service\DetailResponse;
use Aceproxies\ResellerApi\Response\Service\ListResponse;

final class ServicesApiTest extends StagingTestCase
{
    public function testListsServicesAndFindsASelectedService(): void
    {
        $services = $this->client->services()->list(limit: 100);

        self::assertInstanceOf(ListResponse::class, $services);
        $selected = $this->randomItem($services->items, 'services');
        $details = $this->client->services()->find($selected->code);

        self::assertInstanceOf(DetailResponse::class, $details);
        self::assertSame($selected->code, $details->code);
    }
}
