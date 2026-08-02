<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\GenusResource\Pages;

use App\Filament\Admin\Resources\GenusResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListGenera extends ListRecords
{
    protected static string $resource = GenusResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
