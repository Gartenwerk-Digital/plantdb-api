<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Models\Plant;
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

it('renders the placeholder for an approved plant', function (): void {
    Plant::factory()->create(['slug' => 'tomato', 'status' => PlantStatus::Approved->value]);

    $this->get('/plants/tomato')
        ->assertOk()
        ->assertSee('Coming soon');
});
