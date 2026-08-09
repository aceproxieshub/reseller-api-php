<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Response;

use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Response\HealthResponse;
use Aceproxies\ResellerApi\Response\OrderListResponse;
use Aceproxies\ResellerApi\Response\OrderResponse;
use Aceproxies\ResellerApi\Response\ProductListResponse;
use Aceproxies\ResellerApi\Response\ProductResponse;
use Aceproxies\ResellerApi\Response\ProductTypesResponse;
use Aceproxies\ResellerApi\Response\ResponseFactory;
use JsonException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ResponseFactoryTest extends TestCase
{
    private ResponseFactory $factory;

    public function testCreatesResponseFromDataEnvelope(): void
    {
        $response = $this->factory->create(
            '{"data":{"status":"ok"}}',
            HealthResponse::class,
            200,
        );

        self::assertSame('ok', $response->status);
    }

    public function testMalformedJsonThrowsInvalidResponseExceptionWithPreviousException(): void
    {
        try {
            $this->factory->create('{', HealthResponse::class, 200);
            self::fail('Expected InvalidResponseException.');
        } catch (InvalidResponseException $exception) {
            self::assertInstanceOf(JsonException::class, $exception->getPrevious());
            self::assertSame(200, $exception->statusCode);
            self::assertSame('{', $exception->body);
        }
    }

    public function testMissingDataThrowsInvalidResponseException(): void
    {
        try {
            $this->factory->create('{}', EmptyResponse::class, 200);
            self::fail('Expected InvalidResponseException.');
        } catch (InvalidResponseException $exception) {
            self::assertNull($exception->getPrevious());
            self::assertSame(200, $exception->statusCode);
            self::assertSame('{}', $exception->body);
        }
    }

    public function testMissingRequiredPropertyThrowsInvalidResponseException(): void
    {
        try {
            $this->factory->create('{"data":{}}', HealthResponse::class, 200);
            self::fail('Expected InvalidResponseException.');
        } catch (InvalidResponseException $exception) {
            self::assertSame(200, $exception->statusCode);
            self::assertNull($exception->getPrevious());
        }
    }

    public function testOptionalConstructorPropertyUsesDefaultValue(): void
    {
        $response = $this->factory->create(
            '{"data":{"status":"ok"}}',
            OptionalResponse::class,
            200,
        );

        self::assertSame('ok', $response->status);
        self::assertSame('default', $response->message);
    }

    public function testInvalidConstructorValueThrowsInvalidResponseExceptionWithPreviousException(): void
    {
        try {
            $this->factory->create(
                '{"data":{"status":"invalid"}}',
                ThrowingResponse::class,
                200,
            );
            self::fail('Expected InvalidResponseException.');
        } catch (InvalidResponseException $exception) {
            self::assertSame(200, $exception->statusCode);
            self::assertInstanceOf(RuntimeException::class, $exception->getPrevious());
        }
    }

    public function testCreatesObjectWithoutConstructor(): void
    {
        $response = $this->factory->create('{"data":{}}', EmptyResponse::class, 200);

        self::assertInstanceOf(EmptyResponse::class, $response);
    }

    public function testCreatesOrderListWithNestedTypedResponses(): void
    {
        $response = $this->factory->create(
            '{"data":{"items":[{"createdAt":"2026-08-08T12:00:00+00:00","description":"Order","id":"order-1","isRecurring":false,"status":"completed","total":{"amount":18.59,"currency":"USD"}}],"limit":25,"page":2}}',
            OrderListResponse::class,
            200,
        );

        self::assertCount(1, $response->items);
        self::assertSame('order-1', $response->items[0]->id);
        self::assertFalse($response->items[0]->isRecurring);
        self::assertSame(18.59, $response->items[0]->total->amount);
        self::assertSame('+00:00', $response->items[0]->createdAt->getTimezone()->getName());
    }

    public function testCreatesOrderDetailsWithNestedTypedResponse(): void
    {
        $response = $this->factory->create(
            '{"data":{"createdAt":"2026-08-08T12:00:00+00:00","description":"Order","id":"order-1","isRecurring":false,"status":"completed","total":{"amount":18.59,"currency":"USD"}}}',
            OrderResponse::class,
            200,
        );

        self::assertFalse($response->isRecurring);
        self::assertSame('USD', $response->total->currency);
    }

    public function testCreatesProductListWithNestedTypedResponses(): void
    {
        $response = $this->factory->create(
            '{"data":[{"addons":{"sticky":true},"durations":[{"durationDays":30,"id":"duration-1","name":"Monthly","price":18.59}],"id":"product-1","name":"Residential","options":{"country":"US"},"price":null,"type":"residential"},{"addons":[],"durations":null,"id":"product-2","name":"Datacenter","options":{},"price":5,"type":"datacenter"}]}',
            ProductListResponse::class,
            200,
        );

        self::assertCount(2, $response->items);
        self::assertInstanceOf(ProductResponse::class, $response->items[0]);
        self::assertSame('product-1', $response->items[0]->id);
        self::assertNull($response->items[0]->price);
        self::assertSame(['sticky' => true], $response->items[0]->addons);
        self::assertSame('US', $response->items[0]->options['country']);
        self::assertNotNull($response->items[0]->durations);
        self::assertSame(30, $response->items[0]->durations[0]->durationDays);
        self::assertSame(18.59, $response->items[0]->durations[0]->price);
        self::assertNull($response->items[1]->durations);
    }

    public function testCreatesProductTypesResponse(): void
    {
        $response = $this->factory->create(
            '{"data":{"types":["residential","datacenter"]}}',
            ProductTypesResponse::class,
            200,
        );

        self::assertSame(['residential', 'datacenter'], $response->types);
    }

    public function testMissingProductFieldThrowsInvalidResponseException(): void
    {
        try {
            $this->factory->create(
                '{"data":[{"addons":[],"durations":null,"id":"product-1","name":"Residential","options":{},"price":18.59,"type":[]}]}',
                ProductListResponse::class,
                200,
            );
            self::fail('Expected InvalidResponseException.');
        } catch (InvalidResponseException $exception) {
            self::assertSame(200, $exception->statusCode);
            self::assertNotNull($exception->getPrevious());
        }
    }

    public function testProductListRequiresArrayData(): void
    {
        try {
            $this->factory->create(
                '{"data":{"product-1":{"addons":[],"durations":null,"id":"product-1","name":"Residential","options":{},"price":18.59,"type":"residential"}}}',
                ProductListResponse::class,
                200,
            );
            self::fail('Expected InvalidResponseException.');
        } catch (InvalidResponseException $exception) {
            self::assertSame(200, $exception->statusCode);
            self::assertNull($exception->getPrevious());
        }
    }

    public function testSupportsJsonAtConfiguredMaximumDepth(): void
    {
        $body = '{"data":' . str_repeat('[', 510) . '1' . str_repeat(']', 510) . '}';

        self::assertInstanceOf(EmptyResponse::class, $this->factory->create($body, EmptyResponse::class, 200));
    }

    public function testRejectsJsonBeyondConfiguredMaximumDepth(): void
    {
        $body = '{"data":' . str_repeat('[', 511) . '1' . str_repeat(']', 511) . '}';

        try {
            $this->factory->create($body, EmptyResponse::class, 200);
            self::fail('Expected InvalidResponseException.');
        } catch (InvalidResponseException $exception) {
            self::assertInstanceOf(JsonException::class, $exception->getPrevious());
        }
    }

    protected function setUp(): void
    {
        $this->factory = new ResponseFactory();
    }
}
