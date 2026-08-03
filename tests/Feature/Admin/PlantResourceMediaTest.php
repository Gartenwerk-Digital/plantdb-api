<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\PlantResource\Pages\EditPlant;
use App\Filament\Admin\Resources\PlantResource\RelationManagers\MediaRelationManager;
use App\Models\Plant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    Storage::fake('public');
});

it('uploads media into a plant collection with license and attribution', function (): void {
    $plant = Plant::factory()->create();

    Livewire::test(MediaRelationManager::class, [
        'ownerRecord' => $plant,
        'pageClass' => EditPlant::class,
    ])
        ->callTableAction('upload', data: [
            'collection_name' => 'portrait',
            'file' => UploadedFile::fake()->image('rose.jpg', 800, 1200),
            'license' => 'CC BY 4.0',
            'attribution' => 'Jane Photographer',
        ])
        ->assertHasNoTableActionErrors();

    $plant->refresh();

    expect($plant->getMedia('portrait'))->toHaveCount(1);

    $media = $plant->getMedia('portrait')->first();

    expect($media->getCustomProperty('license'))->toBe('CC BY 4.0')
        ->and($media->getCustomProperty('attribution'))->toBe('Jane Photographer')
        ->and($media->getCustomProperty('submitted_by'))->toBe($this->admin->id);
});

it('lists uploaded media in the relation manager table', function (): void {
    $plant = Plant::factory()->create();

    $plant->addMedia(UploadedFile::fake()->image('a.jpg', 800, 1200))
        ->withCustomProperties(['license' => 'CC BY 4.0', 'attribution' => 'A'])
        ->toMediaCollection('portrait');

    Livewire::test(MediaRelationManager::class, [
        'ownerRecord' => $plant,
        'pageClass' => EditPlant::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($plant->media);
});
