<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Models\Family;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GET /api/v1/families', function (): void {
    it('returns a paginated envelope with 20 per page', function (): void {
        Family::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/families')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'description', 'plants_count', 'created_at', 'updated_at']],
                'meta' => ['pagination' => ['current_page', 'per_page', 'total', 'last_page']],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);

        expect($response->json('meta.pagination.per_page'))->toBe(20)
            ->and($response->json('meta.pagination.total'))->toBe(25)
            ->and($response->json('data'))->toHaveCount(20)
            ->and($response->json('links.next'))->not->toBeNull();
    });

    it('supports filter[name] and sort', function (): void {
        Family::factory()->create(['name' => 'Rosaceae', 'slug' => 'rosaceae']);
        Family::factory()->create(['name' => 'Poaceae', 'slug' => 'poaceae']);
        Family::factory()->create(['name' => 'Fabaceae', 'slug' => 'fabaceae']);

        $response = $this->getJson('/api/v1/families?filter[name]=aceae&sort=-name')
            ->assertOk();

        $names = array_column($response->json('data'), 'name');
        expect($names)->toBe(['Rosaceae', 'Poaceae', 'Fabaceae']);
    });

    it('returns a single family by slug', function (): void {
        $family = Family::factory()->create(['name' => 'Rosaceae', 'slug' => 'rosaceae']);

        $this->getJson('/api/v1/families/rosaceae')
            ->assertOk()
            ->assertJsonPath('data.id', $family->id)
            ->assertJsonPath('data.slug', 'rosaceae')
            ->assertJsonPath('data.plants_count', 0);
    });

    it('returns 404 error envelope for unknown slug', function (): void {
        $this->getJson('/api/v1/families/does-not-exist')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    });

    it('plants_count only counts approved plants', function (): void {
        $family = Family::factory()->create(['slug' => 'countable']);

        Plant::factory()->count(2)->create(['family_id' => $family->id, 'status' => PlantStatus::Approved->value]);
        Plant::factory()->create(['family_id' => $family->id, 'status' => PlantStatus::Draft->value]);
        Plant::factory()->create(['family_id' => $family->id, 'status' => PlantStatus::Rejected->value]);

        $this->getJson('/api/v1/families/countable')
            ->assertOk()
            ->assertJsonPath('data.plants_count', 2);
    });
});
