<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\FamilyResource\Pages;

use App\Filament\Admin\Resources\FamilyResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditFamily extends EditRecord
{
    protected static string $resource = FamilyResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
