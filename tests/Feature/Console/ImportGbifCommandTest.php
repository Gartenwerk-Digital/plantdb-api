<?php

declare(strict_types=1);

use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('imports plants from the GBIF API and reports counts', function (): void {
    Http::fake([
        'api.gbif.org/v1/species/search*' => Http::response([
            'offset' => 0,
            'limit' => 100,
            'endOfRecords' => true,
            'count' => 3,
            'results' => [
                [
                    'key' => 1,
                    'canonicalName' => 'Alpha beta',
                    'family' => 'Fabaceae',
                    'genus' => 'Alpha',
                ],
                [
                    'key' => 2,
                    'canonicalName' => 'Gamma delta',
                    'family' => 'Fabaceae',
                    'genus' => 'Gamma',
                ],
                [
                    'key' => 3,
                    'scientificName' => 'Incomplete species',
                    // missing family + genus → skipped_incomplete
                ],
            ],
        ]),
    ]);

    $this->artisan('import:gbif', ['--limit' => 100, '--chunk' => 100])
        ->assertExitCode(0);

    expect(Plant::query()->count())->toBe(2)
        ->and(Plant::query()->where('status', 'pending_review')->count())->toBe(2);
});

it('skips duplicate scientific_name from GBIF', function (): void {
    Http::fake([
        'api.gbif.org/v1/species/search*' => Http::response([
            'endOfRecords' => true,
            'results' => [
                ['key' => 1, 'canonicalName' => 'Rosa canina', 'family' => 'Rosaceae', 'genus' => 'Rosa'],
                ['key' => 1, 'canonicalName' => 'Rosa canina', 'family' => 'Rosaceae', 'genus' => 'Rosa'],
            ],
        ]),
    ]);

    $this->artisan('import:gbif', ['--limit' => 10, '--chunk' => 10])
        ->assertExitCode(0);

    expect(Plant::query()->where('scientific_name', 'Rosa canina')->count())->toBe(1);
});
