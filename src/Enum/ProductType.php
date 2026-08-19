<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Enum;

use InvalidArgumentException;
use ValueError;

enum ProductType: string
{
    case DedicatedProxy = 'dedicated_proxy';
    case ResidentialProxy = 'residential_proxy';
    case PaygResidentialProxy = 'payg_residential_proxy';
    case StaticResidentialProxy = 'static_residential_proxy';
    case MobileProxy = 'mobile_proxy';

    public static function normalize(self|string $type): self
    {
        if ($type instanceof self) {
            return $type;
        }

        trigger_error(
            'Passing product or service types as strings is deprecated. Use ProductType enum cases instead.',
            E_USER_DEPRECATED,
        );

        try {
            return self::from($type);
        } catch (ValueError $exception) {
            throw new InvalidArgumentException(
                sprintf('Unsupported product or service type "%s".', $type),
                previous: $exception,
            );
        }
    }
}
