<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Concerns;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

trait HasTranslationsRepeater
{
    public static function translationsRepeater(): Repeater
    {
        return Repeater::make('translations')
            ->relationship()
            ->schema([
                Select::make('locale')
                    ->label('Locale')
                    ->options(fn (): array => collect(config('i18n.supported', []))
                        ->mapWithKeys(fn (string $l): array => [$l => mb_strtoupper($l)])
                        ->all())
                    ->required()
                    ->distinct()
                    ->validationMessages([
                        'distinct' => 'Locale bereits vorhanden.',
                    ]),
                TextInput::make('common_name')
                    ->label('Common Name')
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(4),
            ])
            ->itemLabel(fn (array $state): ?string => isset($state['locale']) && $state['locale'] !== ''
                ? mb_strtoupper((string) $state['locale'])
                : null)
            ->collapsible()
            ->defaultItems(0)
            ->addActionLabel('Übersetzung hinzufügen')
            ->columnSpanFull();
    }
}
