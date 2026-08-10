<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Residential;
use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Request\Service\Residential\CreateProxyRequest;
use Aceproxies\ResellerApi\Response\Service\Residential\CountriesResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestsResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\RotationIntervalsResponse;
use InvalidArgumentException;
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

    public function testListsResidentialProxyRequests(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/residential/service%2F1/proxy-requests',
                ProxyRequestsResponse::class,
            )
            ->willReturn(new ProxyRequestsResponse([
                [
                    'countryId' => 1,
                    'createdAt' => '2026-08-08T12:00:00+00:00',
                    'id' => 'request-1',
                    'proxyCount' => 10,
                    'rotationInterval' => 'all',
                    'status' => 'pending',
                    'updatedAt' => '2026-08-08T12:30:00+00:00',
                ],
            ]));

        $response = (new Residential($httpClient, 'https://example.test///'))->proxyRequests('service/1');

        self::assertCount(1, $response->items);
        self::assertSame('request-1', $response->items[0]->id);
        self::assertSame(1, $response->items[0]->countryId);
        self::assertSame(10, $response->items[0]->proxyCount);
        self::assertSame('pending', $response->items[0]->status);
        self::assertSame('+00:00', $response->items[0]->createdAt->getTimezone()->getName());
        self::assertSame('2026-08-08 12:30:00', $response->items[0]->updatedAt->format('Y-m-d H:i:s'));
    }

    public function testProxyRequestsRequireAServiceCode(): void
    {
        $residential = new Residential($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $residential->proxyRequests('');
    }

    public function testProxyRequestsPropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Residential($httpClient, 'https://example.test'))->proxyRequests('service-1');
    }

    public function testFindsResidentialProxyRequest(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/residential/service%2F1/proxy-requests/request%2F1',
                ProxyRequestResponse::class,
            )
            ->willReturn(new ProxyRequestResponse(
                countryId: 1,
                createdAt: '2026-08-08T12:00:00+00:00',
                id: 'request/1',
                proxyCount: 10,
                rotationInterval: 'all',
                status: 'pending',
                updatedAt: '2026-08-08T12:30:00+00:00',
            ));

        $response = (new Residential($httpClient, 'https://example.test///'))->findProxyRequest('service/1', 'request/1');

        self::assertNotNull($response);
        self::assertSame('request/1', $response->id);
        self::assertSame(1, $response->countryId);
        self::assertSame(10, $response->proxyCount);
        self::assertSame('pending', $response->status);
    }

    public function testFindProxyRequestRequiresAServiceCode(): void
    {
        $residential = new Residential($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $residential->findProxyRequest('', 'request-1');
    }

    public function testFindProxyRequestRequiresAProxyRequestId(): void
    {
        $residential = new Residential($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The proxy request ID must not be empty.'));

        $residential->findProxyRequest('service-1', '');
    }

    public function testFindProxyRequestReturnsNullWhenNotFound(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::assertNull((new Residential($httpClient, 'https://example.test'))->findProxyRequest('service-1', 'request-1'));
    }

    public function testFindProxyRequestPropagatesNonNotFoundApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_UNAUTHORIZED, 'Unauthorized', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Residential($httpClient, 'https://example.test'))->findProxyRequest('service-1', 'request-1');
    }

    public function testCreatesResidentialProxyRequest(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_POST,
                'https://example.test/api/v1/services/residential/service%2F1/proxy-requests',
                ProxyRequestResponse::class,
                [
                    'json' => [
                        'countryId' => 1,
                        'proxyCount' => 10,
                        'rotationInterval' => 'all',
                    ],
                ],
            )
            ->willReturn(new ProxyRequestResponse(
                countryId: 1,
                createdAt: '2026-08-08T12:00:00+00:00',
                id: 'request-1',
                proxyCount: 10,
                rotationInterval: 'all',
                status: 'pending',
                updatedAt: '2026-08-08T12:00:00+00:00',
            ));

        $response = (new Residential($httpClient, 'https://example.test///'))->createProxyRequest(
            'service/1',
            new CreateProxyRequest(1, 10, 'all'),
        );

        self::assertSame('request-1', $response->id);
        self::assertSame('pending', $response->status);
    }

    public function testCreateProxyRequestRequiresAServiceCode(): void
    {
        $residential = new Residential($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $residential->createProxyRequest('', new CreateProxyRequest(1, 10, 'all'));
    }

    public function testCreateProxyRequestPropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Residential($httpClient, 'https://example.test'))->createProxyRequest(
            'service-1',
            new CreateProxyRequest(1, 10, 'all'),
        );
    }

    public function testDeletesResidentialProxyRequest(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_DELETE,
                'https://example.test/api/v1/services/residential/service%2F1/proxy-requests/request%2F1',
                ProxyRequestResponse::class,
            )
            ->willReturn(new ProxyRequestResponse(
                countryId: 1,
                createdAt: '2026-08-08T12:00:00+00:00',
                id: 'request/1',
                proxyCount: 10,
                rotationInterval: 'all',
                status: 'deleted',
                updatedAt: '2026-08-08T12:30:00+00:00',
            ));

        $response = (new Residential($httpClient, 'https://example.test///'))->deleteProxyRequest('service/1', 'request/1');

        self::assertSame('request/1', $response->id);
        self::assertSame('deleted', $response->status);
    }

    public function testDeleteProxyRequestRequiresAServiceCode(): void
    {
        $residential = new Residential($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $residential->deleteProxyRequest('', 'request-1');
    }

    public function testDeleteProxyRequestRequiresAProxyRequestId(): void
    {
        $residential = new Residential($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The proxy request ID must not be empty.'));

        $residential->deleteProxyRequest('service-1', '');
    }

    public function testDeleteProxyRequestPropagatesNotFoundApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Residential($httpClient, 'https://example.test'))->deleteProxyRequest('service-1', 'request-1');
    }
}
