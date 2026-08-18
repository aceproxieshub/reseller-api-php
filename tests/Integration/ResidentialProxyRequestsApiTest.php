<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Enum\RotationInterval;
use Aceproxies\ResellerApi\Request\Service\Residential\CreateProxyRequest;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyListResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestsResponse;

final class ResidentialProxyRequestsApiTest extends StagingTestCase
{
    public function testListsAndFindsAnExistingResidentialProxyRequest(): void
    {
        $service = $this->residentialService();
        $residential = $this->client->services()->residential();

        if ($this->fullMode()) {
            $country = $this->randomItem($residential->countries()->items, 'residential countries');
            $rotation = $this->randomItem(
                array_values(array_filter(
                    $country->rotationIntervals,
                    static fn (string $value): bool => RotationInterval::tryFrom($value) !== null,
                )),
                'residential rotation intervals',
            );
            $created = $residential->createProxyRequest(
                $service->code,
                new CreateProxyRequest($country->id, 1, RotationInterval::from($rotation)),
            );

            self::assertInstanceOf(ProxyRequestResponse::class, $created);
            self::assertSame($created->id, $residential->findProxyRequest($service->code, $created->id)?->id);
            self::assertInstanceOf(
                ProxyListResponse::class,
                $residential->getProxyList($service->code, $created->id),
            );
            self::assertInstanceOf(
                ProxyRequestResponse::class,
                $residential->deleteProxyRequest($service->code, $created->id),
            );

            return;
        }

        $requests = $residential->proxyRequests($service->code);
        self::assertInstanceOf(ProxyRequestsResponse::class, $requests);

        $selected = $this->randomItem($requests->items, 'residential proxy requests');
        $details = $residential->findProxyRequest($service->code, $selected->id);

        self::assertInstanceOf(ProxyRequestResponse::class, $details);
        self::assertSame($selected->id, $details->id);
        self::assertInstanceOf(
            ProxyListResponse::class,
            $residential->getProxyList($service->code, $selected->id),
        );
    }
}
