<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\FamilyResource\Pages\EditFamily;
use App\Models\Family;
use App\Models\FamilyTranslation;
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

it('creates DE and EN translations for a family', function (): void {
    $family = Family::factory()->create();

    Livewire::test(EditFamily::class, ['record' => $family->getRouteKey()])
        ->fillForm([
            'translations' => [
                ['locale' => 'de', 'common_name' => 'Rosengewächse', 'description' => 'DE'],
                ['locale' => 'en', 'common_name' => 'Rose family', 'description' => 'EN'],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($family->fresh()->translations)->toHaveCount(2);
});

it('updates an existing family translation', function (): void {
    $family = Family::factory()->create();
    FamilyTranslation::query()->create([
        'family_id' => $family->id,
        'locale' => 'de',
        'common_name' => 'Alt',
        'description' => null,
    ]);

    Livewire::test(EditFamily::class, ['record' => $family->getRouteKey()])
        ->fillForm([
            'translations' => [
                ['locale' => 'de', 'common_name' => 'Neu', 'description' => null],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(FamilyTranslation::query()->where('family_id', $family->id)->where('locale', 'de')->value('common_name'))
        ->toBe('Neu');
});

it('rejects duplicate locales in the family translations repeater', function (): void {
    $family = Family::factory()->create();

    Livewire::test(EditFamily::class, ['record' => $family->getRouteKey()])
        ->fillForm([
            'translations' => [
                ['locale' => 'de', 'common_name' => 'A', 'description' => null],
                ['locale' => 'de', 'common_name' => 'B', 'description' => null],
            ],
        ])
        ->call('save')
        ->assertHasFormErrors();

    expect(FamilyTranslation::query()->where('family_id', $family->id)->count())->toBe(0);
});
