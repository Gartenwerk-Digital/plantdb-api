<?php

declare(strict_types=1);

use App\Actions\Import\ImportPlantFromData;
use App\DTOs\Import\PlantImportData;
use App\Enums\ImportOutcome;
use App\Enums\PlantStatus;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('imports a new plant, auto-creating family and genus', function (): void {
    $action = resolve(ImportPlantFromData::class);

    $result = $action(new PlantImportData(
        scientificName: 'Quercus robur',
        familyName: 'Fagaceae',
        genusName: 'Quercus',
        commonNames: ['de' => 'Stiel-Eiche', 'en' => 'English oak'],
    ));

    expect($result->outcome)->toBe(ImportOutcome::Imported)
        ->and($result->plant)->not->toBeNull()
        ->and($result->plant->status)->toBe(PlantStatus::PendingReview)
        ->and($result->plant->scientific_name)->toBe('Quercus robur')
        ->and(Family::query()->where('slug', 'fagaceae')->exists())->toBeTrue()
        ->and(Genus::query()->where('slug', 'quercus')->exists())->toBeTrue()
        ->and($result->plant->translations()->count())->toBe(2);
});

it('skips duplicates by scientific_name', function (): void {
    $family = Family::factory()->create(['name' => 'Rosaceae', 'slug' => 'rosaceae']);
    $genus = Genus::factory()->create(['name' => 'Rosa', 'slug' => 'rosa', 'family_id' => $family->id]);
    Plant::factory()->create([
        'scientific_name' => 'Rosa canina',
        'family_id' => $family->id,
        'genus_id' => $genus->id,
    ]);

    $action = resolve(ImportPlantFromData::class);

    $result = $action(new PlantImportData(
        scientificName: 'Rosa canina',
        familyName: 'Rosaceae',
        genusName: 'Rosa',
    ));

    expect($result->outcome)->toBe(ImportOutcome::SkippedDuplicate)
        ->and(Plant::query()->where('scientific_name', 'Rosa canina')->count())->toBe(1);
});

it('reuses existing family and genus records', function (): void {
    $family = Family::factory()->create(['name' => 'Rosaceae', 'slug' => 'rosaceae']);
    Genus::factory()->create(['name' => 'Rosa', 'slug' => 'rosa', 'family_id' => $family->id]);

    $action = resolve(ImportPlantFromData::class);

    $action(new PlantImportData(
        scientificName: 'Rosa gallica',
        familyName: 'Rosaceae',
        genusName: 'Rosa',
    ));

    expect(Family::query()->count())->toBe(1)
        ->and(Genus::query()->count())->toBe(1);
});
