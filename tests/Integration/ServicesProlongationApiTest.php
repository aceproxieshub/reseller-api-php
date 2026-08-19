<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Enum\ProductType;
use Aceproxies\ResellerApi\Request\Service\CreateProlongationRequest;
use Aceproxies\ResellerApi\Response\Service\CreateProlongationResponse;
use Aceproxies\ResellerApi\Response\Service\ProlongationsResponse;

final class ServicesProlongationApiTest extends StagingTestCase
{
    public function testGetsProlongationsAndCreatesOneInFullMode(): void
    {
        if (!$this->fullMode()) {
            $result = $this->findService(
                fn (string $code): ProlongationsResponse => $this->client->services()->getProlongations($code),
                'prolongations',
            );

            self::assertInstanceOf(ProlongationsResponse::class, $result['response']);

            return;
        }

        $this->createOrder(ProductType::PaygResidentialProxy);
        $result = $this->mutateService(
            function (string $code): ?CreateProlongationResponse {
                $options = $this->client->services()->getProlongations($code);

                if ($options->items === []) {
                    return null;
                }

                $duration = $this->randomItem($options->items, 'service prolongation options');

                return $this->client->services()->createProlongation(
                    $code,
                    new CreateProlongationRequest($duration->durationId, 1),
                );
            },
            'prolongations',
        );

        self::assertInstanceOf(CreateProlongationResponse::class, $result['response']);
    }
}
