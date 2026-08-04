<?php

declare(strict_types=1);

use App\Models\Family;
use App\Models\FamilyTranslation;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores translations for a family', function (): void {
    $family = Family::factory()->create();
    FamilyTranslation::factory()->for($family)->create(['locale' => 'de', 'common_name' => 'Rosengewächse']);
    FamilyTranslation::factory()->for($family)->create(['locale' => 'en', 'common_name' => 'Rose family']);

    expect($family->translations()->count())->toBe(2);
    $this->assertDatabaseHas('family_translations', [
        'family_id' => $family->id,
        'locale' => 'de',
        'common_name' => 'Rosengewächse',
    ]);
});

it('enforces unique locale per family', function (): void {
    $family = Family::factory()->create();
    FamilyTranslation::factory()->for($family)->create(['locale' => 'de']);

    FamilyTranslation::factory()->for($family)->create(['locale' => 'de']);
})->throws(QueryException::class);

it('cascades on family delete', function (): void {
    $family = Family::factory()->create();
    FamilyTranslation::factory()->for($family)->create(['locale' => 'de']);
    FamilyTranslation::factory()->for($family)->create(['locale' => 'en']);

    $family->delete();

    $this->assertDatabaseMissing('family_translations', ['family_id' => $family->id]);
});
