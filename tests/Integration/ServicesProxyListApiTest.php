<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Response\Service\ProxyListResponse;

final class ServicesProxyListApiTest extends StagingTestCase
{
    public function testGetsAServiceProxyList(): void
    {
        $result = $this->findService(
            fn (string $code): ProxyListResponse => $this->client->services()->getProxyList($code),
            'service proxy list',
        );

        self::assertInstanceOf(ProxyListResponse::class, $result['response']);
    }
}
