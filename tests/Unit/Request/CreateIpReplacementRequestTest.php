<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Request;

use Aceproxies\ResellerApi\Request\Service\CreateIpReplacementRequest;
use PHPUnit\Framework\TestCase;

final class CreateIpReplacementRequestTest extends TestCase
{
    public function testOmitsLocationsWhenNotProvided(): void
    {
        self::assertSame([], (new CreateIpReplacementRequest())->toArray());
    }

    public function testSerializesLocations(): void
    {
        self::assertSame(
            ['locations' => ['US', 'DE']],
            (new CreateIpReplacementRequest(['US', 'DE']))->toArray(),
        );
    }

    public function testSerializesAnExplicitlyEmptyLocationsList(): void
    {
        self::assertSame(['locations' => []], (new CreateIpReplacementRequest([]))->toArray());
    }
}
