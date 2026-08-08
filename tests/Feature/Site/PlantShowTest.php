<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Models\Plant;
use App\Models\PlantTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

it('returns 404 for unknown plant slugs', function (): void {
    $this->get('/plants/does-not-exist')->assertNotFound();
});

it('returns 404 for plants that are not approved', function (): void {
    Plant::factory()->create(['slug' => 'draft-plant', 'status' => PlantStatus::Draft->value]);

    $this->get('/plants/draft-plant')->assertNotFound();
});

it('renders the detail page for an approved plant', function (): void {
    $plant = Plant::factory()->create([
        'slug' => 'tomato',
        'status' => PlantStatus::Approved->value,
        'scientific_name' => 'Solanum lycopersicum',
    ]);

    PlantTranslation::factory()->create([
        'plant_id' => $plant->id,
        'locale' => 'de',
        'common_name' => 'Tomate',
        'description' => 'Die Tomate ist eine beliebte Gemüsepflanze.',
    ]);

    $this->get('/plants/tomato')
        ->assertOk()
        ->assertSee('Solanum lycopersicum')
        ->assertSee('Tomate')
        ->assertSee('Die Tomate ist eine beliebte Gemüsepflanze.')
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type": "Thing"', false);
});

it('falls back to scientific name when no translation exists', function (): void {
    Plant::factory()->create([
        'slug' => 'nameless',
        'status' => PlantStatus::Approved->value,
        'scientific_name' => 'Foo bar',
    ]);

    $this->get('/plants/nameless')
        ->assertOk()
        ->assertSee('Foo bar');
});
