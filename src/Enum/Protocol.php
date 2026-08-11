<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Enum;

enum Protocol: string
{
    case socks5 = 'socks5';
    case http = 'http';
}
