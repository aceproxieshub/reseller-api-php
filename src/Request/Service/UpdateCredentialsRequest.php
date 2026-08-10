<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Request\Service;

use Aceproxies\ResellerApi\Validation\Assert;

final readonly class UpdateCredentialsRequest
{
    public function __construct(
        public string $password,
        public ?string $username = null,
    ) {
        Assert::nonEmptyString($this->password, 'password');
    }

    /**
     * @return array{password: string, username?: string}
     */
    public function toArray(): array
    {
        $data = ['password' => $this->password];

        if ($this->username !== null) {
            $data['username'] = $this->username;
        }

        return $data;
    }
}
