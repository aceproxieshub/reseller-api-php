<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Enum\ProductType;
use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Response\Product\ProductListResponse;
use Aceproxies\ResellerApi\Response\Product\ProductTypesResponse;
use InvalidArgumentException;

interface ProductsInterface
{
    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function list(ProductType|string|null $type = null): ProductListResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     */
    public function types(): ProductTypesResponse;
}
