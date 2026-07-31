<?php

declare(strict_types=1);

namespace App\Enums;

enum RootDepth: string
{
    case Shallow = 'shallow';
    case Medium = 'medium';
    case Deep = 'deep';
}
