<?php

declare(strict_types=1);

use App\Models\Family;
use App\Models\Genus;
use App\Models\GenusTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Locale-aware GenusResource', function (): void {
    beforeEach(function (): void {
        $family = Family::factory()->create();
        $this->genus = Genus::factory()->for($family)->create(['slug' => 'rosa', 'name' => 'Rosa']);
    });

    it('returns common_name in DE when ?locale=de', function (): void {
        GenusTranslation::factory()->for($this->genus)->create([
            'locale' => 'de',
            'common_name' => 'Rosen',
            'description' => 'Die Gattung der Rosen',
        ]);
        GenusTranslation::factory()->for($this->genus)->create([
            'locale' => 'en',
            'common_name' => 'Roses',
            'description' => 'The rose genus',
        ]);

        $this->getJson('/api/v1/genera/rosa?locale=de')
            ->assertOk()
            ->assertJsonPath('data.common_name', 'Rosen')
            ->assertJsonPath('data.description', 'Die Gattung der Rosen');
    });

    it('returns common_name in EN when ?locale=en', function (): void {
        GenusTranslation::factory()->for($this->genus)->create(['locale' => 'de', 'common_name' => 'Rosen']);
        GenusTranslation::factory()->for($this->genus)->create(['locale' => 'en', 'common_name' => 'Roses']);

        $this->getJson('/api/v1/genera/rosa?locale=en')
            ->assertOk()
            ->assertJsonPath('data.common_name', 'Roses');
    });

    it('falls back to fallback locale when active locale translation is missing', function (): void {
        GenusTranslation::factory()->for($this->genus)->create(['locale' => 'en', 'common_name' => 'Roses']);

        $this->getJson('/api/v1/genera/rosa?locale=de')
            ->assertOk()
            ->assertJsonPath('data.common_name', 'Roses');
    });

    it('returns null when neither active nor fallback translation exists', function (): void {
        $this->getJson('/api/v1/genera/rosa?locale=de')
            ->assertOk()
            ->assertJsonPath('data.common_name', null)
            ->assertJsonPath('data.description', null);
    });
});
