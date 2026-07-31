<?php

declare(strict_types=1);

namespace App\Enums;

enum ContributionType: string
{
    case NewPlant = 'new_plant';
    case Update = 'update';
    case Image = 'image';
    case Correction = 'correction';
}
