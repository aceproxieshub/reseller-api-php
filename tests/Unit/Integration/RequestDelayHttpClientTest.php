<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Integration;

use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Tests\Integration\RequestDelayHttpClient;
use PHPUnit\Framework\TestCase;
use stdClass;

final class RequestDelayHttpClientTest extends TestCase
{
    public function testItDelaysOnlyBetweenRequests(): void
    {
        $delegate = $this->createMock(HttpClientInterface::class);
        $delegate->expects(self::exactly(2))
            ->method('request')
            ->willReturn(new stdClass());
        $delays = [];
        $client = new RequestDelayHttpClient(
            $delegate,
            250_000,
            static function (int $delay) use (&$delays): void {
                $delays[] = $delay;
            },
        );

        $client->request(HttpClientInterface::METHOD_GET, 'https://example.test/one', stdClass::class);
        $client->request(HttpClientInterface::METHOD_GET, 'https://example.test/two', stdClass::class);

        self::assertSame([250_000], $delays);
    }
}
