<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Enum;

use Aceproxies\ResellerApi\Enum\RotationInterval;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RotationIntervalTest extends TestCase
{
    public function testBackedValues(): void
    {
        self::assertSame(
            ['all', 'high', '1min', '10min', '30min'],
            array_map(
                static fn (RotationInterval $rotationInterval): string => $rotationInterval->value,
                RotationInterval::cases(),
            ),
        );
    }

    #[DataProvider('unsupportedValueProvider')]
    public function testUnsupportedValueDoesNotResolve(string $value): void
    {
        self::assertNull(RotationInterval::tryFrom($value));
    }

    /**
     * @return iterable<array{string}>
     */
    public static function unsupportedValueProvider(): iterable
    {
        yield ['5min'];
    }
}
