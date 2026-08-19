<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Response\Balance\BalanceResponse;

final class BalanceApiTest extends StagingTestCase
{
    public function testBalanceEndpoint(): void
    {
        self::assertInstanceOf(BalanceResponse::class, $this->client->balance()->getBalance());
    }
}
