<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Residential;
use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\ResidentialCountriesResponse;
use PHPUnit\Framework\TestCase;

final class ResidentialTest extends TestCase
{
    public function testListsResidentialCountries(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/residential/countries',
                ResidentialCountriesResponse::class,
            )
            ->willReturn(new ResidentialCountriesResponse([
                [
                    'id' => 1,
                    'name' => 'United States',
                    'rotationIntervals' => ['all', '1min'],
                ],
            ]));

        $response = (new Residential($httpClient, 'https://example.test///'))->countries();

        self::assertCount(1, $response->items);
        self::assertSame(1, $response->items[0]->id);
        self::assertSame('United States', $response->items[0]->name);
        self::assertSame(['all', '1min'], $response->items[0]->rotationIntervals);
    }

    public function testListsEmptyResidentialCountries(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willReturn(new ResidentialCountriesResponse([]));

        self::assertSame([], (new Residential($httpClient, 'https://example.test'))->countries()->items);
    }

    public function testPropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_UNAUTHORIZED, 'Unauthorized', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Residential($httpClient, 'https://example.test'))->countries();
    }
}
