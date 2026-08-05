<?php

declare(strict_types=1);

use App\Enums\ContributionStatus;
use App\Enums\PlantStatus;
use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Widgets\DashboardStatsOverview;
use App\Filament\Admin\Widgets\PendingContributionsTable;
use App\Filament\Admin\Widgets\PendingPlantsTable;
use App\Models\Contribution;
use App\Models\Plant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    Role::findOrCreate('admin', 'web');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Cache::forget('admin.dashboard.stats');
});

it('renders the dashboard for an admin user', function (): void {
    $this->get('/admin')->assertOk();

    Livewire::test(DashboardStatsOverview::class)
        ->assertSee('Pflanzen gesamt')
        ->assertSee('Wartet auf Review')
        ->assertSee('Offene Contributions')
        ->assertSee('Taxonomie');
});

it('shows the correct pending count in the stats overview', function (): void {
    Plant::factory()->count(3)->create(['status' => PlantStatus::Approved->value]);
    Plant::factory()->count(2)->create(['status' => PlantStatus::PendingReview->value]);
    Cache::forget('admin.dashboard.stats');

    Livewire::test(DashboardStatsOverview::class)
        ->assertSee('Pflanzen gesamt')
        ->assertSee('3 freigegeben')
        ->assertSee('Wartet auf Review');
});

it('approves a pending plant from the widget action', function (): void {
    $plant = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(PendingPlantsTable::class)
        ->callTableAction('approve', $plant);

    expect($plant->fresh()->status)->toBe(PlantStatus::Approved)
        ->and($plant->fresh()->reviewed_by)->toBe($this->admin->id);
});

it('rejects a pending plant with review notes', function (): void {
    $plant = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(PendingPlantsTable::class)
        ->callTableAction('reject', $plant, data: ['review_notes' => 'Duplikat']);

    $plant->refresh();
    expect($plant->status)->toBe(PlantStatus::Rejected)
        ->and($plant->review_notes)->toBe('Duplikat');
});

it('hides the pending plants widget when nothing is pending', function (): void {
    expect(PendingPlantsTable::canView())->toBeFalse();

    Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);
    expect(PendingPlantsTable::canView())->toBeTrue();
});

it('shows the empty state on the contributions widget when nothing is pending', function (): void {
    Livewire::test(PendingContributionsTable::class)
        ->assertSee('Alle Beiträge bearbeitet');
});

it('lists pending contributions in the widget', function (): void {
    $pending = Contribution::factory()->create();
    $approved = Contribution::factory()->create(['status' => ContributionStatus::Approved->value]);

    Livewire::test(PendingContributionsTable::class)
        ->assertCanSeeTableRecords([$pending])
        ->assertCanNotSeeTableRecords([$approved]);
});

it('registers the three dashboard widgets in the expected order', function (): void {
    $widgets = (new Dashboard())->getWidgets();

    expect($widgets)->toBe([
        DashboardStatsOverview::class,
        PendingPlantsTable::class,
        PendingContributionsTable::class,
    ]);
});
