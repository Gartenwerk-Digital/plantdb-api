<?php

declare(strict_types=1);

use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\PlantTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

it('exports plants, families and genera as slug-referenced JSON', function (): void {
    $family = Family::factory()->create(['name' => 'Rosaceae', 'slug' => 'rosaceae', 'description' => 'Rose family']);
    $genus = Genus::factory()->create(['name' => 'Rosa', 'slug' => 'rosa', 'family_id' => $family->id]);
    $plant = Plant::factory()->create([
        'slug' => 'rosa-canina',
        'scientific_name' => 'Rosa canina',
        'family_id' => $family->id,
        'genus_id' => $genus->id,
    ]);
    PlantTranslation::query()->create([
        'plant_id' => $plant->id,
        'locale' => 'de',
        'common_name' => 'Hunds-Rose',
    ]);

    $dir = storage_path('framework/testing/export-'.uniqid());

    try {
        $this->artisan('plants:export', ['--output' => $dir])->assertExitCode(0);

        $families = json_decode(File::get($dir.'/families.json'), true);
        $genera = json_decode(File::get($dir.'/genera.json'), true);
        $plants = json_decode(File::get($dir.'/plants.json'), true);

        expect($families)->toHaveCount(1)
            ->and($families[0])->toMatchArray(['slug' => 'rosaceae', 'name' => 'Rosaceae', 'description' => 'Rose family'])
            ->and($genera[0])->toMatchArray(['slug' => 'rosa', 'name' => 'Rosa', 'family_slug' => 'rosaceae'])
            ->and($plants[0]['slug'])->toBe('rosa-canina')
            ->and($plants[0]['family_slug'])->toBe('rosaceae')
            ->and($plants[0]['genus_slug'])->toBe('rosa')
            ->and($plants[0])->not->toHaveKey('id')
            ->and($plants[0])->not->toHaveKey('family_id')
            ->and($plants[0])->not->toHaveKey('search_vector')
            ->and($plants[0]['translations'])->toHaveCount(1)
            ->and($plants[0]['translations'][0])->toMatchArray(['locale' => 'de', 'common_name' => 'Hunds-Rose']);
    } finally {
        File::deleteDirectory($dir);
    }
});
