<?php

declare(strict_types=1);

namespace App\Enums;

enum SunRequirement: string
{
    case FullSun = 'full_sun';
    case PartialShade = 'partial_shade';
    case FullShade = 'full_shade';
}
