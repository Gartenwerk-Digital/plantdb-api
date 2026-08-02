<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\FamilyResource\Pages;

use App\Filament\Admin\Resources\FamilyResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateFamily extends CreateRecord
{
    protected static string $resource = FamilyResource::class;
}
