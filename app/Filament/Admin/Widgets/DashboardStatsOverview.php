<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\ContributionStatus;
use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\ContributionResource;
use App\Filament\Admin\Resources\PlantResource;
use App\Models\Contribution;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

final class DashboardStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    /** @return array<int, Stat> */
    protected function getStats(): array
    {
        $counts = Cache::remember('admin.dashboard.stats', now()->addMinutes(5), fn (): array => [
            'plants_total' => Plant::query()->count(),
            'plants_approved' => Plant::query()->where('status', PlantStatus::Approved)->count(),
            'plants_pending' => Plant::query()->where('status', PlantStatus::PendingReview)->count(),
            'contributions_pending' => Contribution::query()->where('status', ContributionStatus::Pending)->count(),
            'families' => Family::query()->count(),
            'genera' => Genus::query()->count(),
        ]);

        return [
            Stat::make('Pflanzen gesamt', (string) $counts['plants_total'])
                ->description($counts['plants_approved'].' freigegeben')
                ->descriptionIcon('heroicon-o-check-badge')
                ->icon('heroicon-o-squares-2x2')
                ->color('primary'),

            Stat::make('Wartet auf Review', (string) $counts['plants_pending'])
                ->description($counts['plants_pending'] > 0 ? 'Zum Review-Filter springen' : 'Alles bearbeitet')
                ->descriptionIcon('heroicon-o-arrow-right')
                ->icon('heroicon-o-clock')
                ->color($counts['plants_pending'] > 0 ? 'warning' : 'success')
                ->url(PlantResource::getUrl('index', [
                    'tableFilters' => ['status' => ['value' => PlantStatus::PendingReview->value]],
                ])),

            Stat::make('Offene Contributions', (string) $counts['contributions_pending'])
                ->description($counts['contributions_pending'] > 0 ? 'Beiträge warten auf Freigabe' : 'Keine offenen Beiträge')
                ->descriptionIcon('heroicon-o-arrow-right')
                ->icon('heroicon-o-inbox')
                ->color($counts['contributions_pending'] > 0 ? 'warning' : 'gray')
                ->url(ContributionResource::getUrl('index', [
                    'tableFilters' => ['status' => ['value' => ContributionStatus::Pending->value]],
                ])),

            Stat::make('Taxonomie', $counts['families'].' Familien')
                ->description($counts['genera'].' Gattungen')
                ->descriptionIcon('heroicon-o-book-open')
                ->icon('heroicon-o-book-open')
                ->color('gray'),
        ];
    }
}
