<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Validation;

use InvalidArgumentException;

final readonly class Assert
{
    private function __construct()
    {
    }

    public static function positiveInteger(int $value, string $propertyName): void
    {
        if ($value < 1) {
            throw new InvalidArgumentException('The ' . $propertyName . ' must be greater than zero.');
        }
    }

    public static function nonEmptyString(string $value, string $propertyName): void
    {
        if ($value === '') {
            throw new InvalidArgumentException('The ' . $propertyName . ' must not be empty.');
        }
    }

    /**
     * @param array<mixed> $value
     */
    public static function nonEmptyArray(array $value, string $propertyName): void
    {
        if ($value === []) {
            throw new InvalidArgumentException('The ' . $propertyName . ' must contain at least one item.');
        }
    }
}
