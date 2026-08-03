<?php

declare(strict_types=1);

use App\Actions\Contributions\ApproveContribution;
use App\Actions\Contributions\RejectContribution;
use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Enums\PlantImageLicense;
use App\Models\Contribution;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function imageAuthHeader(User $user): array
{
    $token = $user->createToken('bootstrap')->plainTextToken;

    return ['Authorization' => 'Bearer '.$token];
}

beforeEach(function (): void {
    Storage::fake('public');
    Mail::fake();

    $this->user = User::factory()->create();
    $this->plant = Plant::factory()->create();
});

describe('POST /api/v1/contributions: image validation', function (): void {
    it('rejects image without plant_id', function (): void {
        $this->withHeaders(imageAuthHeader($this->user))
            ->post('/api/v1/contributions', [
                'type' => 'image',
                'image' => UploadedFile::fake()->image('a.jpg', 800, 1200),
                'payload' => [
                    'collection' => 'portrait',
                    'license' => PlantImageLicense::Cc0->value,
                ],
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    });

    it('rejects image without file', function (): void {
        $this->withHeaders(imageAuthHeader($this->user))
            ->post('/api/v1/contributions', [
                'type' => 'image',
                'plant_id' => $this->plant->id,
                'payload' => [
                    'collection' => 'portrait',
                    'license' => PlantImageLicense::Cc0->value,
                ],
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    });

    it('rejects invalid collection', function (): void {
        $this->withHeaders(imageAuthHeader($this->user))
            ->post('/api/v1/contributions', [
                'type' => 'image',
                'plant_id' => $this->plant->id,
                'image' => UploadedFile::fake()->image('a.jpg', 800, 1200),
                'payload' => [
                    'collection' => 'not-a-collection',
                    'license' => PlantImageLicense::Cc0->value,
                ],
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    });

    it('rejects missing attribution when license requires it', function (): void {
        $this->withHeaders(imageAuthHeader($this->user))
            ->post('/api/v1/contributions', [
                'type' => 'image',
                'plant_id' => $this->plant->id,
                'image' => UploadedFile::fake()->image('a.jpg', 800, 1200),
                'payload' => [
                    'collection' => 'portrait',
                    'license' => PlantImageLicense::CcBy->value,
                ],
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    });

    it('rejects non-uuid plant_id without hitting the database', function (): void {
        $this->withHeaders(imageAuthHeader($this->user))
            ->post('/api/v1/contributions', [
                'type' => 'image',
                'plant_id' => 'not-a-uuid',
                'image' => UploadedFile::fake()->image('a.jpg', 800, 1200),
                'payload' => [
                    'collection' => 'portrait',
                    'license' => PlantImageLicense::Cc0->value,
                ],
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    });

    it('rejects invalid license', function (): void {
        $this->withHeaders(imageAuthHeader($this->user))
            ->post('/api/v1/contributions', [
                'type' => 'image',
                'plant_id' => $this->plant->id,
                'image' => UploadedFile::fake()->image('a.jpg', 800, 1200),
                'payload' => [
                    'collection' => 'portrait',
                    'license' => 'MIT',
                ],
            ], ['Accept' => 'application/json'])
            ->assertStatus(422);
    });
});

describe('POST /api/v1/contributions: image success', function (): void {
    it('accepts a valid image upload and attaches media to the contribution only', function (): void {
        $response = $this->withHeaders(imageAuthHeader($this->user))
            ->post('/api/v1/contributions', [
                'type' => 'image',
                'plant_id' => $this->plant->id,
                'image' => UploadedFile::fake()->image('rose.jpg', 800, 1200),
                'payload' => [
                    'collection' => 'portrait',
                    'license' => PlantImageLicense::CcBy->value,
                    'attribution' => 'Jane Photographer',
                ],
            ], ['Accept' => 'application/json']);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'image')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.plant_id', $this->plant->id)
            ->assertJsonPath('data.payload.collection', 'portrait')
            ->assertJsonPath('data.payload.license', PlantImageLicense::CcBy->value);

        $contribution = Contribution::query()->firstOrFail();

        expect($contribution->type)->toBe(ContributionType::Image)
            ->and($contribution->getMedia(Contribution::MEDIA_PENDING_IMAGE))->toHaveCount(1)
            ->and($this->plant->fresh()->getMedia('portrait'))->toHaveCount(0);
    });
});

describe('Approve / Reject image contributions', function (): void {
    it('moves the media onto the plant with custom_properties on approve', function (): void {
        $reviewer = User::factory()->create();

        $this->withHeaders(imageAuthHeader($this->user))
            ->post('/api/v1/contributions', [
                'type' => 'image',
                'plant_id' => $this->plant->id,
                'image' => UploadedFile::fake()->image('rose.jpg', 800, 1200),
                'payload' => [
                    'collection' => 'flower',
                    'license' => PlantImageLicense::CcBy->value,
                    'attribution' => 'Jane Photographer',
                ],
            ], ['Accept' => 'application/json'])
            ->assertStatus(201);

        $contribution = Contribution::query()->firstOrFail();

        resolve(ApproveContribution::class)($contribution, $reviewer);

        $contribution->refresh();
        $plant = $this->plant->fresh();

        expect($contribution->status)->toBe(ContributionStatus::Approved)
            ->and($contribution->getMedia(Contribution::MEDIA_PENDING_IMAGE))->toHaveCount(0)
            ->and($plant->getMedia('flower'))->toHaveCount(1);

        $media = $plant->getMedia('flower')->first();

        expect($media->getCustomProperty('license'))->toBe(PlantImageLicense::CcBy->value)
            ->and($media->getCustomProperty('attribution'))->toBe('Jane Photographer')
            ->and($media->getCustomProperty('submitted_by'))->toBe($this->user->id);
    });

    it('clears pending image on reject', function (): void {
        $reviewer = User::factory()->create();

        $this->withHeaders(imageAuthHeader($this->user))
            ->post('/api/v1/contributions', [
                'type' => 'image',
                'plant_id' => $this->plant->id,
                'image' => UploadedFile::fake()->image('rose.jpg', 800, 1200),
                'payload' => [
                    'collection' => 'portrait',
                    'license' => PlantImageLicense::Cc0->value,
                ],
            ], ['Accept' => 'application/json'])
            ->assertStatus(201);

        $contribution = Contribution::query()->firstOrFail();

        resolve(RejectContribution::class)($contribution, $reviewer, 'not usable');

        $contribution->refresh();

        expect($contribution->status)->toBe(ContributionStatus::Rejected)
            ->and($contribution->getMedia(Contribution::MEDIA_PENDING_IMAGE))->toHaveCount(0);
    });
});

describe('GET /api/v1/contributions', function (): void {
    it('lists the authenticated user own contributions', function (): void {
        $other = User::factory()->create();

        Contribution::factory()->count(2)->create(['submitted_by' => $this->user->id]);
        Contribution::factory()->create(['submitted_by' => $other->id]);

        $response = $this->withHeaders(imageAuthHeader($this->user))
            ->getJson('/api/v1/contributions');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    });
});
