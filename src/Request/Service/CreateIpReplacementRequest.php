<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request\Service;

final readonly class CreateIpReplacementRequest
{
    /**
     * @param list<string>|null $locations
     */
    public function __construct(public ?array $locations = null)
    {
    }

    /**
     * @return array{locations?: list<string>}
     */
    public function toArray(): array
    {
        if ($this->locations === null) {
            return [];
        }

        return ['locations' => $this->locations];
    }
}
