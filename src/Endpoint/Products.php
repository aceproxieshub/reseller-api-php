<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\Product\ProductListResponse;
use Aceproxies\ResellerApi\Response\Product\ProductTypesResponse;
use Aceproxies\ResellerApi\Validation\Assert;
use InvalidArgumentException;

final readonly class Products implements ProductsInterface
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
    public function list(?string $type = null): ProductListResponse
    {
        if ($type !== null) {
            Assert::nonEmptyString($type, 'product type');
        }

        $url = sprintf(
            '%s/api/v1/products',
            rtrim($this->baseUrl, '/'),
        );

        if ($type !== null) {
            $url .= '?' . http_build_query(['type' => $type]);
        }

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            $url,
            ProductListResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     */
    public function types(): ProductTypesResponse
    {
        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            sprintf(
                '%s/api/v1/products/types',
                rtrim($this->baseUrl, '/'),
            ),
            ProductTypesResponse::class,
        );
    }
}
