<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GET /api/v1/genera', function (): void {
    it('returns a paginated envelope with 20 per page', function (): void {
        Genus::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/genera')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'family_id', 'plants_count', 'created_at', 'updated_at']],
                'meta' => ['pagination' => ['current_page', 'per_page', 'total', 'last_page']],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);

        expect($response->json('meta.pagination.per_page'))->toBe(20)
            ->and($response->json('meta.pagination.total'))->toBe(25)
            ->and($response->json('data'))->toHaveCount(20)
            ->and($response->json('links.next'))->not->toBeNull();
    });

    it('supports filter[name] and sort', function (): void {
        $family = Family::factory()->create();

        Genus::factory()->for($family)->create(['name' => 'Rosa', 'slug' => 'rosa']);
        Genus::factory()->for($family)->create(['name' => 'Rubus', 'slug' => 'rubus']);
        Genus::factory()->for($family)->create(['name' => 'Prunus', 'slug' => 'prunus']);

        $response = $this->getJson('/api/v1/genera?filter[name]=r&sort=-name')
            ->assertOk();

        $names = array_column($response->json('data'), 'name');
        expect($names)->toBe(['Rubus', 'Rosa', 'Prunus']);
    });

    it('returns a single genus by slug', function (): void {
        $family = Family::factory()->create();
        $genus = Genus::factory()->for($family)->create(['name' => 'Rosa', 'slug' => 'rosa']);

        $this->getJson('/api/v1/genera/rosa')
            ->assertOk()
            ->assertJsonPath('data.id', $genus->id)
            ->assertJsonPath('data.slug', 'rosa')
            ->assertJsonPath('data.family_id', $family->id)
            ->assertJsonPath('data.plants_count', 0);
    });

    it('returns 404 error envelope for unknown slug', function (): void {
        $this->getJson('/api/v1/genera/does-not-exist')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    });

    it('plants_count only counts approved plants', function (): void {
        $family = Family::factory()->create();
        $genus = Genus::factory()->for($family)->create(['slug' => 'countable']);

        Plant::factory()->count(2)->create([
            'family_id' => $family->id,
            'genus_id' => $genus->id,
            'status' => PlantStatus::Approved->value,
        ]);
        Plant::factory()->create([
            'family_id' => $family->id,
            'genus_id' => $genus->id,
            'status' => PlantStatus::Draft->value,
        ]);
        Plant::factory()->create([
            'family_id' => $family->id,
            'genus_id' => $genus->id,
            'status' => PlantStatus::PendingReview->value,
        ]);

        $this->getJson('/api/v1/genera/countable')
            ->assertOk()
            ->assertJsonPath('data.plants_count', 2);
    });
});
