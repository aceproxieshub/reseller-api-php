<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExceptionTest extends TestCase
{
    public function testApiExceptionUsesApiMessageWhenProvided(): void
    {
        $exception = new ApiException(400, 'Bad request', 'body');

        self::assertSame('Bad request', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertSame(400, $exception->statusCode);
        self::assertSame('Bad request', $exception->apiMessage);
        self::assertSame('body', $exception->body);
    }

    public function testApiExceptionUsesDefaultMessageWhenApiMessageIsNull(): void
    {
        $exception = new ApiException(500, null, 'body');

        self::assertSame('The API returned an error response.', $exception->getMessage());
    }

    public function testInvalidResponseExceptionUsesExpectedMessageCodeAndPrevious(): void
    {
        $previous = new RuntimeException('previous');
        $exception = new InvalidResponseException(200, 'body', $previous);

        self::assertSame('The API returned an invalid response.', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testTransportExceptionUsesExpectedCodeAndPrevious(): void
    {
        $previous = new RuntimeException('previous');
        $exception = new TransportException('Connection failed.', $previous);

        self::assertSame('Connection failed.', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
