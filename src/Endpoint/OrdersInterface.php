<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Request\CreateOrderRequest;
use Aceproxies\ResellerApi\Response\CreateOrderResponse;
use Aceproxies\ResellerApi\Response\OrderDetailsResponse;
use Aceproxies\ResellerApi\Response\OrderListResponse;
use InvalidArgumentException;

interface OrdersInterface
{
    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function list(?int $page = null, ?int $limit = null): OrderListResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     */
    public function create(CreateOrderRequest $request): CreateOrderResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function get(string $id): OrderDetailsResponse;
}
