<?php

declare(strict_types=1);

use App\Enums\LifeCycle;
use App\Enums\PlantStatus;
use App\Enums\SoilMoisture;
use App\Enums\SunRequirement;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GET /api/v1/plants', function (): void {
    it('returns a paginated envelope with only approved plants (default 20)', function (): void {
        Plant::factory()->count(22)->create(['status' => PlantStatus::Approved->value]);
        Plant::factory()->count(3)->create(['status' => PlantStatus::Draft->value]);
        Plant::factory()->create(['status' => PlantStatus::PendingReview->value]);
        Plant::factory()->create(['status' => PlantStatus::Rejected->value]);

        $response = $this->getJson('/api/v1/plants')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'slug', 'scientific_name', 'status']],
                'meta' => ['pagination' => ['current_page', 'per_page', 'total', 'last_page']],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);

        expect($response->json('meta.pagination.per_page'))->toBe(20)
            ->and($response->json('meta.pagination.total'))->toBe(22)
            ->and($response->json('data'))->toHaveCount(20);

        foreach ($response->json('data') as $row) {
            expect($row['status'])->toBe('approved');
        }
    });

    it('never exposes draft, pending_review or rejected plants across pages', function (): void {
        Plant::factory()->count(2)->create(['status' => PlantStatus::Approved->value]);
        Plant::factory()->create(['status' => PlantStatus::Draft->value, 'slug' => 'hidden-draft']);
        Plant::factory()->create(['status' => PlantStatus::PendingReview->value, 'slug' => 'hidden-pending']);
        Plant::factory()->create(['status' => PlantStatus::Rejected->value, 'slug' => 'hidden-rejected']);

        $response = $this->getJson('/api/v1/plants?per_page=50')->assertOk();

        $slugs = array_column($response->json('data'), 'slug');
        expect($slugs)
            ->not->toContain('hidden-draft')
            ->not->toContain('hidden-pending')
            ->not->toContain('hidden-rejected')
            ->and($response->json('meta.pagination.total'))->toBe(2);
    });

    it('filters by life_cycle', function (): void {
        Plant::factory()->count(2)->create(['status' => PlantStatus::Approved->value, 'life_cycle' => LifeCycle::Annual->value]);
        Plant::factory()->count(3)->create(['status' => PlantStatus::Approved->value, 'life_cycle' => LifeCycle::Perennial->value]);

        $response = $this->getJson('/api/v1/plants?filter[life_cycle]=perennial')->assertOk();

        expect($response->json('meta.pagination.total'))->toBe(3);
        foreach ($response->json('data') as $row) {
            expect($row['life_cycle'])->toBe('perennial');
        }
    });

    it('filters by sun_requirement', function (): void {
        Plant::factory()->count(2)->create(['status' => PlantStatus::Approved->value, 'sun_requirement' => SunRequirement::FullSun->value]);
        Plant::factory()->create(['status' => PlantStatus::Approved->value, 'sun_requirement' => SunRequirement::FullShade->value]);

        $response = $this->getJson('/api/v1/plants?filter[sun_requirement]=full_sun')->assertOk();

        expect($response->json('meta.pagination.total'))->toBe(2);
    });

    it('filters by soil_moisture', function (): void {
        Plant::factory()->create(['status' => PlantStatus::Approved->value, 'soil_moisture' => SoilMoisture::Dry->value]);
        Plant::factory()->count(2)->create(['status' => PlantStatus::Approved->value, 'soil_moisture' => SoilMoisture::Moist->value]);

        $response = $this->getJson('/api/v1/plants?filter[soil_moisture]=moist')->assertOk();

        expect($response->json('meta.pagination.total'))->toBe(2);
    });

    it('filters by bloom_month (start<=month<=end)', function (): void {
        Plant::factory()->create([
            'status' => PlantStatus::Approved->value,
            'bloom_start_month' => 5,
            'bloom_end_month' => 8,
            'slug' => 'blooms-in-june',
        ]);
        Plant::factory()->create([
            'status' => PlantStatus::Approved->value,
            'bloom_start_month' => 7,
            'bloom_end_month' => 9,
            'slug' => 'blooms-later',
        ]);

        $response = $this->getJson('/api/v1/plants?filter[bloom_month]=6')->assertOk();

        $slugs = array_column($response->json('data'), 'slug');
        expect($slugs)
            ->toContain('blooms-in-june')
            ->not->toContain('blooms-later');
    });

    it('filters by edible=true (excludes null and empty edible_parts)', function (): void {
        Plant::factory()->create(['status' => PlantStatus::Approved->value, 'edible_parts' => ['fruit'], 'slug' => 'edible-a']);
        Plant::factory()->create(['status' => PlantStatus::Approved->value, 'edible_parts' => ['leaf', 'root'], 'slug' => 'edible-b']);
        Plant::factory()->create(['status' => PlantStatus::Approved->value, 'edible_parts' => null, 'slug' => 'no-edible']);
        Plant::factory()->create(['status' => PlantStatus::Approved->value, 'edible_parts' => [], 'slug' => 'empty-edible']);

        $response = $this->getJson('/api/v1/plants?filter[edible]=true')->assertOk();

        $slugs = array_column($response->json('data'), 'slug');
        expect($slugs)
            ->toContain('edible-a')
            ->toContain('edible-b')
            ->not->toContain('no-edible')
            ->not->toContain('empty-edible');
    });

    it('filters by toxic_to_pets', function (): void {
        Plant::factory()->count(2)->create(['status' => PlantStatus::Approved->value, 'toxic_to_pets' => true]);
        Plant::factory()->count(3)->create(['status' => PlantStatus::Approved->value, 'toxic_to_pets' => false]);

        $response = $this->getJson('/api/v1/plants?filter[toxic_to_pets]=1')->assertOk();

        expect($response->json('meta.pagination.total'))->toBe(2);
        foreach ($response->json('data') as $row) {
            expect($row['toxic_to_pets'])->toBeTrue();
        }
    });

    it('filter[q] matches scientific_name via search_vector', function (): void {
        Plant::factory()->create([
            'status' => PlantStatus::Approved->value,
            'scientific_name' => 'Rosa canina',
            'slug' => 'rosa-canina',
        ]);
        Plant::factory()->create([
            'status' => PlantStatus::Approved->value,
            'scientific_name' => 'Quercus robur',
            'slug' => 'quercus-robur',
        ]);

        $response = $this->getJson('/api/v1/plants?filter[q]=canina')->assertOk();

        $slugs = array_column($response->json('data'), 'slug');
        expect($slugs)
            ->toContain('rosa-canina')
            ->not->toContain('quercus-robur');
    });

    it('caps per_page at 50', function (): void {
        $family = Family::factory()->create();
        $genus = Genus::factory()->create(['family_id' => $family->id]);

        Plant::factory()
            ->count(52)
            ->sequence(fn ($sequence): array => [
                'scientific_name' => 'Species number '.$sequence->index,
                'slug' => 'species-'.$sequence->index,
            ])
            ->create([
                'status' => PlantStatus::Approved->value,
                'family_id' => $family->id,
                'genus_id' => $genus->id,
            ]);

        $response = $this->getJson('/api/v1/plants?per_page=200')->assertOk();

        expect($response->json('meta.pagination.per_page'))->toBe(50)
            ->and($response->json('data'))->toHaveCount(50);
    });

    it('sorts by scientific_name ascending and descending', function (): void {
        Plant::factory()->create(['status' => PlantStatus::Approved->value, 'scientific_name' => 'Zephyra elegans']);
        Plant::factory()->create(['status' => PlantStatus::Approved->value, 'scientific_name' => 'Acer palmatum']);
        Plant::factory()->create(['status' => PlantStatus::Approved->value, 'scientific_name' => 'Malus domestica']);

        $asc = $this->getJson('/api/v1/plants?sort=scientific_name')->assertOk();
        $desc = $this->getJson('/api/v1/plants?sort=-scientific_name')->assertOk();

        expect(array_column($asc->json('data'), 'scientific_name'))
            ->toBe(['Acer palmatum', 'Malus domestica', 'Zephyra elegans'])
            ->and(array_column($desc->json('data'), 'scientific_name'))
            ->toBe(['Zephyra elegans', 'Malus domestica', 'Acer palmatum']);
    });
});
