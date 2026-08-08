<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Request;

use Aceproxies\ResellerApi\Request\CreateOrderItem;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CreateOrderItemTest extends TestCase
{
    public function testProductIdMustNotBeEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The product ID must not be empty.');

        new CreateOrderItem('', 1);
    }

    public function testOptionalDurationIsOmittedFromPayloadWhenUnset(): void
    {
        self::assertSame(
            [
                'productId' => 'product-1',
                'quantity' => 1,
                'addons' => [],
                'options' => [],
            ],
            (new CreateOrderItem('product-1', 1))->toArray(),
        );
    }

    public function testQuantityMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The order item quantity must be greater than zero.');

        new CreateOrderItem('product-1', 0);
    }
}
