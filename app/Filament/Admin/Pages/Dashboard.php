<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\DashboardStatsOverview;
use App\Filament\Admin\Widgets\PendingContributionsTable;
use App\Filament\Admin\Widgets\PendingPlantsTable;
use Filament\Pages\Dashboard as BaseDashboard;

final class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Übersicht';

    protected static ?string $navigationLabel = 'Übersicht';

    /** @return array<int, class-string> */
    public function getWidgets(): array
    {
        return [
            DashboardStatsOverview::class,
            PendingPlantsTable::class,
            PendingContributionsTable::class,
        ];
    }
}
