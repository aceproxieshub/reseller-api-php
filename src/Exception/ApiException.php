<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Exception;

use RuntimeException;

final class ApiException extends RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly ?string $apiMessage,
        public readonly string $body,
    ) {
        parent::__construct($apiMessage ?? 'The API returned an error response.');
    }
}
