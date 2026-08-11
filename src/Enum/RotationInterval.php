<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Enum;

enum RotationInterval: string
{
    case all = 'all';
    case high = 'high';
    case oneMinute = '1min';
    case tenMinutes = '10min';
    case thirtyMinutes = '30min';
}
