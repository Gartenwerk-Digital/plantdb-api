<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;
}
