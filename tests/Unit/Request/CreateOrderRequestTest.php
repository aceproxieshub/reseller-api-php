<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Request;

use Aceproxies\ResellerApi\Request\CreateOrderRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CreateOrderRequestTest extends TestCase
{
    public function testOrderMustContainAtLeastOneItem(): void
    {
        try {
            new CreateOrderRequest([]);
            self::fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('An order must contain at least one item.', $exception->getMessage());
        }
    }
}
