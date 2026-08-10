<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Request;

use Aceproxies\ResellerApi\Request\Service\CreateProlongationRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CreateProlongationRequestTest extends TestCase
{
    public function testSerializesPayload(): void
    {
        self::assertSame(
            ['durationId' => 'duration-1', 'quantity' => 2],
            (new CreateProlongationRequest('duration-1', 2))->toArray(),
        );
    }

    public function testRejectsEmptyDurationId(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The duration ID must not be empty.'));

        new CreateProlongationRequest('', 1);
    }

    public function testRejectsNonPositiveQuantity(): void
    {
        self::expectExceptionObject(new InvalidArgumentException('The quantity must be greater than zero.'));

        new CreateProlongationRequest('duration-1', 0);
    }
}
