<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\PlantResource;
use App\Models\Plant;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Collection;

final class PendingPlantsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Zuletzt eingereichte Pflanzen';

    public static function canView(): bool
    {
        return Plant::query()->where('status', PlantStatus::PendingReview)->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Plant::query()
                    ->where('status', PlantStatus::PendingReview)
                    ->with(['translations' => fn ($q) => $q->where('locale', 'de'), 'family'])
                    ->latest('created_at')
            )
            ->paginated([10, 25])
            ->columns([
                TextColumn::make('scientific_name')
                    ->label('Wissenschaftlicher Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('common_name_de')
                    ->label('Deutscher Name')
                    ->getStateUsing(fn (Plant $record): ?string => $record->translations->firstWhere('locale', 'de')?->common_name)
                    ->placeholder('—'),
                TextColumn::make('family.name')
                    ->label('Familie')
                    ->toggleable(),
                TextColumn::make('import_source')
                    ->label('Quelle')
                    ->badge()
                    ->color('gray')
                    ->placeholder('manuell'),
                TextColumn::make('created_at')
                    ->label('Eingereicht')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                TableAction::make('approve')
                    ->label('Freigeben')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Pflanze freigeben?')
                    ->action(function (Plant $record): void {
                        $record->forceFill([
                            'status' => PlantStatus::Approved,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ])->save();

                        Notification::make()
                            ->title('Pflanze freigegeben')
                            ->success()
                            ->send();
                    }),
                TableAction::make('reject')
                    ->label('Ablehnen')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        Textarea::make('review_notes')
                            ->label('Begründung')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $data, Plant $record): void {
                        $record->forceFill([
                            'status' => PlantStatus::Rejected,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'review_notes' => (string) $data['review_notes'],
                        ])->save();

                        Notification::make()
                            ->title('Pflanze abgelehnt')
                            ->success()
                            ->send();
                    }),
                TableAction::make('open')
                    ->label('Öffnen')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Plant $record): string => PlantResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Ausgewählte freigeben')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (Plant $plant): void {
                                $plant->forceFill([
                                    'status' => PlantStatus::Approved,
                                    'reviewed_by' => auth()->id(),
                                    'reviewed_at' => now(),
                                ])->save();
                            });

                            Notification::make()
                                ->title(sprintf('%d Pflanzen freigegeben', $records->count()))
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
