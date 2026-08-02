<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\FamilyResource\Pages;

use App\Filament\Admin\Resources\FamilyResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewFamily extends ViewRecord
{
    protected static string $resource = FamilyResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
