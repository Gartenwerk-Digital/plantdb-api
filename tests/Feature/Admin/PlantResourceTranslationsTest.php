<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\PlantResource\Pages\EditPlant;
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

it('creates DE and EN translations for a plant', function (): void {
    $plant = Plant::factory()->create();

    Livewire::test(EditPlant::class, ['record' => $plant->getRouteKey()])
        ->fillForm([
            'translations' => [
                ['locale' => 'de', 'common_name' => 'Tomate', 'description' => 'Rot und rund.'],
                ['locale' => 'en', 'common_name' => 'Tomato', 'description' => 'Red and round.'],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($plant->fresh()->translations)->toHaveCount(2);
    expect(PlantTranslation::query()->where('plant_id', $plant->id)->where('locale', 'de')->value('common_name'))
        ->toBe('Tomate');
    expect(PlantTranslation::query()->where('plant_id', $plant->id)->where('locale', 'en')->value('common_name'))
        ->toBe('Tomato');
});

it('updates an existing plant translation', function (): void {
    $plant = Plant::factory()->create();
    PlantTranslation::query()->create([
        'plant_id' => $plant->id,
        'locale' => 'de',
        'common_name' => 'Alter Name',
        'description' => null,
    ]);

    $existing = $plant->fresh()->translations->first();

    Livewire::test(EditPlant::class, ['record' => $plant->getRouteKey()])
        ->fillForm([
            'translations' => [
                ['locale' => 'de', 'common_name' => 'Neuer Name', 'description' => 'Beschrieb.'],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(PlantTranslation::query()->where('plant_id', $plant->id)->count())->toBe(1);
    expect(PlantTranslation::query()->where('plant_id', $plant->id)->where('locale', 'de')->value('common_name'))
        ->toBe('Neuer Name');
});

it('rejects duplicate locales in the plant translations repeater', function (): void {
    $plant = Plant::factory()->create();

    Livewire::test(EditPlant::class, ['record' => $plant->getRouteKey()])
        ->fillForm([
            'translations' => [
                ['locale' => 'de', 'common_name' => 'A', 'description' => null],
                ['locale' => 'de', 'common_name' => 'B', 'description' => null],
            ],
        ])
        ->call('save')
        ->assertHasFormErrors();

    expect(PlantTranslation::query()->where('plant_id', $plant->id)->count())->toBe(0);
});
