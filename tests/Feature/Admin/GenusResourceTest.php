<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\GenusResource;
use App\Filament\Admin\Resources\GenusResource\Pages\CreateGenus;
use App\Filament\Admin\Resources\GenusResource\Pages\EditGenus;
use App\Filament\Admin\Resources\GenusResource\Pages\ListGenera;
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

it('renders the genus list page', function (): void {
    $genera = Genus::factory()->count(3)->create();

    Livewire::test(ListGenera::class)
        ->assertOk()
        ->assertCanSeeTableRecords($genera);
});

it('creates a genus with the given slug', function (): void {
    $family = Family::factory()->create();

    Livewire::test(CreateGenus::class)
        ->fillForm([
            'family_id' => $family->id,
            'name' => 'Rosa',
            'slug' => 'rosa',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Genus::query()->where('slug', 'rosa')->exists())->toBeTrue();
});

it('validates slug uniqueness on create', function (): void {
    $family = Family::factory()->create();
    Genus::factory()->create(['slug' => 'rosa']);

    Livewire::test(CreateGenus::class)
        ->fillForm([
            'family_id' => $family->id,
            'name' => 'Rosa Two',
            'slug' => 'rosa',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('requires a family on create', function (): void {
    Livewire::test(CreateGenus::class)
        ->fillForm([
            'name' => 'Orphan',
            'slug' => 'orphan',
        ])
        ->call('create')
        ->assertHasFormErrors(['family_id']);
});

it('updates an existing genus', function (): void {
    $genus = Genus::factory()->create(['name' => 'Old', 'slug' => 'old']);

    Livewire::test(EditGenus::class, ['record' => $genus->getRouteKey()])
        ->fillForm([
            'family_id' => $genus->family_id,
            'name' => 'New',
            'slug' => 'new',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($genus->refresh()->name)->toBe('New');
});

it('deletes a genus without plants', function (): void {
    $genus = Genus::factory()->create();

    Livewire::test(EditGenus::class, ['record' => $genus->getRouteKey()])
        ->callAction('delete');

    expect(Genus::query()->whereKey($genus->id)->exists())->toBeFalse();
});

it('does not allow deleting a genus that still has plants', function (): void {
    $genus = Genus::factory()->create();
    Plant::factory()->create([
        'family_id' => $genus->family_id,
        'genus_id' => $genus->id,
    ]);

    expect($this->admin->can('delete', $genus))->toBeFalse();
});

it('shows only approved plants in the count column', function (): void {
    $genus = Genus::factory()->create();
    Plant::factory()->create([
        'family_id' => $genus->family_id,
        'genus_id' => $genus->id,
        'status' => PlantStatus::Approved->value,
    ]);
    Plant::factory()->create([
        'family_id' => $genus->family_id,
        'genus_id' => $genus->id,
        'status' => PlantStatus::Draft->value,
    ]);

    $record = GenusResource::getEloquentQuery()
        ->withCount(['plants' => fn ($q) => $q->where('status', PlantStatus::Approved)])
        ->find($genus->id);

    expect($record->plants_count)->toBe(1);
});
