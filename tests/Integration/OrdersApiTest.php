<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Response\Order\OrderListResponse;
use Aceproxies\ResellerApi\Response\Order\OrderResponse;

final class OrdersApiTest extends StagingTestCase
{
    public function testListsOrdersAndFindsASelectedOrder(): void
    {
        if ($this->fullMode()) {
            foreach ($this->orderTypes() as $type) {
                $created = $this->createOrder($type);
                self::assertNotSame('', $created->id);
            }
        }

        $orders = $this->client->orders()->list(limit: 5);

        self::assertInstanceOf(OrderListResponse::class, $orders);
        self::assertNotEmpty($orders->items);

        $selected = $this->randomItem($orders->items, 'orders');
        $details = $this->client->orders()->find($selected->id);

        self::assertInstanceOf(OrderResponse::class, $details);
        self::assertSame($selected->id, $details->id);
    }
}
