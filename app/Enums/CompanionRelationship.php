<?php

declare(strict_types=1);

namespace App\Enums;

enum CompanionRelationship: string
{
    case Beneficial = 'beneficial';
    case Neutral = 'neutral';
    case Incompatible = 'incompatible';
}
