<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Request\Service\CreateIpReplacementRequest;
use Aceproxies\ResellerApi\Response\Service\IpReplacementCountResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementLocationsResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementsResponse;

final class ServicesIpReplacementApiTest extends StagingTestCase
{
    public function testGetsIpReplacementInformationAndCreatesOneInFullMode(): void
    {
        $services = $this->client->services();

        if (!$this->fullMode()) {
            $result = $this->findService(
                function (string $code) use ($services): IpReplacementLocationsResponse {
                    $replacements = $services->getIpReplacements($code);
                    self::assertInstanceOf(IpReplacementsResponse::class, $replacements);
                    self::assertInstanceOf(
                        IpReplacementCountResponse::class,
                        $services->getAvailableIpReplacements($code),
                    );
                    self::assertInstanceOf(
                        IpReplacementCountResponse::class,
                        $services->getIpReplacementCount($code),
                    );

                    return $services->getIpReplacementLocations($code);
                },
                'IP replacements',
            );

            self::assertInstanceOf(IpReplacementLocationsResponse::class, $result['response']);

            return;
        }

        $result = $this->mutateService(
            function (string $code) use ($services): IpReplacementResponse {
                $services->getIpReplacements($code);
                $services->getAvailableIpReplacements($code);
                $services->getIpReplacementCount($code);
                $locations = $services->getIpReplacementLocations($code);
                $locationIds = $locations->locations === []
                    ? []
                    : [$this->randomItem($locations->locations, 'IP replacement locations')->id];

                return $services->createIpReplacement(
                    $code,
                    new CreateIpReplacementRequest($locationIds),
                );
            },
            'IP replacements',
        );

        self::assertInstanceOf(IpReplacementResponse::class, $result['response']);
        self::assertNotSame('', $result['response']->uuid);
    }
}
