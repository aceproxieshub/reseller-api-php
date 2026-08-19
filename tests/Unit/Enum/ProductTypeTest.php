<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Enum;

use Aceproxies\ResellerApi\Enum\ProductType;
use PHPUnit\Framework\TestCase;

final class ProductTypeTest extends TestCase
{
    public function testBackedValues(): void
    {
        self::assertSame(
            [
                'dedicated_proxy',
                'residential_proxy',
                'payg_residential_proxy',
                'static_residential_proxy',
                'mobile_proxy',
            ],
            array_map(
                static fn (ProductType $productType): string => $productType->value,
                ProductType::cases(),
            ),
        );
    }

    public function testNormalizeReturnsEnumValue(): void
    {
        self::assertSame(ProductType::DedicatedProxy, ProductType::normalize(ProductType::DedicatedProxy));
    }

    public function testNormalizeRejectsUnsupportedString(): void
    {
        $this->expectUserDeprecationMessage(
            'Passing product or service types as strings is deprecated. Use ProductType enum cases instead.',
        );
        $this->expectExceptionMessage('Unsupported product or service type "unsupported".');

        ProductType::normalize('unsupported');
    }
}
