<?php

declare(strict_types=1);

use App\Models\Plant;
use App\Models\PlantTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function writeCuratedFixture(array $entries): string
{
    $path = storage_path('framework/testing/curated-'.uniqid().'.json');
    File::ensureDirectoryExists(dirname($path));
    File::put($path, json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    return $path;
}

it('imports curated plants via GBIF species/match with translations', function (): void {
    Http::fake([
        'api.gbif.org/v1/species/match*name=Solanum+lycopersicum*' => Http::response([
            'usageKey' => 2930137,
            'canonicalName' => 'Solanum lycopersicum',
            'family' => 'Solanaceae',
            'genus' => 'Solanum',
            'matchType' => 'EXACT',
        ]),
        'api.gbif.org/v1/species/match*name=Ocimum+basilicum*' => Http::response([
            'usageKey' => 2927096,
            'canonicalName' => 'Ocimum basilicum',
            'family' => 'Lamiaceae',
            'genus' => 'Ocimum',
            'matchType' => 'EXACT',
        ]),
    ]);

    $path = writeCuratedFixture([
        ['scientific_name' => 'Solanum lycopersicum', 'de' => 'Tomate', 'en' => 'Tomato', 'category' => 'vegetable'],
        ['scientific_name' => 'Ocimum basilicum', 'de' => 'Basilikum', 'en' => 'Basil', 'category' => 'herb'],
    ]);

    try {
        $this->artisan('import:curated', ['--file' => $path])->assertExitCode(0);

        expect(Plant::query()->count())->toBe(2);

        $tomato = Plant::query()->where('scientific_name', 'Solanum lycopersicum')->firstOrFail();
        expect($tomato->import_source)->toBe('gbif')
            ->and($tomato->source_key)->toBe('2930137')
            ->and($tomato->family->name)->toBe('Solanaceae')
            ->and($tomato->genus->name)->toBe('Solanum')
            ->and(PlantTranslation::query()->where('plant_id', $tomato->id)->where('locale', 'de')->value('common_name'))->toBe('Tomate')
            ->and(PlantTranslation::query()->where('plant_id', $tomato->id)->where('locale', 'en')->value('common_name'))->toBe('Tomato');
    } finally {
        File::delete($path);
    }
});

it('reports no_match when GBIF returns matchType NONE', function (): void {
    Http::fake([
        'api.gbif.org/v1/species/match*' => Http::response([
            'matchType' => 'NONE',
        ]),
    ]);

    $path = writeCuratedFixture([
        ['scientific_name' => 'Nonexistent plant', 'de' => 'x', 'en' => 'x', 'category' => 'x'],
    ]);

    try {
        $this->artisan('import:curated', ['--file' => $path])->assertExitCode(0);

        expect(Plant::query()->count())->toBe(0);
    } finally {
        File::delete($path);
    }
});
