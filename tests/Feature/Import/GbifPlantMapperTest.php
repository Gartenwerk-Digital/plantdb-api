<?php

declare(strict_types=1);

use App\Services\Import\Mappers\GbifPlantMapper;

it('maps a full GBIF species response to a PlantImportData DTO', function (): void {
    $mapper = new GbifPlantMapper;

    $data = $mapper([
        'key' => 2705015,
        'canonicalName' => 'Rosa canina',
        'scientificName' => 'Rosa canina L.',
        'family' => 'Rosaceae',
        'genus' => 'Rosa',
    ], [
        ['language' => 'eng', 'vernacularName' => 'Dog rose'],
        ['language' => 'deu', 'vernacularName' => 'Hunds-Rose'],
        ['language' => 'fra', 'vernacularName' => 'Églantier'],
    ]);

    expect($data)->not->toBeNull()
        ->and($data->scientificName)->toBe('Rosa canina')
        ->and($data->familyName)->toBe('Rosaceae')
        ->and($data->genusName)->toBe('Rosa')
        ->and($data->sourceKey)->toBe('2705015')
        ->and($data->sourceUrl)->toBe('https://www.gbif.org/species/2705015')
        ->and($data->commonNames)->toBe(['en' => 'Dog rose', 'de' => 'Hunds-Rose']);
});

it('returns null when family or genus is missing', function (): void {
    $mapper = new GbifPlantMapper;

    expect($mapper(['canonicalName' => 'X y', 'family' => 'Rosaceae']))->toBeNull()
        ->and($mapper(['canonicalName' => 'X y', 'genus' => 'Rosa']))->toBeNull()
        ->and($mapper(['family' => 'Rosaceae', 'genus' => 'Rosa']))->toBeNull();
});

it('falls back to scientificName when canonicalName is absent', function (): void {
    $mapper = new GbifPlantMapper;

    $data = $mapper([
        'scientificName' => 'Rosa canina L.',
        'family' => 'Rosaceae',
        'genus' => 'Rosa',
    ]);

    expect($data)->not->toBeNull()
        ->and($data->scientificName)->toBe('Rosa canina L.');
});
