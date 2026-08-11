<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Request;

use Aceproxies\ResellerApi\Enum\Protocol;
use Aceproxies\ResellerApi\Request\Service\UpdateServiceAuthPayload;
use Aceproxies\ResellerApi\Request\Service\UpdateServiceRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UpdateServiceRequestTest extends TestCase
{
    public function testSerializesProtocolAndAuth(): void
    {
        $request = new UpdateServiceRequest(Protocol::http, new UpdateServiceAuthPayload('ip'));

        self::assertSame([
            'protocol' => 'http',
            'auth' => ['method' => 'ip'],
        ], $request->toArray());
    }

    public function testRejectsEmptyPatch(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The service update must contain at least one field.'));

        new UpdateServiceRequest();
    }

    public function testAuthMethodMustNotBeEmpty(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The service auth method must not be empty.'));

        new UpdateServiceAuthPayload('');
    }
}
