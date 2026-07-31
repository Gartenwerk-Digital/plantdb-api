<?php

declare(strict_types=1);

namespace App\Enums;

enum FertilizingFrequency: string
{
    case None = 'none';
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
