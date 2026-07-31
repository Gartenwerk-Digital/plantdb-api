<?php

declare(strict_types=1);

namespace App\Enums;

enum SoilMoisture: string
{
    case Dry = 'dry';
    case Normal = 'normal';
    case Moist = 'moist';
    case Wet = 'wet';
}
