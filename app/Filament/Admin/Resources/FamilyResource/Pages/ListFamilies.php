<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\FamilyResource\Pages;

use App\Filament\Admin\Resources\FamilyResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListFamilies extends ListRecords
{
    protected static string $resource = FamilyResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
