<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Balance;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\Balance\BalanceResponse;
use PHPUnit\Framework\TestCase;

final class BalanceTest extends TestCase
{
    public function testRequestsBalanceResponseThroughLibraryHttpClient(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/balance',
                BalanceResponse::class,
            )
            ->willReturn(new BalanceResponse(18.59, 'USD'));

        $result = (new Balance($httpClient, 'https://example.test///'))->getBalance();

        self::assertSame(18.59, $result->balance);
        self::assertSame('USD', $result->currency);
    }
}
