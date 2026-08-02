<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlantResource\Pages;

use App\Filament\Admin\Resources\PlantResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListPlants extends ListRecords
{
    protected static string $resource = PlantResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
