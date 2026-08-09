<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\Service\Residential\CountriesResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestsResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\RotationIntervalsResponse;
use Aceproxies\ResellerApi\Validation\Assert;
use InvalidArgumentException;

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

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function proxyRequests(string $code): ProxyRequestsResponse
    {
        Assert::nonEmptyString($code, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/services/residential/' . rawurlencode($code) . '/proxy-requests',
            ProxyRequestsResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function findProxyRequest(string $code, string $id): ?ProxyRequestResponse
    {
        Assert::nonEmptyString($code, 'service code');
        Assert::nonEmptyString($id, 'proxy request ID');

        try {
            return $this->httpClient->request(
                HttpClientInterface::METHOD_GET,
                rtrim($this->baseUrl, '/') . '/api/v1/services/residential/' . rawurlencode($code) . '/proxy-requests/' . rawurlencode($id),
                ProxyRequestResponse::class,
            );
        } catch (ApiException $exception) {
            if ($exception->statusCode === HttpClientInterface::HTTP_NOT_FOUND) {
                return null;
            }

            throw $exception;
        }
    }
}
