<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Enum;

use Aceproxies\ResellerApi\Enum\Protocol;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProtocolTest extends TestCase
{
    public function testBackedValues(): void
    {
        self::assertSame(
            ['socks5', 'http'],
            array_map(static fn (Protocol $protocol): string => $protocol->value, Protocol::cases()),
        );
    }

    #[DataProvider('unsupportedValueProvider')]
    public function testUnsupportedValueDoesNotResolve(string $value): void
    {
        self::assertNull(Protocol::tryFrom($value));
    }

    /**
     * @return iterable<array{string}>
     */
    public static function unsupportedValueProvider(): iterable
    {
        yield ['https'];
    }
}
