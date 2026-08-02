<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlantResource\Pages;

use App\Filament\Admin\Resources\PlantResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditPlant extends EditRecord
{
    protected static string $resource = PlantResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
