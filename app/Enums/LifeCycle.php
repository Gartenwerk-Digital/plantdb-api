<?php

declare(strict_types=1);

namespace App\Enums;

enum LifeCycle: string
{
    case Annual = 'annual';
    case Biennial = 'biennial';
    case Perennial = 'perennial';
    case Evergreen = 'evergreen';
}
