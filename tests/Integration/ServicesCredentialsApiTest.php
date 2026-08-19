<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Request\Service\UpdateCredentialsRequest;
use Aceproxies\ResellerApi\Response\Service\CredentialsResponse;

final class ServicesCredentialsApiTest extends StagingTestCase
{
    public function testGetsServiceCredentialsAndUpdatesThemInFullMode(): void
    {
        if (!$this->fullMode()) {
            $result = $this->findService(
                fn (string $code): ?CredentialsResponse => $this->client->services()->getCredentials($code),
                'service credentials',
            );

            self::assertInstanceOf(CredentialsResponse::class, $result['response']);

            return;
        }

        $password = 'integration-' . bin2hex(random_bytes(12));
        $result = $this->mutateService(
            function (string $code) use ($password): ?CredentialsResponse {
                if ($this->client->services()->getCredentials($code) === null) {
                    return null;
                }

                return $this->client->services()->updateCredentials(
                    $code,
                    new UpdateCredentialsRequest($password),
                );
            },
            'service credentials',
        );

        self::assertInstanceOf(CredentialsResponse::class, $result['response']);
        self::assertSame($password, $result['response']->password);
    }
}
