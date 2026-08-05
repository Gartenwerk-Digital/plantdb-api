<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\PlantResource\Pages\CreatePlant;
use App\Filament\Admin\Resources\PlantResource\Pages\EditPlant;
use App\Filament\Admin\Resources\PlantResource\Pages\ListPlants;
use App\Filament\Admin\Resources\PlantResource\Pages\ViewPlant;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\PlantTranslation;
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
        ->removeTableFilter('status')
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

it('filters plants by status', function (): void {
    $approved = Plant::factory()->create(['status' => PlantStatus::Approved->value]);
    $drafts = Plant::factory()->count(2)->create(['status' => PlantStatus::Draft->value]);

    Livewire::test(ListPlants::class)
        ->filterTable('status', PlantStatus::Approved->value)
        ->assertCanSeeTableRecords([$approved])
        ->assertCanNotSeeTableRecords($drafts);
});

it('defaults the status filter to pending_review on the list page', function (): void {
    $pending = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);
    $approved = Plant::factory()->create(['status' => PlantStatus::Approved->value]);
    $draft = Plant::factory()->create(['status' => PlantStatus::Draft->value]);

    Livewire::test(ListPlants::class)
        ->assertCanSeeTableRecords([$pending])
        ->assertCanNotSeeTableRecords([$approved, $draft]);
});

it('does not expose approve/reject as row actions', function (): void {
    $plant = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(ListPlants::class)
        ->assertTableActionDoesNotExist('approve')
        ->assertTableActionDoesNotExist('reject');

    expect($plant->refresh()->status)->toBe(PlantStatus::PendingReview);
});

it('approves a plant from the EditPlant header action', function (): void {
    $plant = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(EditPlant::class, ['record' => $plant->getRouteKey()])
        ->callAction('approve')
        ->assertNotified(__('admin.plants.notifications.approved'));

    $plant->refresh();

    expect($plant->status)->toBe(PlantStatus::Approved)
        ->and($plant->reviewed_by)->toBe($this->admin->id)
        ->and($plant->reviewed_at)->not->toBeNull();
});

it('rejects a plant from the EditPlant header action with review_notes', function (): void {
    $plant = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(EditPlant::class, ['record' => $plant->getRouteKey()])
        ->callAction('reject', data: ['review_notes' => 'Missing sources'])
        ->assertNotified(__('admin.plants.notifications.rejected'));

    $plant->refresh();

    expect($plant->status)->toBe(PlantStatus::Rejected)
        ->and($plant->review_notes)->toBe('Missing sources');
});

it('approves a plant from the ViewPlant header action', function (): void {
    $plant = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(ViewPlant::class, ['record' => $plant->getRouteKey()])
        ->callAction('approve')
        ->assertNotified(__('admin.plants.notifications.approved'));

    expect($plant->refresh()->status)->toBe(PlantStatus::Approved);
});

it('requires review_notes on the reject header action', function (): void {
    $plant = Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);

    Livewire::test(EditPlant::class, ['record' => $plant->getRouteKey()])
        ->callAction('reject', data: ['review_notes' => ''])
        ->assertHasActionErrors(['review_notes']);

    expect($plant->refresh()->status)->toBe(PlantStatus::PendingReview);
});

it('finds a plant by its german common name via the search column', function (): void {
    $tomato = Plant::factory()->create(['scientific_name' => 'Solanum lycopersicum', 'status' => PlantStatus::Approved->value]);
    PlantTranslation::query()->create([
        'plant_id' => $tomato->id,
        'locale' => 'de',
        'common_name' => 'Tomate',
        'description' => null,
    ]);

    $other = Plant::factory()->create(['scientific_name' => 'Zea mays', 'status' => PlantStatus::Approved->value]);

    Livewire::test(ListPlants::class)
        ->filterTable('status', PlantStatus::Approved->value)
        ->searchTable('Tomate')
        ->assertCanSeeTableRecords([$tomato])
        ->assertCanNotSeeTableRecords([$other]);
});
