<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Enum\RotationInterval;
use Aceproxies\ResellerApi\Request\Service\Residential\CreateProxyRequest;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestsResponse;
use Aceproxies\ResellerApi\Response\Service\ServiceResponse;

final class ResidentialProxyRequestsApiTest extends StagingTestCase
{
    private const RESIDENTIAL_SERVICE_TYPES = [
        'residential_proxy',
        'payg_residential_proxy',
    ];

    public function testListsAndFindsAnExistingResidentialProxyRequest(): void
    {
        $service = $this->randomItem(
            $this->residentialActiveServices(),
            'active residential proxy services',
        );

        $residentialClient = $this->client->services()->residential();


        $country = $this->randomItem($residentialClient->countries()->items, 'residential countries');
        $rotation = $this->randomItem(
            array_values(array_filter(
                $country->rotationIntervals,
                static fn (string $value): bool => RotationInterval::tryFrom($value) !== null,
            )),
            'residential rotation intervals',
        );

        // Create a new proxy request
        $created = $residentialClient->createProxyRequest(
            $service->code,
            new CreateProxyRequest($country->id, 1, RotationInterval::from($rotation)),
        );

        self::assertInstanceOf(ProxyRequestResponse::class, $created);
        self::assertSame($created->id, $residentialClient->findProxyRequest($service->code, $created->id)?->id);
        self::assertInstanceOf(
            ProxyRequestResponse::class,
            $residentialClient->deleteProxyRequest($service->code, $created->id),
        );

        // Get list of proxy requests
        $requests = $residentialClient->proxyRequests($service->code);
        self::assertInstanceOf(ProxyRequestsResponse::class, $requests);

        // Check proxy request existance
        $selected = $this->randomItem($requests->items, 'residential proxy requests');
        $details = $residentialClient->findProxyRequest($service->code, $selected->id);
        self::assertInstanceOf(ProxyRequestResponse::class, $details);
        self::assertSame($selected->id, $details->id);
    }

    /**
     * @return list<ServiceResponse>
     */
    private function residentialActiveServices(): array
    {
        return array_values(array_filter(
            $this->services(),
            static fn (ServiceResponse $service): bool => in_array(
                strtolower((string) $service->type),
                self::RESIDENTIAL_SERVICE_TYPES,
                true,
            ) && strtolower($service->status) === 'active',
        ));
    }
}
