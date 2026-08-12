<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Validation;

use Aceproxies\ResellerApi\Validation\Assert;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AssertTest extends TestCase
{
    public function testPositiveIntegerAcceptsPositiveValue(): void
    {
        Assert::positiveInteger(1, 'quantity');

        self::expectNotToPerformAssertions();
    }

    public function testPositiveIntegerRejectsZero(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The quantity must be greater than zero.'));

        Assert::positiveInteger(0, 'quantity');
    }

    public function testPositiveIntegerRejectsNegativeValue(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The quantity must be greater than zero.'));

        Assert::positiveInteger(-1, 'quantity');
    }

    public function testNonEmptyStringAcceptsValue(): void
    {
        Assert::nonEmptyString('value', 'property');

        self::expectNotToPerformAssertions();
    }

    public function testNonEmptyStringRejectsEmptyValue(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The property must not be empty.'));

        Assert::nonEmptyString('', 'property');
    }

    public function testNonEmptyStringRejectsWhitespaceOnlyValue(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The property must not be empty.'));

        Assert::nonEmptyString(" \t\n", 'property');
    }

    public function testIpAddressAcceptsIpv4AndIpv6(): void
    {
        Assert::ipAddress('192.0.2.1', 'IP address');
        Assert::ipAddress('2001:db8::1', 'IP address');

        self::expectNotToPerformAssertions();
    }

    public function testIpAddressRejectsInvalidValue(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The IP address must be a valid IP address.'));

        Assert::ipAddress('not-an-ip', 'IP address');
    }

    public function testNonEmptyArrayAcceptsValue(): void
    {
        Assert::nonEmptyArray(['value'], 'items');

        self::expectNotToPerformAssertions();
    }

    public function testNonEmptyArrayRejectsEmptyValue(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The items must contain at least one item.'));

        Assert::nonEmptyArray([], 'items');
    }
}
