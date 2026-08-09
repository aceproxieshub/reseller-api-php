<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Residential;
use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\Service\Residential\CountriesResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\RotationIntervalsResponse;
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
                CountriesResponse::class,
            )
            ->willReturn(new CountriesResponse([
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
            ->willReturn(new CountriesResponse([]));

        self::assertSame([], (new Residential($httpClient, 'https://example.test'))->countries()->items);
    }

    public function testListsResidentialRotationIntervals(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/residential/rotation-intervals/',
                RotationIntervalsResponse::class,
            )
            ->willReturn(new RotationIntervalsResponse([
                'all' => 'All traffic',
                'high' => 'High rotation',
                '1min' => 'Every minute',
                '10min' => 'Every 10 minutes',
                '30min' => 'Every 30 minutes',
            ]));

        $response = (new Residential($httpClient, 'https://example.test///'))->rotationIntervals();

        self::assertSame('All traffic', $response->intervals['all']);
        self::assertSame('Every minute', $response->intervals['1min']);
        self::assertSame('Every 30 minutes', $response->intervals['30min']);
    }

    public function testPropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_UNAUTHORIZED, 'Unauthorized', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Residential($httpClient, 'https://example.test'))->rotationIntervals();
    }
}
