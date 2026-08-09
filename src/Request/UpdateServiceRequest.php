<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request;

use InvalidArgumentException;

final readonly class UpdateServiceRequest
{
    public function __construct(
        public ?string $protocol = null,
        public ?UpdateServiceAuthPayload $auth = null,
    ) {
        if ($this->protocol === null && $this->auth === null) {
            throw new InvalidArgumentException('The service update must contain at least one field.');
        }

        if ($this->protocol !== null && !in_array($this->protocol, ['socks5', 'http'], true)) {
            throw new InvalidArgumentException('The service protocol must be either socks5 or http.');
        }
    }

    /**
     * @return array{protocol?: string, auth?: array{method: string}}
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->protocol !== null) {
            $data['protocol'] = $this->protocol;
        }

        if ($this->auth !== null) {
            $data['auth'] = $this->auth->toArray();
        }

        return $data;
    }
}
