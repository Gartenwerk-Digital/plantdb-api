<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Models\Plant;
use App\Models\PlantCareTask;
use App\Models\PlantTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('GET /api/v1/plants/{slug}', function (): void {
    it('returns a plant by slug', function (): void {
        $plant = Plant::factory()->create([
            'slug' => 'solanum-lycopersicum',
            'status' => PlantStatus::Approved->value,
        ]);

        $this->getJson('/api/v1/plants/solanum-lycopersicum')
            ->assertOk()
            ->assertJsonPath('data.id', $plant->id)
            ->assertJsonPath('data.slug', 'solanum-lycopersicum')
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonStructure([
                'data' => [
                    'id', 'slug', 'scientific_name', 'family_id', 'genus_id',
                    'status', 'life_cycle', 'sun_requirement', 'soil_types',
                    'bloom_colors', 'propagation_methods',
                    'created_at', 'updated_at',
                ],
            ]);
    });

    it('does not include relations by default', function (): void {
        Plant::factory()->create(['slug' => 'no-includes', 'status' => PlantStatus::Approved->value]);

        $response = $this->getJson('/api/v1/plants/no-includes')->assertOk();

        expect($response->json('data'))
            ->not->toHaveKey('images')
            ->not->toHaveKey('companions')
            ->not->toHaveKey('care_tasks')
            ->not->toHaveKey('translations');
    });

    it('includes images when requested', function (): void {
        Storage::fake('public');

        $plant = Plant::factory()->create(['slug' => 'with-images', 'status' => PlantStatus::Approved->value]);

        foreach (['portrait', 'flower', 'leaf'] as $collection) {
            $plant->addMedia(UploadedFile::fake()->image($collection.'.jpg', 800, 1200))
                ->withCustomProperties([
                    'license' => 'CC BY 4.0',
                    'attribution' => 'Test Photographer',
                ])
                ->toMediaCollection($collection);
        }

        $this->getJson('/api/v1/plants/with-images?include=images')
            ->assertOk()
            ->assertJsonCount(3, 'data.images')
            ->assertJsonStructure(['data' => ['images' => [[
                'id', 'type', 'urls' => ['original', 'portrait', 'thumb'],
                'license', 'attribution',
            ]]]])
            ->assertJsonPath('data.images.0.license', 'CC BY 4.0')
            ->assertJsonPath('data.images.0.attribution', 'Test Photographer');
    });

    it('includes companions when requested', function (): void {
        $plant = Plant::factory()->create(['slug' => 'with-companions', 'status' => PlantStatus::Approved->value]);
        $companion = Plant::factory()->create(['status' => PlantStatus::Approved->value]);
        $plant->companions()->attach($companion->id, ['relationship' => 'beneficial', 'notes' => 'test']);

        $this->getJson('/api/v1/plants/with-companions?include=companions')
            ->assertOk()
            ->assertJsonCount(1, 'data.companions')
            ->assertJsonPath('data.companions.0.id', $companion->id)
            ->assertJsonPath('data.companions.0.relationship', 'beneficial')
            ->assertJsonPath('data.companions.0.notes', 'test');
    });

    it('includes care_tasks when requested', function (): void {
        $plant = Plant::factory()->create(['slug' => 'with-care', 'status' => PlantStatus::Approved->value]);
        PlantCareTask::factory()->count(2)->create(['plant_id' => $plant->id]);

        $this->getJson('/api/v1/plants/with-care?include=care_tasks')
            ->assertOk()
            ->assertJsonCount(2, 'data.care_tasks')
            ->assertJsonStructure(['data' => ['care_tasks' => [['id', 'task_type', 'month_start', 'month_end']]]]);
    });

    it('includes translations when requested', function (): void {
        $plant = Plant::factory()->create(['slug' => 'with-translations', 'status' => PlantStatus::Approved->value]);
        PlantTranslation::factory()->create(['plant_id' => $plant->id, 'locale' => 'de', 'common_name' => 'Tomate']);
        PlantTranslation::factory()->create(['plant_id' => $plant->id, 'locale' => 'en', 'common_name' => 'Tomato']);

        $this->getJson('/api/v1/plants/with-translations?include=translations')
            ->assertOk()
            ->assertJsonCount(2, 'data.translations');
    });

    it('returns 404 for unknown slug', function (): void {
        $this->getJson('/api/v1/plants/does-not-exist')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    });

    it('returns 404 for draft plant (no data leak via status)', function (): void {
        Plant::factory()->create(['slug' => 'hidden-draft', 'status' => PlantStatus::Draft->value]);

        $this->getJson('/api/v1/plants/hidden-draft')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    });

    it('returns 404 for pending_review plant', function (): void {
        Plant::factory()->create(['slug' => 'hidden-pending', 'status' => PlantStatus::PendingReview->value]);

        $this->getJson('/api/v1/plants/hidden-pending')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    });

    it('returns 404 for rejected plant', function (): void {
        Plant::factory()->create(['slug' => 'hidden-rejected', 'status' => PlantStatus::Rejected->value]);

        $this->getJson('/api/v1/plants/hidden-rejected')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    });
});
