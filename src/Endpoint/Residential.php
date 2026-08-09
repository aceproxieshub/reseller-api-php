<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\Service\Residential\CountriesResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\RotationIntervalsResponse;

final readonly class Residential implements ResidentialInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
    ) {
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     */
    public function countries(): CountriesResponse
    {
        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/services/residential/countries',
            CountriesResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     */
    public function rotationIntervals(): RotationIntervalsResponse
    {
        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/services/residential/rotation-intervals/',
            RotationIntervalsResponse::class,
        );
    }
}
