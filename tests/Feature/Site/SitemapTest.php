<?php

declare(strict_types=1);

use App\Enums\PlantStatus;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

it('serves a valid XML sitemap with static pages and approved plants', function (): void {
    Plant::factory()->create([
        'slug' => 'basil',
        'status' => PlantStatus::Approved->value,
    ]);
    Plant::factory()->create([
        'slug' => 'draft-only',
        'status' => PlantStatus::Draft->value,
    ]);

    $response = $this->get('/sitemap.xml');

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'text/xml; charset=UTF-8')
        ->assertSee('<urlset', false)
        ->assertSee('/impressum', false)
        ->assertSee('/datenschutz', false)
        ->assertSee('/plants/basil', false)
        ->assertDontSee('/plants/draft-only', false);
});
