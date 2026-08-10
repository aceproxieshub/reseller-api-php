<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Endpoint;

use Aceproxies\ResellerApi\Endpoint\Services;
use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Request\Service\CreateIpReplacementRequest;
use Aceproxies\ResellerApi\Request\Service\CreateProlongationRequest;
use Aceproxies\ResellerApi\Request\Service\CreateWhitelistedIpRequest;
use Aceproxies\ResellerApi\Request\Service\UpdateCredentialsRequest;
use Aceproxies\ResellerApi\Request\Service\UpdateServiceAuthPayload;
use Aceproxies\ResellerApi\Request\Service\UpdateServiceRequest;
use Aceproxies\ResellerApi\Response\EmptyResponse;
use Aceproxies\ResellerApi\Response\Service\BandwidthResponse;
use Aceproxies\ResellerApi\Response\Service\CreateProlongationResponse;
use Aceproxies\ResellerApi\Response\Service\CredentialsResponse;
use Aceproxies\ResellerApi\Response\Service\DetailResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementCountResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementLocationsResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementsResponse;
use Aceproxies\ResellerApi\Response\Service\ListResponse;
use Aceproxies\ResellerApi\Response\Service\ProlongationsResponse;
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpResponse;
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpsResponse;
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

    public function testGetsServiceCredentials(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/service%2F1/auth/credentials',
                CredentialsResponse::class,
            )
            ->willReturn(new CredentialsResponse('proxy-user', 'secret'));

        $result = (new Services($httpClient, 'https://example.test///'))->getCredentials('service/1');

        self::assertNotNull($result);
        self::assertSame('proxy-user', $result->username);
        self::assertSame('secret', $result->password);
    }

    public function testGetCredentialsReturnsNullWhenServiceIsNotFound(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException(new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}'));

        self::assertNull((new Services($httpClient, 'https://example.test/'))->getCredentials('service-1'));
    }

    public function testGetCredentialsPropagatesNonNotFoundApiException(): void
    {
        $exception = new ApiException(409, 'Conflict', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test/'))->getCredentials('service-1');
    }

    public function testGetCredentialsRequiresAServiceCode(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->getCredentials('');
    }

    public function testUpdatesServiceCredentialsWithPasswordOnly(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_PUT,
                'https://example.test/api/v1/services/service%2F1/auth/credentials',
                CredentialsResponse::class,
                ['json' => ['password' => 'secret']],
            )
            ->willReturn(new CredentialsResponse('proxy-user', 'secret'));

        $result = (new Services($httpClient, 'https://example.test///'))->updateCredentials(
            'service/1',
            new UpdateCredentialsRequest('secret'),
        );

        self::assertSame('proxy-user', $result->username);
        self::assertSame('secret', $result->password);
    }

    public function testUpdatesServiceCredentialsWithUsernameAndPassword(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_PUT,
                'https://example.test/api/v1/services/service%2F1/auth/credentials',
                CredentialsResponse::class,
                ['json' => ['password' => 'secret', 'username' => 'proxy-user']],
            )
            ->willReturn(new CredentialsResponse('proxy-user', 'secret'));

        (new Services($httpClient, 'https://example.test/'))->updateCredentials(
            'service/1',
            new UpdateCredentialsRequest('secret', 'proxy-user'),
        );
    }

    public function testUpdateCredentialsPropagatesApiException(): void
    {
        $exception = new ApiException(400, 'Bad request', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test/'))->updateCredentials(
            'service-1',
            new UpdateCredentialsRequest('secret'),
        );
    }

    public function testUpdateCredentialsServiceCodeMustNotBeEmpty(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->updateCredentials('', new UpdateCredentialsRequest('secret'));
    }

    public function testGetsServiceWhitelistedIps(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/service%2F1/auth/whitelisted-ips',
                WhitelistedIpsResponse::class,
            )
            ->willReturn(new WhitelistedIpsResponse([
                ['ip' => '192.0.2.10', 'description' => 'Office'],
            ]));

        $result = (new Services($httpClient, 'https://example.test///'))->getWhitelistedIps('service/1');

        self::assertSame('192.0.2.10', $result->items[0]->ip);
        self::assertSame('Office', $result->items[0]->description);
    }

    public function testGetWhitelistedIpsRequiresAServiceCode(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->getWhitelistedIps('');
    }

    public function testGetWhitelistedIpsPropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test'))->getWhitelistedIps('service-1');
    }

    public function testAddsServiceWhitelistedIp(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_POST,
                'https://example.test/api/v1/services/service%2F1/auth/whitelisted-ips',
                WhitelistedIpResponse::class,
                ['json' => ['ip' => '192.0.2.10']],
            )
            ->willReturn(new WhitelistedIpResponse('192.0.2.10', 'Office'));

        $result = (new Services($httpClient, 'https://example.test/'))->addWhitelistedIp(
            'service/1',
            new CreateWhitelistedIpRequest('192.0.2.10'),
        );

        self::assertSame('192.0.2.10', $result->ip);
        self::assertSame('Office', $result->description);
    }

    public function testAddWhitelistedIpRequiresAServiceCode(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->addWhitelistedIp('', new CreateWhitelistedIpRequest('192.0.2.10'));
    }

    public function testAddWhitelistedIpPropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test'))->addWhitelistedIp(
            'service-1',
            new CreateWhitelistedIpRequest('192.0.2.10'),
        );
    }

    public function testDeletesServiceWhitelistedIp(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_DELETE,
                'https://example.test/api/v1/services/service%2F1/auth/whitelisted-ips/192.0.2.10',
                EmptyResponse::class,
            )
            ->willReturn(new EmptyResponse());

        (new Services($httpClient, 'https://example.test///'))->deleteWhitelistedIp('service/1', '192.0.2.10');
    }

    public function testDeleteWhitelistedIpRequiresServiceCodeAndIp(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->deleteWhitelistedIp('', '192.0.2.10');
    }

    public function testDeleteWhitelistedIpRequiresAnIp(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The IP address must not be empty.'));

        $services->deleteWhitelistedIp('service-1', '');
    }

    public function testDeleteWhitelistedIpPropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test'))->deleteWhitelistedIp('service-1', '192.0.2.10');
    }

    public function testGetsServiceIpReplacements(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/service%2F1/ip-replacements',
                IpReplacementsResponse::class,
            )
            ->willReturn(new IpReplacementsResponse([
                [
                    'createdAt' => '2026-08-08T12:00:00+00:00',
                    'replacedAt' => null,
                    'status' => 'pending',
                    'uuid' => 'replacement-1',
                ],
            ]));

        $result = (new Services($httpClient, 'https://example.test///'))->getIpReplacements('service/1');

        self::assertSame('replacement-1', $result->items[0]->uuid);
        self::assertSame('pending', $result->items[0]->status);
        self::assertSame('+00:00', $result->items[0]->createdAt->getTimezone()->getName());
        self::assertNull($result->items[0]->replacedAt);
    }

    public function testGetIpReplacementsRequiresAServiceCode(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->getIpReplacements('');
    }

    public function testGetIpReplacementsPropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test'))->getIpReplacements('service-1');
    }

    public function testCreatesServiceIpReplacementWithoutLocations(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_POST,
                'https://example.test/api/v1/services/service%2F1/ip-replacements',
                IpReplacementResponse::class,
                ['json' => []],
            )
            ->willReturn(new IpReplacementResponse(
                createdAt: '2026-08-08T12:00:00+00:00',
                replacedAt: null,
                status: 'pending',
                uuid: 'replacement-1',
            ));

        $result = (new Services($httpClient, 'https://example.test/'))->createIpReplacement(
            'service/1',
            new CreateIpReplacementRequest(),
        );

        self::assertSame('replacement-1', $result->uuid);
    }

    public function testCreatesServiceIpReplacementWithLocations(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_POST,
                'https://example.test/api/v1/services/service%2F1/ip-replacements',
                IpReplacementResponse::class,
                ['json' => ['locations' => ['US', 'DE']]],
            )
            ->willReturn(new IpReplacementResponse(
                createdAt: '2026-08-08T12:00:00+00:00',
                replacedAt: '2026-08-08T12:05:00+00:00',
                status: 'completed',
                uuid: 'replacement-1',
            ));

        $result = (new Services($httpClient, 'https://example.test/'))->createIpReplacement(
            'service/1',
            new CreateIpReplacementRequest(['US', 'DE']),
        );

        self::assertSame('completed', $result->status);
        self::assertNotNull($result->replacedAt);
    }

    public function testCreateIpReplacementRequiresAServiceCode(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->createIpReplacement('', new CreateIpReplacementRequest());
    }

    public function testCreateIpReplacementPropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test'))->createIpReplacement(
            'service-1',
            new CreateIpReplacementRequest(),
        );
    }

    public function testGetsAvailableIpReplacements(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/service%2F1/ip-replacements/available',
                IpReplacementCountResponse::class,
            )
            ->willReturn(new IpReplacementCountResponse(7));

        self::assertSame(7, (new Services($httpClient, 'https://example.test'))->getAvailableIpReplacements('service/1')->count);
    }

    public function testGetsIpReplacementCount(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/service%2F1/ip-replacements/count',
                IpReplacementCountResponse::class,
            )
            ->willReturn(new IpReplacementCountResponse(12));

        self::assertSame(12, (new Services($httpClient, 'https://example.test'))->getIpReplacementCount('service/1')->count);
    }

    public function testGetsIpReplacementLocations(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/service%2F1/ip-replacements/locations',
                IpReplacementLocationsResponse::class,
            )
            ->willReturn(new IpReplacementLocationsResponse([
                ['country' => 'United States', 'id' => 'us', 'location' => 'New York'],
            ]));

        $result = (new Services($httpClient, 'https://example.test'))->getIpReplacementLocations('service/1');

        self::assertSame('us', $result->locations[0]->id);
        self::assertSame('United States', $result->locations[0]->country);
        self::assertSame('New York', $result->locations[0]->location);
    }

    public function testIpReplacementCountAndLocationsRequireAServiceCode(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));
        $services->getAvailableIpReplacements('');
    }

    public function testGetsServiceProlongations(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_GET,
                'https://example.test/api/v1/services/service%2F1/prolongations',
                ProlongationsResponse::class,
            )
            ->willReturn(new ProlongationsResponse([
                [
                    'durationDays' => 30,
                    'durationId' => 'duration-1',
                    'name' => 'Monthly',
                    'price' => 18.59,
                ],
            ]));

        $result = (new Services($httpClient, 'https://example.test///'))->getProlongations('service/1');

        self::assertSame('duration-1', $result->items[0]->durationId);
        self::assertSame(30, $result->items[0]->durationDays);
        self::assertSame(18.59, $result->items[0]->price);
    }

    public function testGetProlongationsRequiresAServiceCode(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->getProlongations('');
    }

    public function testGetProlongationsPropagatesApiException(): void
    {
        $exception = new ApiException(HttpClientInterface::HTTP_NOT_FOUND, 'Not found', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test'))->getProlongations('service-1');
    }

    public function testCreatesServiceProlongation(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                HttpClientInterface::METHOD_POST,
                'https://example.test/api/v1/services/service%2F1/prolongations',
                CreateProlongationResponse::class,
                ['json' => ['durationId' => 'duration-1', 'quantity' => 2]],
            )
            ->willReturn(new CreateProlongationResponse(
                durationId: 'duration-1',
                newExpirationDate: '2026-09-08T12:00:00+00:00',
                quantity: 2,
                status: 'completed',
            ));

        $result = (new Services($httpClient, 'https://example.test/'))->createProlongation(
            'service/1',
            new CreateProlongationRequest('duration-1', 2),
        );

        self::assertSame('duration-1', $result->durationId);
        self::assertSame('2026-09-08', $result->newExpirationDate->format('Y-m-d'));
        self::assertSame('completed', $result->status);
    }

    public function testCreateProlongationRequiresAServiceCode(): void
    {
        $services = new Services($this->createStub(HttpClientInterface::class), 'https://example.test');

        self::expectExceptionObject(new InvalidArgumentException('The service code must not be empty.'));

        $services->createProlongation('', new CreateProlongationRequest('duration-1', 1));
    }

    public function testCreateProlongationPropagatesApiException(): void
    {
        $exception = new ApiException(403, 'Forbidden', '{}');
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($exception);

        self::expectExceptionObject($exception);

        (new Services($httpClient, 'https://example.test'))->createProlongation(
            'service-1',
            new CreateProlongationRequest('duration-1', 1),
        );
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
