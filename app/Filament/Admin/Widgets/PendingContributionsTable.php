<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Filament\Admin\Resources\ContributionResource;
use App\Models\Contribution;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

final class PendingContributionsTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Offene Contributions';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Contribution::query()
                    ->where('status', ContributionStatus::Pending)
                    ->with(['submitter', 'plant'])
                    ->latest('created_at')
            )
            ->paginated([10, 25])
            ->columns([
                TextColumn::make('submitter.name')
                    ->label('Autor')
                    ->placeholder('Anonym'),
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (ContributionType $state): string => match ($state) {
                        ContributionType::NewPlant => 'info',
                        ContributionType::Update => 'primary',
                        ContributionType::Correction => 'warning',
                        ContributionType::Image => 'gray',
                    }),
                TextColumn::make('plant.scientific_name')
                    ->label('Pflanze')
                    ->placeholder('Neue Pflanze'),
                TextColumn::make('created_at')
                    ->label('Eingereicht')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                ContributionResource::approveTableAction(),
                ContributionResource::rejectTableAction(),
            ])
            ->emptyStateHeading('Alle Beiträge bearbeitet')
            ->emptyStateDescription('Aktuell warten keine Contributions auf Review.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
