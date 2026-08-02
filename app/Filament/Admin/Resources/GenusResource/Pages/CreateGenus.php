<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\GenusResource\Pages;

use App\Filament\Admin\Resources\GenusResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateGenus extends CreateRecord
{
    protected static string $resource = GenusResource::class;
}
