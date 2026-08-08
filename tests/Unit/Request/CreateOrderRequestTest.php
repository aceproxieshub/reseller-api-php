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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An order must contain at least one item.');

        new CreateOrderRequest([]);
    }
}
