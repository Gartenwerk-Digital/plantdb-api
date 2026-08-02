<?php

declare(strict_types=1);

use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Mail\ContributionReceived;
use App\Mail\NewContributionSubmitted;
use App\Models\Contribution;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function authHeader(User $user): array
{
    $token = $user->createToken('bootstrap')->plainTextToken;

    return ['Authorization' => 'Bearer '.$token];
}

describe('POST /api/v1/contributions: auth', function (): void {
    it('requires authentication', function (): void {
        $this->postJson('/api/v1/contributions', [
            'type' => 'new_plant',
            'payload' => ['scientific_name' => 'Solanum lycopersicum'],
        ])->assertStatus(401);
    });

    it('requires a verified email', function (): void {
        $user = User::factory()->unverified()->create();

        $this->withHeaders(authHeader($user))
            ->postJson('/api/v1/contributions', [
                'type' => 'new_plant',
                'payload' => ['scientific_name' => 'Solanum lycopersicum'],
            ])
            ->assertStatus(403);
    });
});

describe('POST /api/v1/contributions: validation', function (): void {
    beforeEach(function (): void {
        Mail::fake();
        $this->user = User::factory()->create();
    });

    it('rejects a missing type', function (): void {
        $this->withHeaders(authHeader($this->user))
            ->postJson('/api/v1/contributions', ['payload' => ['x' => 1]])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    });

    it('rejects an unknown type', function (): void {
        $this->withHeaders(authHeader($this->user))
            ->postJson('/api/v1/contributions', [
                'type' => 'nonsense',
                'payload' => ['x' => 1],
            ])
            ->assertStatus(422);
    });

    it('rejects image type as unsupported', function (): void {
        $this->withHeaders(authHeader($this->user))
            ->postJson('/api/v1/contributions', [
                'type' => 'image',
                'plant_id' => Plant::factory()->create()->id,
                'payload' => ['url' => 'https://example.com/x.jpg'],
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    });

    it('rejects new_plant with a plant_id', function (): void {
        $this->withHeaders(authHeader($this->user))
            ->postJson('/api/v1/contributions', [
                'type' => 'new_plant',
                'plant_id' => Plant::factory()->create()->id,
                'payload' => ['scientific_name' => 'Foo bar'],
            ])
            ->assertStatus(422);
    });

    it('rejects new_plant without a scientific_name in payload', function (): void {
        $this->withHeaders(authHeader($this->user))
            ->postJson('/api/v1/contributions', [
                'type' => 'new_plant',
                'payload' => ['common_name' => 'Foo'],
            ])
            ->assertStatus(422);
    });

    it('rejects update without a plant_id', function (): void {
        $this->withHeaders(authHeader($this->user))
            ->postJson('/api/v1/contributions', [
                'type' => 'update',
                'payload' => ['description' => 'better'],
            ])
            ->assertStatus(422);
    });

    it('rejects update pointing to a non-existent plant', function (): void {
        $this->withHeaders(authHeader($this->user))
            ->postJson('/api/v1/contributions', [
                'type' => 'update',
                'plant_id' => '00000000-0000-0000-0000-000000000000',
                'payload' => ['description' => 'better'],
            ])
            ->assertStatus(422);
    });

    it('rejects an empty payload', function (): void {
        $this->withHeaders(authHeader($this->user))
            ->postJson('/api/v1/contributions', [
                'type' => 'new_plant',
                'payload' => [],
            ])
            ->assertStatus(422);
    });
});

describe('POST /api/v1/contributions: success', function (): void {
    beforeEach(function (): void {
        Mail::fake();
    });

    it('creates a new_plant contribution and dispatches both mails', function (): void {
        $user = User::factory()->create();

        $response = $this->withHeaders(authHeader($user))
            ->postJson('/api/v1/contributions', [
                'type' => 'new_plant',
                'payload' => [
                    'scientific_name' => 'Solanum lycopersicum',
                    'common_name' => 'Tomato',
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'type', 'status', 'plant_id', 'payload', 'submitted_by', 'submitted_at'],
                'meta' => ['message'],
            ])
            ->assertJsonPath('data.type', 'new_plant')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.plant_id', null)
            ->assertJsonPath('data.submitted_by', $user->id)
            ->assertJsonPath('data.payload.scientific_name', 'Solanum lycopersicum');

        $contribution = Contribution::query()->firstOrFail();
        expect($contribution->type)->toBe(ContributionType::NewPlant)
            ->and($contribution->status)->toBe(ContributionStatus::Pending)
            ->and($contribution->submitted_by)->toBe($user->id)
            ->and($contribution->plant_id)->toBeNull();

        Mail::assertQueued(NewContributionSubmitted::class);
        Mail::assertQueued(ContributionReceived::class, fn (ContributionReceived $mail): bool => $mail->hasTo($user->email));
    });

    it('creates an update contribution linked to a plant', function (): void {
        $user = User::factory()->create();
        $plant = Plant::factory()->create();

        $response = $this->withHeaders(authHeader($user))
            ->postJson('/api/v1/contributions', [
                'type' => 'update',
                'plant_id' => $plant->id,
                'payload' => ['description' => 'Improved description'],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'update')
            ->assertJsonPath('data.plant_id', $plant->id);

        $this->assertDatabaseHas('contributions', [
            'plant_id' => $plant->id,
            'type' => 'update',
            'status' => 'pending',
            'submitted_by' => $user->id,
        ]);
    });

    it('creates a correction contribution', function (): void {
        $user = User::factory()->create();
        $plant = Plant::factory()->create();

        $this->withHeaders(authHeader($user))
            ->postJson('/api/v1/contributions', [
                'type' => 'correction',
                'plant_id' => $plant->id,
                'payload' => ['scientific_name' => 'Solanum lycopersicum'],
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.type', 'correction');
    });
});
