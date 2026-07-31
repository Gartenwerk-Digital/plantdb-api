<?php

declare(strict_types=1);

namespace App\Enums;

enum PlantImageType: string
{
    case Portrait = 'portrait';
    case Detail = 'detail';
    case Fruit = 'fruit';
    case Flower = 'flower';
    case Leaf = 'leaf';
    case Bark = 'bark';
}
