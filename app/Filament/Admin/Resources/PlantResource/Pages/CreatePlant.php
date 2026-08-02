<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlantResource\Pages;

use App\Filament\Admin\Resources\PlantResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePlant extends CreateRecord
{
    protected static string $resource = PlantResource::class;
}
