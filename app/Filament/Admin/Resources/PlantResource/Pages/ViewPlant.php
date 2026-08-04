<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlantResource\Pages;

use App\Filament\Admin\Resources\PlantResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewPlant extends ViewRecord
{
    protected static string $resource = PlantResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            PlantResource::approveHeaderAction(),
            PlantResource::rejectHeaderAction(),
        ];
    }
}
