<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ContributionResource\Pages;

use App\Filament\Admin\Resources\ContributionResource;
use Filament\Resources\Pages\ListRecords;

final class ListContributions extends ListRecords
{
    protected static string $resource = ContributionResource::class;
}
