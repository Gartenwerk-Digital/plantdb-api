<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\PlantResource\Pages\CreatePlant;
use App\Filament\Admin\Resources\PlantResource\Pages\ListPlants;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
});

it('renders the plant list page with status badge', function (): void {
    $plants = Plant::factory()->count(3)->create();

    Livewire::test(ListPlants::class)
        ->assertOk()
        ->assertCanSeeTableRecords($plants)
        ->assertTableColumnExists('status');
});

it('creates a plant with minimal required fields', function (): void {
    $family = Family::factory()->create();
    $genus = Genus::factory()->create(['family_id' => $family->id]);

    Livewire::test(CreatePlant::class)
        ->fillForm([
            'scientific_name' => 'Rosa damascena',
            'slug' => 'rosa-damascena',
            'family_id' => $family->id,
            'genus_id' => $genus->id,
            'status' => PlantStatus::Draft->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Plant::query()->where('slug', 'rosa-damascena')->exists())->toBeTrue();
});

it('approves a plant via table action', function (): void {
    $plant = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(ListPlants::class)
        ->callTableAction('approve', $plant);

    $plant->refresh();

    expect($plant->status)->toBe(PlantStatus::Approved)
        ->and($plant->reviewed_by)->toBe($this->admin->id)
        ->and($plant->reviewed_at)->not->toBeNull();
});

it('rejects a plant via table action with required review_notes', function (): void {
    $plant = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(ListPlants::class)
        ->callTableAction('reject', $plant, data: ['review_notes' => 'Insufficient data'])
        ->assertHasNoTableActionErrors();

    $plant->refresh();

    expect($plant->status)->toBe(PlantStatus::Rejected)
        ->and($plant->reviewed_by)->toBe($this->admin->id)
        ->and($plant->review_notes)->toBe('Insufficient data');
});

it('requires review_notes when rejecting a plant', function (): void {
    $plant = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(ListPlants::class)
        ->callTableAction('reject', $plant, data: ['review_notes' => ''])
        ->assertHasTableActionErrors(['review_notes']);

    expect($plant->refresh()->status)->toBe(PlantStatus::PendingReview);
});

it('bulk approves plants', function (): void {
    $plants = Plant::factory()->count(3)->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(ListPlants::class)
        ->callTableBulkAction('approve', $plants);

    Plant::query()->whereIn('id', $plants->pluck('id'))->get()->each(function (Plant $plant): void {
        expect($plant->status)->toBe(PlantStatus::Approved)
            ->and($plant->reviewed_by)->toBe($this->admin->id);
    });
});

it('bulk rejects plants with review_notes', function (): void {
    $plants = Plant::factory()->count(2)->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(ListPlants::class)
        ->callTableBulkAction('reject', $plants, data: ['review_notes' => 'Batch reject'])
        ->assertHasNoTableBulkActionErrors();

    Plant::query()->whereIn('id', $plants->pluck('id'))->get()->each(function (Plant $plant): void {
        expect($plant->status)->toBe(PlantStatus::Rejected)
            ->and($plant->review_notes)->toBe('Batch reject');
    });
});

it('filters plants by status', function (): void {
    $approved = Plant::factory()->create(['status' => PlantStatus::Approved->value]);
    $drafts = Plant::factory()->count(2)->create(['status' => PlantStatus::Draft->value]);

    Livewire::test(ListPlants::class)
        ->filterTable('status', PlantStatus::Approved->value)
        ->assertCanSeeTableRecords([$approved])
        ->assertCanNotSeeTableRecords($drafts);
});
