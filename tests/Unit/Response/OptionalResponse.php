<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Response;

final readonly class OptionalResponse
{
    public function __construct(
        public string $status,
        public string $message = 'default',
    ) {
    }
}
