<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\GenusResource\Pages;

use App\Filament\Admin\Resources\GenusResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewGenus extends ViewRecord
{
    protected static string $resource = GenusResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
