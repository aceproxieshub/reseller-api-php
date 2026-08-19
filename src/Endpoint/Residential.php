<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Request\Service\Residential\CreateProxyRequest;
use Aceproxies\ResellerApi\Response\Service\Residential\CountriesResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyListResponse;
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
            sprintf(
                '%s/api/v1/services/residential/countries',
                rtrim($this->baseUrl, '/'),
            ),
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
            sprintf(
                '%s/api/v1/services/residential/rotation-intervals/',
                rtrim($this->baseUrl, '/'),
            ),
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
            sprintf(
                '%s/api/v1/services/residential/%s/proxy-requests',
                rtrim($this->baseUrl, '/'),
                rawurlencode($code),
            ),
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
                sprintf(
                    '%s/api/v1/services/residential/%s/proxy-requests/%s',
                    rtrim($this->baseUrl, '/'),
                    rawurlencode($code),
                    rawurlencode($id),
                ),
                ProxyRequestResponse::class,
            );
        } catch (ApiException $exception) {
            if ($exception->statusCode === HttpClientInterface::HTTP_NOT_FOUND) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function createProxyRequest(string $code, CreateProxyRequest $request): ProxyRequestResponse
    {
        Assert::nonEmptyString($code, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_POST,
            sprintf(
                '%s/api/v1/services/residential/%s/proxy-requests',
                rtrim($this->baseUrl, '/'),
                rawurlencode($code),
            ),
            ProxyRequestResponse::class,
            ['json' => $request->toArray()],
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function deleteProxyRequest(string $code, string $id): ProxyRequestResponse
    {
        Assert::nonEmptyString($code, 'service code');
        Assert::nonEmptyString($id, 'proxy request ID');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_DELETE,
            sprintf(
                '%s/api/v1/services/residential/%s/proxy-requests/%s',
                rtrim($this->baseUrl, '/'),
                rawurlencode($code),
                rawurlencode($id),
            ),
            ProxyRequestResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getProxyList(string $serviceCode, string $proxyRequestId): ProxyListResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');
        Assert::nonEmptyString($proxyRequestId, 'proxy request ID');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            sprintf(
                '%s/api/v1/services/residential/%s/proxy-requests/%s/proxy-list',
                rtrim($this->baseUrl, '/'),
                rawurlencode($serviceCode),
                rawurlencode($proxyRequestId),
            ),
            ProxyListResponse::class,
        );
    }
}
