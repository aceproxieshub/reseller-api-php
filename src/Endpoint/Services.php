<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Request\Service\CreateIpReplacementRequest;
use Aceproxies\ResellerApi\Request\Service\CreateProlongationRequest;
use Aceproxies\ResellerApi\Request\Service\CreateWhitelistedIpRequest;
use Aceproxies\ResellerApi\Request\Service\UpdateCredentialsRequest;
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
use Aceproxies\ResellerApi\Response\Service\ProxyListResponse;
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpResponse;
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpsResponse;
use Aceproxies\ResellerApi\Validation\Assert;
use InvalidArgumentException;

final readonly class Services implements ServicesInterface
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
     * @throws InvalidArgumentException
     */
    public function list(?int $page = null, ?int $limit = null): ListResponse
    {
        $query = [];

        if ($page !== null) {
            Assert::positiveInteger($page, 'page');
            $query['page'] = $page;
        }

        if ($limit !== null) {
            Assert::positiveInteger($limit, 'limit');
            $query['limit'] = $limit;
        }

        $url = rtrim($this->baseUrl, '/') . '/api/v1/services';

        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            $url,
            ListResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function find(string $code): ?DetailResponse
    {
        Assert::nonEmptyString($code, 'service code');

        try {
            return $this->httpClient->request(
                HttpClientInterface::METHOD_GET,
                rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($code),
                DetailResponse::class,
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
    public function getBandwidth(string $serviceCode): ?BandwidthResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        try {
            return $this->httpClient->request(
                HttpClientInterface::METHOD_GET,
                rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/bandwidth',
                BandwidthResponse::class,
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
    public function getCredentials(string $serviceCode): ?CredentialsResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        try {
            return $this->httpClient->request(
                HttpClientInterface::METHOD_GET,
                rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/auth/credentials',
                CredentialsResponse::class,
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
    public function updateCredentials(string $serviceCode, UpdateCredentialsRequest $request): CredentialsResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_PUT,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/auth/credentials',
            CredentialsResponse::class,
            ['json' => $request->toArray()],
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getWhitelistedIps(string $serviceCode): WhitelistedIpsResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/auth/whitelisted-ips',
            WhitelistedIpsResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function addWhitelistedIp(string $serviceCode, CreateWhitelistedIpRequest $request): WhitelistedIpResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_POST,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/auth/whitelisted-ips',
            WhitelistedIpResponse::class,
            ['json' => $request->toArray()],
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function deleteWhitelistedIp(string $serviceCode, string $ip): void
    {
        Assert::nonEmptyString($serviceCode, 'service code');
        Assert::ipAddress($ip, 'IP address');

        $this->httpClient->request(
            HttpClientInterface::METHOD_DELETE,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/auth/whitelisted-ips/' . rawurlencode($ip),
            EmptyResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getIpReplacements(string $serviceCode): IpReplacementsResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/ip-replacements',
            IpReplacementsResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function createIpReplacement(string $serviceCode, CreateIpReplacementRequest $request): IpReplacementResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_POST,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/ip-replacements',
            IpReplacementResponse::class,
            ['json' => $request->toArray()],
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getAvailableIpReplacements(string $serviceCode): IpReplacementCountResponse
    {
        return $this->getIpReplacementCountAtPath($serviceCode, 'available');
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getIpReplacementCount(string $serviceCode): IpReplacementCountResponse
    {
        return $this->getIpReplacementCountAtPath($serviceCode, 'count');
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getIpReplacementLocations(string $serviceCode): IpReplacementLocationsResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/ip-replacements/locations',
            IpReplacementLocationsResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getProlongations(string $serviceCode): ProlongationsResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/prolongations',
            ProlongationsResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function createProlongation(string $serviceCode, CreateProlongationRequest $request): CreateProlongationResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_POST,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/prolongations',
            CreateProlongationResponse::class,
            ['json' => $request->toArray()],
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getProxyList(string $serviceCode): ProxyListResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/proxy-list',
            ProxyListResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function update(string $code, UpdateServiceRequest $request): void
    {
        Assert::nonEmptyString($code, 'service code');

        $this->httpClient->request(
            HttpClientInterface::METHOD_PATCH,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($code),
            EmptyResponse::class,
            ['json' => $request->toArray()],
        );
    }

    public function residential(): ResidentialInterface
    {
        return new Residential($this->httpClient, $this->baseUrl);
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    private function getIpReplacementCountAtPath(string $serviceCode, string $path): IpReplacementCountResponse
    {
        Assert::nonEmptyString($serviceCode, 'service code');

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($serviceCode) . '/ip-replacements/' . $path,
            IpReplacementCountResponse::class,
        );
    }
}
