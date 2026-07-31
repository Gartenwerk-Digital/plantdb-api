<?php

declare(strict_types=1);

namespace App\Enums;

enum PestDiseaseType: string
{
    case Pest = 'pest';
    case Disease = 'disease';
    case Fungus = 'fungus';
}
