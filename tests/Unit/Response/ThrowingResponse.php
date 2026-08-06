<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Unit\Response;

use RuntimeException;

final class ThrowingResponse
{
    public function __construct(string $status)
    {
        throw new RuntimeException($status);
    }
}
