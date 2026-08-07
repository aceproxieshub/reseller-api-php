<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Response;

use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Response\HealthResponse;
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
