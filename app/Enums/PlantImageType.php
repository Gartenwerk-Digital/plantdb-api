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

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
