<?php

declare(strict_types=1);

namespace App\Enums;

enum UserTier: string
{
    case Free = 'free';
    case Pro = 'pro';
    case Enterprise = 'enterprise';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function dailyLimit(): int
    {
        return match ($this) {
            self::Free => 1_000,
            self::Pro => 50_000,
            self::Enterprise => 0,
        };
    }

    public function isUnlimited(): bool
    {
        return $this === self::Enterprise;
    }
}
