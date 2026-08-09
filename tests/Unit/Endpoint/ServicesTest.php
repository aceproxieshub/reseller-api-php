<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Services;
use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Request\Service\UpdateServiceAuthPayload;
use Aceproxies\ResellerApi\Request\Service\UpdateServiceRequest;
use Aceproxies\ResellerApi\Response\EmptyResponse;
use Aceproxies\ResellerApi\Response\Service\BandwidthResponse;
use Aceproxies\ResellerApi\Response\Service\DetailResponse;
use Aceproxies\ResellerApi\Response\Service\ListResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ServicesTest extends TestCase
{
    public function testListsServicesWithOptionalPagination(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services?page=2&limit=50',
                ListResponse::class,
            )
            ->willReturn(new ListResponse([], 50, 2));

        $result = (new Services($httpClient, 'https://example.test///'))->list(2, 50);

        self::assertSame(2, $result->page);
        self::assertSame(50, $result->limit);
    }

    public function testListsServicesWithoutPagination(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services',
                ListResponse::class,
            )
            ->willReturn(new ListResponse([], 50, 1));

        $result = (new Services($httpClient, 'https://example.test/'))->list();

        self::assertSame(1, $result->page);
    }

    public function testPaginationMustBePositive(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The page must be greater than zero.'));

        $services->list(0);
    }

    public function testLimitMustBePositive(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The limit must be greater than zero.'));

        $services->list(limit: 0);
    }

    public function testFindsServiceDetails(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/service%2F1',
                DetailResponse::class,
            )
            ->willReturn($this->serviceDetails());

        $result = (new Services($httpClient, 'https://example.test///'))->find('service/1');

        self::assertNotNull($result);
        self::assertSame('service/1', $result->code);
        self::assertSame(850, $result->orderId);
    }

    public function testFindReturnsNullWhenServiceIsNotFound(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException(new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}'));

        self::assertNull((new Services($httpClient, 'https://example.test/'))->find('service-1'));
    }

    public function testFindPropagatesNonNotFoundApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_UNAUTHORIZED, 'Unauthorized', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test/'))->find('service-1');
    }

    public function testGetsServiceBandwidth(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/service%2F1/bandwidth',
                BandwidthResponse::class,
            )
            ->willReturn(new BandwidthResponse([
                'available' => 12.5,
                'total' => 100,
                'unit' => 'GB',
                'used' => 87.5,
            ]));

        $result = (new Services($httpClient, 'https://example.test///'))->getBandwidth('service/1');

        self::assertNotNull($result);
        self::assertSame(12.5, $result->bandwidth->available);
        self::assertSame(100, $result->bandwidth->total);
        self::assertSame('GB', $result->bandwidth->unit);
        self::assertSame(87.5, $result->bandwidth->used);
    }

    public function testGetBandwidthReturnsNullWhenServiceIsNotFound(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException(new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}'));

        self::assertNull((new Services($httpClient, 'https://example.test/'))->getBandwidth('service-1'));
    }

    public function testGetBandwidthPropagatesNonNotFoundApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_UNAUTHORIZED, 'Unauthorized', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test/'))->getBandwidth('service-1');
    }

    public function testGetBandwidthRequiresAServiceCode(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->getBandwidth('');
    }

    public function testServiceCodeMustNotBeEmpty(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->find('');
    }

    public function testUpdatesServiceWithJsonPayload(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_PATCH,
                'https://example.test/api/v1/services/service%2F1',
                EmptyResponse::class,
                ['json' => [
                    'protocol' => 'http',
                    'auth' => ['method' => 'ip'],
                ]],
            )
            ->willReturn(new EmptyResponse());

        (new Services($httpClient, 'https://example.test///'))->update(
            'service/1',
            new UpdateServiceRequest('http', new UpdateServiceAuthPayload('ip')),
        );
    }

    public function testUpdatePropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test/'))->update(
            'service-1',
            new UpdateServiceRequest(protocol: 'http'),
        );
    }

    public function testUpdateServiceCodeMustNotBeEmpty(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->update('', new UpdateServiceRequest(protocol: 'http'));
    }

    private function serviceDetails(): DetailResponse
    {
        return new DetailResponse(
            amount: ['amount' => 1, 'unit' => 'IP'],
            auth: ['method' => 'ip'],
            code: 'service/1',
            createdAt: '2026-08-08T12:00:00+00:00',
            startedAt: null,
            expiresAt: null,
            isRecurring: false,
            orderId: 850,
            orderUuid: 'order-uuid',
            protocol: 'http',
            price: ['amount' => 18.59, 'currency' => 'USD'],
            serviceType: 'dc_proxy',
            status: 'active',
            userId: 'user-1',
        );
    }
}
