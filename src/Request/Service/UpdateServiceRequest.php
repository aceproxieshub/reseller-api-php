<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request\Service;

use Aceproxies\ResellerApi\Enum\Protocol;
use InvalidArgumentException;

final readonly class UpdateServiceRequest
{
    public function __construct(
        public ?Protocol $protocol = null,
        public ?UpdateServiceAuthPayload $auth = null,
    ) {
        if ($this->protocol === null && $this->auth === null) {
            throw new InvalidArgumentException('The service update must contain at least one field.');
        }
    }

    /**
     * @return array{protocol?: string, auth?: array{method: string}}
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->protocol !== null) {
            $data['protocol'] = $this->protocol->value;
        }

        if ($this->auth !== null) {
            $data['auth'] = $this->auth->toArray();
        }

        return $data;
    }
}
