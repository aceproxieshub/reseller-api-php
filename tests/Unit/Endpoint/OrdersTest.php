<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Orders;
use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Request\Order\CreateOrderItem;
use Aceproxies\ResellerApi\Request\Order\CreateOrderRequest;
use Aceproxies\ResellerApi\Response\Order\CreateOrderResponse;
use Aceproxies\ResellerApi\Response\Order\OrderListResponse;
use Aceproxies\ResellerApi\Response\Order\OrderResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OrdersTest extends TestCase
{
    public function testListsOrdersWithOptionalPagination(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/orders?page=1&limit=25',
                OrderListResponse::class,
            )
            ->willReturn(new OrderListResponse([], 25, 1));

        $result = (new Orders($httpClient, 'https://example.test///'))->list(1, 25);

        self::assertSame(1, $result->page);
        self::assertSame(25, $result->limit);
    }

    public function testListsTheLatestTenOrders(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/orders?limit=10',
                OrderListResponse::class,
            )
            ->willReturn(new OrderListResponse([], 10, 1));

        $result = (new Orders($httpClient, 'https://example.test/'))->list(limit: 10);

        self::assertSame(10, $result->limit);
    }

    public function testCreatesOrderWithJsonPayload(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_POST,
                'https://example.test/api/v1/orders',
                CreateOrderResponse::class,
                [
                    'json' => [
                        'items' => [[
                            'productId' => 'product-1',
                    'quantity' => 1,
                            'durationId' => 'duration-1',
                            'addons' => ['staticIp' => true],
                            'options' => ['country' => 'DE'],
                        ]],
                    ],
                ],
            )
            ->willReturn(new CreateOrderResponse('order-1', 'created', '2026-08-08T12:00:00+00:00'));

        $result = (new Orders($httpClient, 'https://example.test/'))->create(
            new CreateOrderRequest([
                new CreateOrderItem(
                    'product-1',
                    1,
                    'duration-1',
                    ['staticIp' => true],
                    ['country' => 'DE'],
                ),
            ]),
        );

        self::assertSame('order-1', $result->id);
    }

    public function testFindsOrderDetails(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/orders/order%2F1',
                OrderResponse::class,
            )
            ->willReturn(new OrderResponse(
                id: 'order/1',
                status: 'completed',
                description: 'Order description',
                total: ['amount' => 18.59, 'currency' => 'USD'],
                createdAt: '2026-08-08T12:00:00+00:00',
                isRecurring: false,
            ));

        $result = (new Orders($httpClient, 'https://example.test///'))->find('order/1');

        self::assertNotNull($result);
        self::assertSame('order/1', $result->id);
        self::assertSame(18.59, $result->total->amount);
    }

    public function testFindReturnsNullWhenOrderIsNotFound(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException(new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}'));

        $result = (new Orders($httpClient, 'https://example.test/'))->find('order-1');

        self::assertNull($result);
    }

    public function testFindPropagatesNonNotFoundApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_UNAUTHORIZED, 'Unauthorized', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Orders($httpClient, 'https://example.test/'))->find('order-1');
    }

    public function testPaginationMustBePositive(): void
    {
        $orders = new Orders($this->createStub(HttpClientInterface::class), 'https://example.test');

        try {
            $orders->list(0);
            self::fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The page must be greater than zero.', $exception->getMessage());
        }
    }

    public function testLimitMustBePositive(): void
    {
        $orders = new Orders($this->createStub(HttpClientInterface::class), 'https://example.test');

        try {
            $orders->list(limit: 0);
            self::fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The limit must be greater than zero.', $exception->getMessage());
        }
    }

    public function testOrderIdMustNotBeEmpty(): void
    {
        $orders = new Orders($this->createStub(HttpClientInterface::class), 'https://example.test');

        try {
            $orders->find('');
            self::fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The order ID must not be empty.', $exception->getMessage());
        }
    }
}
