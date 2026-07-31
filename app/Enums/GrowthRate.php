<?php

declare(strict_types=1);

namespace App\Enums;

enum GrowthRate: string
{
    case Slow = 'slow';
    case Medium = 'medium';
    case Fast = 'fast';
}
