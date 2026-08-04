<?php

declare(strict_types=1);

use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes non-whitelisted plants and orphaned taxonomy', function (): void {
    $family = Family::factory()->create(['slug' => 'archaeoglobaceae', 'name' => 'Archaeoglobaceae']);
    $genus = Genus::factory()->create(['slug' => 'archaeoglobus', 'name' => 'Archaeoglobus', 'family_id' => $family->id]);
    Plant::factory()->count(3)->create(['family_id' => $family->id, 'genus_id' => $genus->id]);

    $keepFamily = Family::factory()->create(['slug' => 'rosaceae', 'name' => 'Rosaceae']);
    $keepGenus = Genus::factory()->create(['slug' => 'rosa', 'name' => 'Rosa', 'family_id' => $keepFamily->id]);
    Plant::factory()->create([
        'slug' => 'rosa-centifolia',
        'scientific_name' => 'Rosa centifolia',
        'family_id' => $keepFamily->id,
        'genus_id' => $keepGenus->id,
    ]);

    $this->artisan('plants:cleanup')->assertExitCode(0);

    expect(Plant::query()->count())->toBe(1)
        ->and(Plant::query()->where('slug', 'rosa-centifolia')->exists())->toBeTrue()
        ->and(Family::query()->where('slug', 'archaeoglobaceae')->exists())->toBeFalse()
        ->and(Genus::query()->where('slug', 'archaeoglobus')->exists())->toBeFalse()
        ->and(Family::query()->where('slug', 'rosaceae')->exists())->toBeTrue();
});

it('dry-run reports counts without deleting', function (): void {
    $family = Family::factory()->create();
    $genus = Genus::factory()->create(['family_id' => $family->id]);
    Plant::factory()->count(5)->create(['family_id' => $family->id, 'genus_id' => $genus->id]);

    $this->artisan('plants:cleanup', ['--dry-run' => true])->assertExitCode(0);

    expect(Plant::query()->count())->toBe(5);
});
