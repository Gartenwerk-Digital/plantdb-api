<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Models\Plant;
use App\Models\PlantTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::forget('api:v1:plants:sitemap');
    RateLimiter::clear('ip:127.0.0.1');
});

describe('GET /api/v1/plants/sitemap', function (): void {
    it('returns slug, updated_at and available locales for every approved plant', function (): void {
        $tomato = Plant::factory()->create(['slug' => 'tomato', 'status' => PlantStatus::Approved->value]);
        PlantTranslation::factory()->create(['plant_id' => $tomato->id, 'locale' => 'de']);
        PlantTranslation::factory()->create(['plant_id' => $tomato->id, 'locale' => 'en']);

        $rose = Plant::factory()->create(['slug' => 'rose', 'status' => PlantStatus::Approved->value]);
        PlantTranslation::factory()->create(['plant_id' => $rose->id, 'locale' => 'en']);

        $response = $this->getJson('/api/v1/plants/sitemap')
            ->assertOk()
            ->assertJsonStructure(['data' => [['slug', 'updated_at', 'locales']]]);

        $bySlug = collect($response->json('data'))->keyBy('slug');

        expect($response->json('data'))->toHaveCount(2)
            ->and($bySlug['tomato']['locales'])->toEqualCanonicalizing(['de', 'en'])
            ->and($bySlug['rose']['locales'])->toEqual(['en'])
            ->and($bySlug['tomato']['updated_at'])->not->toBeNull();
    });

    it('excludes non-approved plants', function (): void {
        Plant::factory()->create(['slug' => 'approved-one', 'status' => PlantStatus::Approved->value]);
        Plant::factory()->create(['slug' => 'hidden-draft', 'status' => PlantStatus::Draft->value]);
        Plant::factory()->create(['slug' => 'hidden-pending', 'status' => PlantStatus::PendingReview->value]);
        Plant::factory()->create(['slug' => 'hidden-rejected', 'status' => PlantStatus::Rejected->value]);

        $slugs = array_column($this->getJson('/api/v1/plants/sitemap')->assertOk()->json('data'), 'slug');

        expect($slugs)
            ->toContain('approved-one')
            ->not->toContain('hidden-draft')
            ->not->toContain('hidden-pending')
            ->not->toContain('hidden-rejected');
    });

    it('returns an empty array when no plants are approved', function (): void {
        $this->getJson('/api/v1/plants/sitemap')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    });

    it('is not throttled — many consecutive calls all succeed', function (): void {
        Plant::factory()->create(['status' => PlantStatus::Approved->value]);

        for ($i = 0; $i < 150; $i++) {
            $this->getJson('/api/v1/plants/sitemap')->assertOk();
        }
    });

    it('does not collide with plants/{slug}', function (): void {
        Plant::factory()->create(['slug' => 'sitemap', 'status' => PlantStatus::Approved->value]);

        // Literal /plants/sitemap must resolve to the sitemap endpoint, not the show endpoint.
        $this->getJson('/api/v1/plants/sitemap')
            ->assertOk()
            ->assertJsonStructure(['data' => [['slug', 'updated_at', 'locales']]])
            ->assertJsonMissingPath('data.scientific_name');
    });
});
