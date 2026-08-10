<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Request;

use Aceproxies\ResellerApi\Request\Service\CreateWhitelistedIpRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CreateWhitelistedIpRequestTest extends TestCase
{
    public function testSerializesPayload(): void
    {
        self::assertSame(
            ['ip' => '192.0.2.10'],
            (new CreateWhitelistedIpRequest('192.0.2.10'))->toArray(),
        );
    }

    public function testRejectsEmptyIp(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The IP address must not be empty.'));

        new CreateWhitelistedIpRequest('');
    }
}
