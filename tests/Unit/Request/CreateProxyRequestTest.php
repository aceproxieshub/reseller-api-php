<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Request;

use Aceproxies\ResellerApi\Request\Service\Residential\CreateProxyRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CreateProxyRequestTest extends TestCase
{
    public function testSerializesPayload(): void
    {
        $request = new CreateProxyRequest(1, 10, 'all');

        self::assertSame([
            'countryId' => 1,
            'proxyCount' => 10,
            'rotationInterval' => 'all',
        ], $request->toArray());
    }

    public function testRejectsNonPositiveCountryId(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The country ID must be greater than zero.'));

        new CreateProxyRequest(0, 10, 'all');
    }

    public function testRejectsNonPositiveProxyCount(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The proxy count must be greater than zero.'));

        new CreateProxyRequest(1, 0, 'all');
    }

    public function testRejectsUnsupportedRotationInterval(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The rotation interval must be one of: all, high, 1min, 10min, 30min.'));

        new CreateProxyRequest(1, 10, '5min');
    }
}
