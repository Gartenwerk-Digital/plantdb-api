<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use Database\Seeders\PlantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates all core tables', function (): void {
    $tables = [
        'families',
        'genera',
        'plants',
        'plant_translations',
        'media',
        'plant_companions',
        'plant_care_tasks',
        'plant_pests_diseases',
        'contributions',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue(sprintf('Table %s should exist', $table));
    }
});

it('seeds the canonical curated plants with their taxonomy', function (): void {
    $this->seed(PlantSeeder::class);

    expect(Plant::query()->count())->toBeGreaterThanOrEqual(3)
        ->and(Family::query()->count())->toBeGreaterThanOrEqual(3)
        ->and(Genus::query()->count())->toBeGreaterThanOrEqual(3);

    $rose = Plant::query()->where('slug', 'rosa-centifolia')->firstOrFail();
    expect($rose->status)->toBe(PlantStatus::Approved)
        ->and($rose->family->name)->toBe('Rosaceae')
        ->and($rose->genus->name)->toBe('Rosa')
        ->and($rose->bloom_colors)->toContain('rosa');
});
