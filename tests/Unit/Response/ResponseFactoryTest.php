<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Response;

use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Response\Health\HealthResponse;
use Aceproxies\ResellerApi\Response\Order\OrderListResponse;
use Aceproxies\ResellerApi\Response\Order\OrderResponse;
use Aceproxies\ResellerApi\Response\Product\ProductListResponse;
use Aceproxies\ResellerApi\Response\Product\ProductResponse;
use Aceproxies\ResellerApi\Response\Product\ProductTypesResponse;
use Aceproxies\ResellerApi\Response\ResponseFactory;
use Aceproxies\ResellerApi\Response\Service\BandwidthResponse;
use Aceproxies\ResellerApi\Response\Service\CreateProlongationResponse;
use Aceproxies\ResellerApi\Response\Service\CredentialsResponse;
use Aceproxies\ResellerApi\Response\Service\DetailResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementCountResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementLocationsResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementsResponse;
use Aceproxies\ResellerApi\Response\Service\ListResponse;
use Aceproxies\ResellerApi\Response\Service\ProlongationsResponse;
use Aceproxies\ResellerApi\Response\Service\ProxyListResponse as ServiceProxyListResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\CountriesResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyListResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestsResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\RotationIntervalsResponse;
use Aceproxies\ResellerApi\Response\Service\ServiceResponse;
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpsResponse;
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

    public function testCreatesServiceListWithNestedTypedResponses(): void
    {
        $response = $this->factory->create(
            '{"data":{"items":[{"code":"D32365E0C629B-170726","orderId":"019f6fae-623b-7142-a81f-238826fbd8dd","status":"active","amount":{"amount":1,"unit":"IP"},"auth":{"method":"ip"},"createdAt":"2026-07-17T10:45:27+00:00","startedAt":"2026-07-17T10:45:28+00:00","expiredAt":"2026-08-16T10:45:28+00:00"}],"limit":50,"page":1}}',
            ListResponse::class,
            200,
        );

        self::assertCount(1, $response->items);
        self::assertInstanceOf(ServiceResponse::class, $response->items[0]);
        self::assertSame('D32365E0C629B-170726', $response->items[0]->code);
        self::assertSame('019f6fae-623b-7142-a81f-238826fbd8dd', $response->items[0]->orderId);
        self::assertNotNull($response->items[0]->amount);
        self::assertSame(1, $response->items[0]->amount->amount);
        self::assertSame('IP', $response->items[0]->amount->unit);
        self::assertSame('ip', $response->items[0]->auth?->method);
        self::assertSame('+00:00', $response->items[0]->createdAt?->getTimezone()->getName());
        self::assertSame(50, $response->limit);
        self::assertSame(1, $response->page);
    }

    public function testCreatesResidentialCountriesWithTypedResponses(): void
    {
        $response = $this->factory->create(
            '{"data":[{"id":1,"name":"United States","rotationIntervals":["all","1min"]}]}',
            CountriesResponse::class,
            200,
        );

        self::assertCount(1, $response->items);
        self::assertSame(1, $response->items[0]->id);
        self::assertSame('United States', $response->items[0]->name);
        self::assertSame(['all', '1min'], $response->items[0]->rotationIntervals);
    }

    public function testCreatesResidentialRotationIntervals(): void
    {
        $response = $this->factory->create(
            '{"data":{"all":"All traffic","high":"High rotation","1min":"Every minute","10min":"Every 10 minutes","30min":"Every 30 minutes"}}',
            RotationIntervalsResponse::class,
            200,
        );

        self::assertSame('All traffic', $response->intervals['all']);
        self::assertSame('Every minute', $response->intervals['1min']);
        self::assertSame('Every 30 minutes', $response->intervals['30min']);
    }

    public function testCreatesResidentialProxyRequestsWithTypedResponses(): void
    {
        $response = $this->factory->create(
            '{"data":[{"countryId":1,"createdAt":"2026-08-08T12:00:00+00:00","id":"request-1","proxyCount":10,"rotationInterval":"all","status":"pending","updatedAt":"2026-08-08T12:30:00+00:00"}]}',
            ProxyRequestsResponse::class,
            200,
        );

        self::assertCount(1, $response->items);
        self::assertSame('request-1', $response->items[0]->id);
        self::assertSame(1, $response->items[0]->countryId);
        self::assertSame('2026-08-08 12:30:00', $response->items[0]->updatedAt->format('Y-m-d H:i:s'));
    }

    public function testCreatesResidentialProxyListWithTypedResponses(): void
    {
        $response = $this->factory->create(
            '{"data":[{"ip":"192.0.2.1","password":"secret","port":8080,"username":"proxy-user"}]}',
            ProxyListResponse::class,
            200,
        );

        self::assertCount(1, $response->items);
        self::assertSame('192.0.2.1', $response->items[0]->ip);
        self::assertSame('secret', $response->items[0]->password);
        self::assertSame(8080, $response->items[0]->port);
        self::assertSame('proxy-user', $response->items[0]->username);
    }

    public function testCreatesServiceWithOmittedNullableFields(): void
    {
        $response = $this->factory->create(
            '{"data":{"items":[{"code":"service-1","orderId":"order-1","status":"pending"}],"limit":50,"page":1}}',
            ListResponse::class,
            200,
        );

        self::assertNull($response->items[0]->amount);
        self::assertNull($response->items[0]->auth);
        self::assertNull($response->items[0]->createdAt);
        self::assertNull($response->items[0]->startedAt);
        self::assertNull($response->items[0]->expiredAt);
    }

    public function testCreatesServiceDetailsWithNestedTypedResponses(): void
    {
        $response = $this->factory->create(
            '{"data":{"amount":{"amount":1,"unit":"IP"},"auth":{"method":"ip"},"code":"CCD9F42D9TGMZ-040426","createdAt":"2026-08-08T12:00:00+00:00","expiresAt":null,"isRecurring":false,"orderId":850,"orderUuid":"0758a20a-b66e-4295-9131-cb9d0fd953f6","price":{"amount":18.59,"currency":"USD"},"protocol":"http","serviceType":"dc_proxy","startedAt":null,"status":"active","userId":"user-1"}}',
            DetailResponse::class,
            200,
        );

        self::assertSame('CCD9F42D9TGMZ-040426', $response->code);
        self::assertSame(1, $response->amount->amount);
        self::assertSame('ip', $response->auth->method);
        self::assertSame(18.59, $response->price->amount);
        self::assertSame('+00:00', $response->createdAt->getTimezone()->getName());
        self::assertNull($response->startedAt);
        self::assertNull($response->expiresAt);
        self::assertFalse($response->isRecurring);
        self::assertSame(850, $response->orderId);
    }

    public function testCreatesServiceBandwidthWithNestedTypedResponse(): void
    {
        $response = $this->factory->create(
            '{"data":{"bandwidth":{"available":12.5,"total":100,"unit":"GB","used":87.5}}}',
            BandwidthResponse::class,
            200,
        );

        self::assertSame(12.5, $response->bandwidth->available);
        self::assertSame(100, $response->bandwidth->total);
        self::assertSame('GB', $response->bandwidth->unit);
        self::assertSame(87.5, $response->bandwidth->used);
    }

    public function testCreatesServiceCredentials(): void
    {
        $response = $this->factory->create(
            '{"data":{"password":"secret","username":"proxy-user"}}',
            CredentialsResponse::class,
            200,
        );

        self::assertSame('proxy-user', $response->username);
        self::assertSame('secret', $response->password);
    }

    public function testCreatesServiceWhitelistedIps(): void
    {
        $response = $this->factory->create(
            '{"data":[{"description":"Office","ip":"192.0.2.10"},{"ip":"198.51.100.20"}]}',
            WhitelistedIpsResponse::class,
            200,
        );

        self::assertSame('192.0.2.10', $response->items[0]->ip);
        self::assertSame('Office', $response->items[0]->description);
        self::assertSame('198.51.100.20', $response->items[1]->ip);
        self::assertNull($response->items[1]->description);
    }

    public function testCreatesServiceIpReplacements(): void
    {
        $response = $this->factory->create(
            '{"data":[{"createdAt":"2026-08-08T12:00:00+00:00","replacedAt":null,"status":"pending","uuid":"replacement-1"}]}',
            IpReplacementsResponse::class,
            200,
        );

        self::assertSame('replacement-1', $response->items[0]->uuid);
        self::assertSame('+00:00', $response->items[0]->createdAt->getTimezone()->getName());
        self::assertNull($response->items[0]->replacedAt);
    }

    public function testCreatesServiceIpReplacementCount(): void
    {
        $response = $this->factory->create(
            '{"data":{"count":7}}',
            IpReplacementCountResponse::class,
            200,
        );

        self::assertSame(7, $response->count);
    }

    public function testCreatesServiceIpReplacementLocations(): void
    {
        $response = $this->factory->create(
            '{"data":{"locations":[{"country":"United States","id":"us","location":"New York"}]}}',
            IpReplacementLocationsResponse::class,
            200,
        );

        self::assertSame('us', $response->locations[0]->id);
        self::assertSame('United States', $response->locations[0]->country);
        self::assertSame('New York', $response->locations[0]->location);
    }

    public function testCreatesServiceProlongations(): void
    {
        $response = $this->factory->create(
            '{"data":[{"durationDays":30,"durationId":"duration-1","name":"Monthly","price":18.59}]}',
            ProlongationsResponse::class,
            200,
        );

        self::assertSame('duration-1', $response->items[0]->durationId);
        self::assertSame('Monthly', $response->items[0]->name);
        self::assertSame(18.59, $response->items[0]->price);
    }

    public function testCreatesServiceProlongation(): void
    {
        $response = $this->factory->create(
            '{"data":{"durationId":"duration-1","newExpirationDate":"2026-09-08T12:00:00+00:00","quantity":2,"status":"completed"}}',
            CreateProlongationResponse::class,
            200,
        );

        self::assertSame('duration-1', $response->durationId);
        self::assertSame('2026-09-08', $response->newExpirationDate->format('Y-m-d'));
        self::assertSame(2, $response->quantity);
        self::assertSame('completed', $response->status);
    }

    public function testCreatesServiceProxyListWithTypedResponses(): void
    {
        $response = $this->factory->create(
            '{"data":[{"ip":"192.0.2.1","password":"secret","port":8080,"username":"proxy-user"}]}',
            ServiceProxyListResponse::class,
            200,
        );

        self::assertCount(1, $response->items);
        self::assertSame('192.0.2.1', $response->items[0]->ip);
        self::assertSame('secret', $response->items[0]->password);
        self::assertSame(8080, $response->items[0]->port);
        self::assertSame('proxy-user', $response->items[0]->username);
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
