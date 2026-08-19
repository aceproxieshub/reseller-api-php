<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Response\Health\HealthResponse;

final class HealthApiTest extends StagingTestCase
{
    public function testHealthEndpoint(): void
    {
        self::assertInstanceOf(HealthResponse::class, $this->client->health()->getHealth());
        self::assertNotSame('', $this->client->getApiVersion());
    }
}
