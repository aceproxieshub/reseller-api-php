<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Request\Service\CreateWhitelistedIpRequest;
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpResponse;
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpsResponse;

final class ServicesWhitelistApiTest extends StagingTestCase
{
    public function testGetsAndManagesWhitelistedIps(): void
    {
        if (!$this->fullMode()) {
            $result = $this->findService(
                fn (string $code): WhitelistedIpsResponse => $this->client->services()->getWhitelistedIps($code),
                'whitelisted IPs',
            );

            self::assertInstanceOf(WhitelistedIpsResponse::class, $result['response']);

            return;
        }

        $ip = null;
        $serviceCode = null;
        $added = false;

        try {
            $result = $this->mutateService(
                function (string $code) use (&$ip, &$serviceCode): ?WhitelistedIpResponse {
                    $existing = array_map(
                        static fn (WhitelistedIpResponse $item): string => $item->ip,
                        $this->client->services()->getWhitelistedIps($code)->items,
                    );

                    for ($lastOctet = 1; $lastOctet <= 254; $lastOctet++) {
                        $candidate = '192.0.2.' . $lastOctet;

                        if (!in_array($candidate, $existing, true)) {
                            $ip = $candidate;
                            $serviceCode = $code;
                            return $this->client->services()->addWhitelistedIp(
                                $code,
                                new CreateWhitelistedIpRequest($candidate),
                            );
                        }
                    }

                    return null;
                },
                'whitelisted IPs',
            );
            $added = true;

            self::assertSame($ip, $result['response']->ip);
        } finally {
            if ($added && $serviceCode !== null && $ip !== null) {
                $this->client->services()->deleteWhitelistedIp($serviceCode, $ip);
            }
        }
    }
}
