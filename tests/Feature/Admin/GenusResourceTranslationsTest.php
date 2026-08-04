<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\GenusResource\Pages\EditGenus;
use App\Models\Genus;
use App\Models\GenusTranslation;
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

it('creates DE and EN translations for a genus', function (): void {
    $genus = Genus::factory()->create();

    Livewire::test(EditGenus::class, ['record' => $genus->getRouteKey()])
        ->fillForm([
            'translations' => [
                ['locale' => 'de', 'common_name' => 'Rose', 'description' => 'DE'],
                ['locale' => 'en', 'common_name' => 'Rose', 'description' => 'EN'],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($genus->fresh()->translations)->toHaveCount(2);
});

it('updates an existing genus translation', function (): void {
    $genus = Genus::factory()->create();
    GenusTranslation::query()->create([
        'genus_id' => $genus->id,
        'locale' => 'de',
        'common_name' => 'Alt',
        'description' => null,
    ]);

    Livewire::test(EditGenus::class, ['record' => $genus->getRouteKey()])
        ->fillForm([
            'translations' => [
                ['locale' => 'de', 'common_name' => 'Neu', 'description' => null],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(GenusTranslation::query()->where('genus_id', $genus->id)->where('locale', 'de')->value('common_name'))
        ->toBe('Neu');
});

it('rejects duplicate locales in the genus translations repeater', function (): void {
    $genus = Genus::factory()->create();

    Livewire::test(EditGenus::class, ['record' => $genus->getRouteKey()])
        ->fillForm([
            'translations' => [
                ['locale' => 'de', 'common_name' => 'A', 'description' => null],
                ['locale' => 'de', 'common_name' => 'B', 'description' => null],
            ],
        ])
        ->call('save')
        ->assertHasFormErrors();

    expect(GenusTranslation::query()->where('genus_id', $genus->id)->count())->toBe(0);
});
