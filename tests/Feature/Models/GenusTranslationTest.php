<?php

declare(strict_types=1);

use App\Models\Genus;
use App\Models\GenusTranslation;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores translations for a genus', function (): void {
    $genus = Genus::factory()->create();
    GenusTranslation::factory()->for($genus)->create(['locale' => 'de', 'common_name' => 'Rosen']);
    GenusTranslation::factory()->for($genus)->create(['locale' => 'en', 'common_name' => 'Roses']);

    expect($genus->translations()->count())->toBe(2);
    $this->assertDatabaseHas('genus_translations', [
        'genus_id' => $genus->id,
        'locale' => 'en',
        'common_name' => 'Roses',
    ]);
});

it('enforces unique locale per genus', function (): void {
    $genus = Genus::factory()->create();
    GenusTranslation::factory()->for($genus)->create(['locale' => 'de']);

    GenusTranslation::factory()->for($genus)->create(['locale' => 'de']);
})->throws(QueryException::class);

it('cascades on genus delete', function (): void {
    $genus = Genus::factory()->create();
    GenusTranslation::factory()->for($genus)->create(['locale' => 'de']);
    GenusTranslation::factory()->for($genus)->create(['locale' => 'en']);

    $genus->delete();

    $this->assertDatabaseMissing('genus_translations', ['genus_id' => $genus->id]);
});
