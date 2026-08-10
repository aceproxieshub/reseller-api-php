<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Request;

use Aceproxies\ResellerApi\Request\Service\UpdateCredentialsRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UpdateCredentialsRequestTest extends TestCase
{
    public function testSerializesPasswordOnlyPayload(): void
    {
        self::assertSame(
            ['password' => 'secret'],
            (new UpdateCredentialsRequest('secret'))->toArray(),
        );
    }

    public function testSerializesPasswordAndUsernamePayload(): void
    {
        self::assertSame(
            ['password' => 'secret', 'username' => 'proxy-user'],
            (new UpdateCredentialsRequest('secret', 'proxy-user'))->toArray(),
        );
    }

    public function testRejectsEmptyPassword(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The password must not be empty.'));

        new UpdateCredentialsRequest('');
    }
}
