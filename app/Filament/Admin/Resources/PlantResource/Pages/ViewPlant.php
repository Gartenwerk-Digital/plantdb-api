<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlantResource\Pages;

use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\PlantResource;
use App\Models\Plant;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

final class ViewPlant extends ViewRecord
{
    protected static string $resource = PlantResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (Plant $record): bool => $record->status !== PlantStatus::Approved)
                ->requiresConfirmation()
                ->action(function (Plant $record): void {
                    $record->forceFill([
                        'status' => PlantStatus::Approved,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])->save();
                }),
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (Plant $record): bool => $record->status !== PlantStatus::Rejected)
                ->form([
                    Textarea::make('review_notes')->required()->rows(3),
                ])
                ->action(function (array $data, Plant $record): void {
                    $record->forceFill([
                        'status' => PlantStatus::Rejected,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                        'review_notes' => $data['review_notes'],
                    ])->save();
                }),
        ];
    }
}
