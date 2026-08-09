<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Product;

final readonly class ProductTypesResponse
{
    /**
     * @param list<string> $types
     */
    public function __construct(
        public array $types,
    ) {
    }
}
