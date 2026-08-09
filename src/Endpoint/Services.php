<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Request\Service\UpdateServiceRequest;
use Aceproxies\ResellerApi\Response\EmptyResponse;
use Aceproxies\ResellerApi\Response\Service\BandwidthResponse;
use Aceproxies\ResellerApi\Response\Service\DetailResponse;
use Aceproxies\ResellerApi\Response\Service\ListResponse;
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
}
