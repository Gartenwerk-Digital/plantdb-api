<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ContributionResource\Pages;

use App\Filament\Admin\Resources\ContributionResource;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditContribution extends EditRecord
{
    protected static string $resource = ContributionResource::class;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
