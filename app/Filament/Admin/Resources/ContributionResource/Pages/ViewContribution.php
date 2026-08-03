<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ContributionResource\Pages;

use App\Actions\Contributions\ApproveContribution;
use App\Actions\Contributions\RejectContribution;
use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Filament\Admin\Resources\ContributionResource;
use App\Models\Contribution;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;

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
                Section::make('Pending image')
                    ->visible(fn (Contribution $record): bool => $record->type === ContributionType::Image)
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('pending_image')
                            ->collection(Contribution::MEDIA_PENDING_IMAGE)
                            ->height(300)
                            ->label('Uploaded image'),
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
            EditAction::make()->visible(fn (Contribution $record): bool => $record->status === ContributionStatus::Pending),
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (Contribution $record): bool => $record->status === ContributionStatus::Pending)
                ->requiresConfirmation()
                ->action(function (Contribution $record): void {
                    /** @var User $reviewer */
                    $reviewer = auth()->user();

                    try {
                        resolve(ApproveContribution::class)($record, $reviewer);
                    } catch (Throwable $throwable) {
                        Notification::make()
                            ->title('Approve failed')
                            ->body($throwable->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Contribution approved')
                        ->success()
                        ->send();
                }),
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (Contribution $record): bool => $record->status === ContributionStatus::Pending)
                ->form([
                    Textarea::make('review_notes')
                        ->required()
                        ->rows(3)
                        ->label('Reviewer notes (sent to submitter)'),
                ])
                ->action(function (array $data, Contribution $record): void {
                    /** @var User $reviewer */
                    $reviewer = auth()->user();

                    resolve(RejectContribution::class)($record, $reviewer, (string) $data['review_notes']);

                    Notification::make()
                        ->title('Contribution rejected')
                        ->success()
                        ->send();
                }),
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
