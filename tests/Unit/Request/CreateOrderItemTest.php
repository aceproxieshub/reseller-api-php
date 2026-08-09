<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Request;

use Aceproxies\ResellerApi\Request\Order\CreateOrderItem;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CreateOrderItemTest extends TestCase
{
    public function testProductIdMustNotBeEmpty(): void
    {
        try {
            new CreateOrderItem('', 1);
            self::fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The product ID must not be empty.', $exception->getMessage());
        }
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
        try {
            new CreateOrderItem('product-1', 0);
            self::fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The order item quantity must be greater than zero.', $exception->getMessage());
        }
    }
}
