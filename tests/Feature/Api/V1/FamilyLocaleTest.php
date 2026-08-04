<?php

declare(strict_types=1);

use App\Models\Family;
use App\Models\FamilyTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Locale-aware FamilyResource', function (): void {
    beforeEach(function (): void {
        $this->family = Family::factory()->create(['slug' => 'rosaceae', 'name' => 'Rosaceae']);
    });

    it('returns common_name in DE when ?locale=de', function (): void {
        FamilyTranslation::factory()->for($this->family)->create([
            'locale' => 'de',
            'common_name' => 'Rosengewächse',
            'description' => 'Familie der Rosen',
        ]);
        FamilyTranslation::factory()->for($this->family)->create([
            'locale' => 'en',
            'common_name' => 'Rose family',
            'description' => 'The rose family',
        ]);

        $this->getJson('/api/v1/families/rosaceae?locale=de')
            ->assertOk()
            ->assertJsonPath('data.common_name', 'Rosengewächse')
            ->assertJsonPath('data.description', 'Familie der Rosen');
    });

    it('returns common_name in EN when ?locale=en', function (): void {
        FamilyTranslation::factory()->for($this->family)->create(['locale' => 'de', 'common_name' => 'Rosengewächse']);
        FamilyTranslation::factory()->for($this->family)->create(['locale' => 'en', 'common_name' => 'Rose family']);

        $this->getJson('/api/v1/families/rosaceae?locale=en')
            ->assertOk()
            ->assertJsonPath('data.common_name', 'Rose family');
    });

    it('falls back to fallback locale when active locale translation is missing', function (): void {
        FamilyTranslation::factory()->for($this->family)->create(['locale' => 'en', 'common_name' => 'Rose family']);

        $this->getJson('/api/v1/families/rosaceae?locale=de')
            ->assertOk()
            ->assertJsonPath('data.common_name', 'Rose family');
    });

    it('returns null when neither active nor fallback translation exists', function (): void {
        $this->getJson('/api/v1/families/rosaceae?locale=de')
            ->assertOk()
            ->assertJsonPath('data.common_name', null)
            ->assertJsonPath('data.description', null);
    });
});
