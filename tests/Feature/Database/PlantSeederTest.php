<?php

declare(strict_types=1);

use App\Models\Family;
use App\Models\FamilyTranslation;
use App\Models\Genus;
use App\Models\GenusTranslation;
use App\Models\Plant;
use Database\Seeders\PlantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds from JSON data and is idempotent on a second run', function (): void {
    $this->seed(PlantSeeder::class);
    $firstCount = Plant::query()->count();

    expect($firstCount)->toBeGreaterThan(0)
        ->and(Family::query()->count())->toBeGreaterThan(0)
        ->and(Genus::query()->count())->toBeGreaterThan(0);

    $this->seed(PlantSeeder::class);

    expect(Plant::query()->count())->toBe($firstCount);
});

it('seeds DE and EN translations for every family and genus', function (): void {
    $this->seed(PlantSeeder::class);

    $familyCount = Family::query()->count();
    $genusCount = Genus::query()->count();

    expect(FamilyTranslation::query()->where('locale', 'de')->count())->toBe($familyCount)
        ->and(FamilyTranslation::query()->where('locale', 'en')->count())->toBe($familyCount)
        ->and(GenusTranslation::query()->where('locale', 'de')->count())->toBe($genusCount)
        ->and(GenusTranslation::query()->where('locale', 'en')->count())->toBe($genusCount);
});

it('is idempotent for family and genus translations', function (): void {
    $this->seed(PlantSeeder::class);
    $famCount = FamilyTranslation::query()->count();
    $genCount = GenusTranslation::query()->count();

    $this->seed(PlantSeeder::class);

    expect(FamilyTranslation::query()->count())->toBe($famCount)
        ->and(GenusTranslation::query()->count())->toBe($genCount);
});
