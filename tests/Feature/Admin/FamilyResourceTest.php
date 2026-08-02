<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\FamilyResource;
use App\Filament\Admin\Resources\FamilyResource\Pages\CreateFamily;
use App\Filament\Admin\Resources\FamilyResource\Pages\EditFamily;
use App\Filament\Admin\Resources\FamilyResource\Pages\ListFamilies;
use App\Models\Family;
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

it('renders the family list page', function (): void {
    $families = Family::factory()->count(3)->create();

    Livewire::test(ListFamilies::class)
        ->assertOk()
        ->assertCanSeeTableRecords($families);
});

it('creates a family and auto-generates a slug from the name', function (): void {
    Livewire::test(CreateFamily::class)
        ->fillForm([
            'name' => 'Rosaceae',
            'slug' => 'rosaceae',
            'description' => 'Rose family.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Family::query()->where('slug', 'rosaceae')->exists())->toBeTrue();
});

it('validates slug uniqueness on create', function (): void {
    Family::factory()->create(['slug' => 'rosaceae']);

    Livewire::test(CreateFamily::class)
        ->fillForm([
            'name' => 'Rosaceae Duplicate',
            'slug' => 'rosaceae',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('updates an existing family', function (): void {
    $family = Family::factory()->create(['name' => 'Old', 'slug' => 'old']);

    Livewire::test(EditFamily::class, ['record' => $family->getRouteKey()])
        ->fillForm([
            'name' => 'New Name',
            'slug' => 'new-name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($family->refresh()->name)->toBe('New Name');
});

it('deletes a family without plants', function (): void {
    $family = Family::factory()->create();

    Livewire::test(EditFamily::class, ['record' => $family->getRouteKey()])
        ->callAction('delete');

    expect(Family::query()->whereKey($family->id)->exists())->toBeFalse();
});

it('does not allow deleting a family that still has plants', function (): void {
    $family = Family::factory()->create();
    Plant::factory()->create(['family_id' => $family->id]);

    expect($this->admin->can('delete', $family))->toBeFalse();
});

it('shows only approved plants in the count column', function (): void {
    $family = Family::factory()->create();
    Plant::factory()->create(['family_id' => $family->id, 'status' => PlantStatus::Approved->value]);
    Plant::factory()->create(['family_id' => $family->id, 'status' => PlantStatus::Draft->value]);
    Plant::factory()->create(['family_id' => $family->id, 'status' => PlantStatus::PendingReview->value]);

    $record = FamilyResource::getEloquentQuery()
        ->withCount(['plants' => fn ($q) => $q->where('status', PlantStatus::Approved)])
        ->find($family->id);

    expect($record->plants_count)->toBe(1);
});
