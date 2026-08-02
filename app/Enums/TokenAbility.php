<?php

declare(strict_types=1);

namespace App\Enums;

enum TokenAbility: string
{
    case Read = 'read';
    case Write = 'write';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
