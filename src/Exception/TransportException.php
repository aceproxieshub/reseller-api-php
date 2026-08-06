<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Exception;

use RuntimeException;
use Throwable;

final class TransportException extends RuntimeException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
