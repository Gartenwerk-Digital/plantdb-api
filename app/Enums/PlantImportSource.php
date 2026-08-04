<?php

declare(strict_types=1);

namespace App\Enums;

enum PlantImportSource: string
{
    case Gbif = 'gbif';
    case OpenFarm = 'openfarm';
}
