<?php

declare(strict_types=1);

namespace App\Enums;

enum PlantImageLicense: string
{
    case Cc0 = 'CC0-1.0';
    case CcBy = 'CC-BY-4.0';
    case CcBySa = 'CC-BY-SA-4.0';
    case CcByNc = 'CC-BY-NC-4.0';
    case CcByNd = 'CC-BY-ND-4.0';
    case PublicDomain = 'public-domain';
    case AllRightsReserved = 'all-rights-reserved';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public function label(): string
    {
        return match ($this) {
            self::Cc0 => 'CC0 1.0 – Public Domain Dedication',
            self::CcBy => 'CC BY 4.0 – Namensnennung',
            self::CcBySa => 'CC BY-SA 4.0 – Namensnennung, Weitergabe unter gleichen Bedingungen',
            self::CcByNc => 'CC BY-NC 4.0 – Namensnennung, nicht kommerziell',
            self::CcByNd => 'CC BY-ND 4.0 – Namensnennung, keine Bearbeitung',
            self::PublicDomain => 'Public Domain (gemeinfrei)',
            self::AllRightsReserved => 'Alle Rechte vorbehalten',
        };
    }

    public function requiresAttribution(): bool
    {
        return match ($this) {
            self::Cc0, self::PublicDomain => false,
            default => true,
        };
    }
}
