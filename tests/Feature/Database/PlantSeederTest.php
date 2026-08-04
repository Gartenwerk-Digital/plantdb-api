<?php

declare(strict_types=1);

use App\Models\Family;
use App\Models\Genus;
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
