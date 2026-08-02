<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\GenusResource\Pages;

use App\Filament\Admin\Resources\GenusResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditGenus extends EditRecord
{
    protected static string $resource = GenusResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
