<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Health;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\HealthResponse;
use PHPUnit\Framework\TestCase;

final class HealthTest extends TestCase
{
    public function testRequestsHealthResponseThroughLibraryHttpClient(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/health',
                HealthResponse::class,
            )
            ->willReturn(new HealthResponse('ok'));

        $result = (new Health($httpClient, 'https://example.test///'))->getHealth();

        self::assertSame('ok', $result->status);
    }
}
