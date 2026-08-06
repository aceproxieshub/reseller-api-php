<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Exception;

use RuntimeException;
use Throwable;

final class InvalidResponseException extends RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $body,
        ?Throwable $previous = null,
    ) {
        parent::__construct('The API returned an invalid response.', 0, $previous);
    }
}
