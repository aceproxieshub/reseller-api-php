<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\ServiceDetailResponse;
use Aceproxies\ResellerApi\Response\ServiceListResponse;
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
    public function list(?int $page = null, ?int $limit = null): ServiceListResponse
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
            ServiceListResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function find(string $code): ?ServiceDetailResponse
    {
        Assert::nonEmptyString($code, 'service code');

        try {
            return $this->httpClient->request(
                HttpClientInterface::METHOD_GET,
                rtrim($this->baseUrl, '/') . '/api/v1/services/' . rawurlencode($code),
                ServiceDetailResponse::class,
            );
        } catch (ApiException $exception) {
            if ($exception->statusCode === HttpClientInterface::HTTP_NOT_FOUND) {
                return null;
            }

            throw $exception;
        }
    }
}
