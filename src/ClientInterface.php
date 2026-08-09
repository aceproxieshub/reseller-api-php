<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi;

use Aceproxies\ResellerApi\Endpoint\BalanceInterface;
use Aceproxies\ResellerApi\Endpoint\HealthInterface;
use Aceproxies\ResellerApi\Endpoint\OrdersInterface;
use Aceproxies\ResellerApi\Endpoint\ProductsInterface;

interface ClientInterface
{
    public function health(): HealthInterface;

    public function balance(): BalanceInterface;

    public function orders(): OrdersInterface;

    public function products(): ProductsInterface;

    public function getApiVersion(): string;
}
