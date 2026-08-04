<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Models\Plant;
use App\Models\PlantTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('Locale-aware PlantResource', function (): void {
    beforeEach(function (): void {
        $this->plant = Plant::factory()->create([
            'slug' => 'tomato',
            'status' => PlantStatus::Approved->value,
        ]);
    });

    it('returns common_name in DE when ?locale=de', function (): void {
        PlantTranslation::factory()->create([
            'plant_id' => $this->plant->id,
            'locale' => 'de',
            'common_name' => 'Tomate',
            'description' => 'Rote Beere',
        ]);
        PlantTranslation::factory()->create([
            'plant_id' => $this->plant->id,
            'locale' => 'en',
            'common_name' => 'Tomato',
            'description' => 'Red berry',
        ]);

        $this->getJson('/api/v1/plants/tomato?locale=de')
            ->assertOk()
            ->assertJsonPath('data.common_name', 'Tomate')
            ->assertJsonPath('data.description', 'Rote Beere');
    });

    it('returns common_name in EN when ?locale=en', function (): void {
        PlantTranslation::factory()->create([
            'plant_id' => $this->plant->id,
            'locale' => 'de',
            'common_name' => 'Tomate',
        ]);
        PlantTranslation::factory()->create([
            'plant_id' => $this->plant->id,
            'locale' => 'en',
            'common_name' => 'Tomato',
        ]);

        $this->getJson('/api/v1/plants/tomato?locale=en')
            ->assertOk()
            ->assertJsonPath('data.common_name', 'Tomato');
    });

    it('falls back to fallback locale when active locale translation is missing', function (): void {
        PlantTranslation::factory()->create([
            'plant_id' => $this->plant->id,
            'locale' => 'en',
            'common_name' => 'Tomato',
        ]);

        $this->getJson('/api/v1/plants/tomato?locale=de')
            ->assertOk()
            ->assertJsonPath('data.common_name', 'Tomato');
    });

    it('returns null when neither active nor fallback translation exists', function (): void {
        $this->getJson('/api/v1/plants/tomato?locale=de')
            ->assertOk()
            ->assertJsonPath('data.common_name', null)
            ->assertJsonPath('data.description', null);
    });

    it('does not trigger N+1 queries on list endpoint', function (): void {
        Plant::factory()->count(5)->create(['status' => PlantStatus::Approved->value])
            ->each(function (Plant $plant): void {
                PlantTranslation::factory()->create(['plant_id' => $plant->id, 'locale' => 'de']);
                PlantTranslation::factory()->create(['plant_id' => $plant->id, 'locale' => 'en']);
            });

        DB::connection()->enableQueryLog();
        $this->getJson('/api/v1/plants?locale=de')->assertOk();
        $queryCount = count(DB::connection()->getQueryLog());
        DB::connection()->disableQueryLog();

        expect($queryCount)->toBeLessThan(10);
    });

    it('keeps ?include=translations returning all locales', function (): void {
        PlantTranslation::factory()->create(['plant_id' => $this->plant->id, 'locale' => 'de']);
        PlantTranslation::factory()->create(['plant_id' => $this->plant->id, 'locale' => 'en']);

        $this->getJson('/api/v1/plants/tomato?include=translations&locale=de')
            ->assertOk()
            ->assertJsonCount(2, 'data.translations');
    });
});
