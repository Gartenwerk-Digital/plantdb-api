<?php

declare(strict_types=1);

namespace App\Enums;

enum CareTaskType: string
{
    case Watering = 'watering';
    case Fertilizing = 'fertilizing';
    case Pruning = 'pruning';
    case Repotting = 'repotting';
    case Sowing = 'sowing';
    case Harvesting = 'harvesting';
    case Mulching = 'mulching';
    case Dividing = 'dividing';
}
