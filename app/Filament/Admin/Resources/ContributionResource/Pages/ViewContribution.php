<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ContributionResource\Pages;

use App\Enums\ContributionType;
use App\Filament\Admin\Resources\ContributionResource;
use App\Models\Contribution;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

final class ViewContribution extends ViewRecord
{
    protected static string $resource = ContributionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Contribution')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('type')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('plant.scientific_name')
                            ->label('Plant')
                            ->placeholder('— (new plant)'),
                        TextEntry::make('submitter.email')->label('Submitted by'),
                        TextEntry::make('created_at')->dateTime()->label('Submitted at'),
                        TextEntry::make('reviewed_at')->dateTime()->placeholder('—'),
                    ]),
                Section::make('Proposed payload')
                    ->schema([
                        KeyValueEntry::make('payload')
                            ->keyLabel('Field')
                            ->valueLabel('Proposed value'),
                    ]),
                Section::make('Current plant values')
                    ->visible(fn (Contribution $record): bool => in_array($record->type, [ContributionType::Update, ContributionType::Correction], true) && $record->plant !== null)
                    ->schema([
                        KeyValueEntry::make('current_values')
                            ->keyLabel('Field')
                            ->valueLabel('Current value')
                            ->state(fn (Contribution $record): array => $this->currentPlantValues($record)),
                    ]),
                Section::make('Review')
                    ->visible(fn (Contribution $record): bool => $record->review_notes !== null)
                    ->schema([
                        TextEntry::make('review_notes')->label('Reviewer notes'),
                    ]),
            ]);
    }

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn (Contribution $record): bool => $record->status->value === 'pending'),
            ContributionResource::approveTableAction(),
            ContributionResource::rejectTableAction(),
        ];
    }

    /** @return array<string, mixed> */
    private function currentPlantValues(Contribution $record): array
    {
        $plant = $record->plant;

        if ($plant === null) {
            return [];
        }

        $keys = array_keys($record->payload);
        $current = [];

        foreach ($keys as $key) {
            $value = $plant->getAttribute($key);
            $current[$key] = is_scalar($value) || $value === null
                ? (string) ($value ?? '—')
                : json_encode($value);
        }

        return $current;
    }
}
