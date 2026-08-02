<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

describe('API keys: authentication', function (): void {
    it('requires authentication to list keys', function (): void {
        $this->getJson('/api/v1/api-keys')->assertStatus(401);
    });

    it('requires authentication to create keys', function (): void {
        $this->postJson('/api/v1/api-keys', [
            'name' => 'x',
            'abilities' => ['read'],
        ])->assertStatus(401);
    });

    it('requires authentication to revoke keys', function (): void {
        $this->deleteJson('/api/v1/api-keys/1')->assertStatus(401);
    });

    it('requires verified email', function (): void {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('bootstrap')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/api-keys')
            ->assertStatus(403);
    });
});

describe('API keys: list', function (): void {
    it('lists only own keys without plaintext', function (): void {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $bootstrap = $userA->createToken('bootstrap')->plainTextToken;
        $userA->createToken('other', ['read']);
        $userB->createToken('foreign', ['read']);

        $response = $this->withHeader('Authorization', 'Bearer '.$bootstrap)
            ->getJson('/api/v1/api-keys');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'abilities', 'last_used_at', 'expires_at', 'created_at'],
                ],
            ]);

        expect($response->json('data'))->toHaveCount(2);

        foreach ($response->json('data') as $key) {
            expect($key)->not->toHaveKey('plain_text_token');
            expect($key)->not->toHaveKey('token');
        }
    });
});

describe('API keys: create', function (): void {
    it('creates a named key and returns the plaintext once', function (): void {
        $user = User::factory()->create();
        $bootstrap = $user->createToken('bootstrap')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$bootstrap)
            ->postJson('/api/v1/api-keys', [
                'name' => 'my-key',
                'abilities' => ['read'],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'abilities', 'plain_text_token'],
                'meta' => ['message'],
            ])
            ->assertJsonPath('data.name', 'my-key')
            ->assertJsonPath('data.abilities', ['read']);

        $plain = $response->json('data.plain_text_token');
        expect($plain)->toBeString()->not->toBeEmpty();

        $listResponse = $this->withHeader('Authorization', 'Bearer '.$bootstrap)
            ->getJson('/api/v1/api-keys');

        foreach ($listResponse->json('data') as $key) {
            expect($key)->not->toHaveKey('plain_text_token');
        }
    });

    it('validates ability values', function (): void {
        $user = User::factory()->create();
        $token = $user->createToken('bootstrap')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/api-keys', [
                'name' => 'bad',
                'abilities' => ['destroy-the-world'],
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    });

    it('requires a name and at least one ability', function (): void {
        $user = User::factory()->create();
        $token = $user->createToken('bootstrap')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/api-keys', [
                'name' => '',
                'abilities' => [],
            ])
            ->assertStatus(422);
    });
});

describe('API keys: revoke', function (): void {
    it('revokes own key', function (): void {
        $user = User::factory()->create();
        $bootstrap = $user->createToken('bootstrap')->plainTextToken;
        $target = $user->createToken('target', ['read'])->accessToken;

        $this->withHeader('Authorization', 'Bearer '.$bootstrap)
            ->deleteJson('/api/v1/api-keys/'.$target->getKey())
            ->assertStatus(204);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $target->getKey(),
        ]);
    });

    it('cannot revoke another users key', function (): void {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $bootstrap = $userA->createToken('bootstrap')->plainTextToken;
        $foreign = $userB->createToken('foreign', ['read'])->accessToken;

        $this->withHeader('Authorization', 'Bearer '.$bootstrap)
            ->deleteJson('/api/v1/api-keys/'.$foreign->getKey())
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $foreign->getKey(),
        ]);
    });
});

describe('API keys: ability enforcement', function (): void {
    it('rejects a read-only token on a write-scoped route', function (): void {
        Route::middleware(['api', 'auth:sanctum', 'ability:write'])
            ->get('/_test/write-only', fn () => response()->json(['ok' => true]))
            ->name('_test.write-only');

        $user = User::factory()->create();
        $readToken = $user->createToken('read-only', ['read'])->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$readToken)
            ->getJson('/_test/write-only')
            ->assertStatus(403);
    });

    it('allows a matching-ability token on a write-scoped route', function (): void {
        Route::middleware(['api', 'auth:sanctum', 'ability:write'])
            ->get('/_test/write-only', fn () => response()->json(['ok' => true]))
            ->name('_test.write-only');

        $user = User::factory()->create();
        $writeToken = $user->createToken('writer', ['write'])->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$writeToken)
            ->getJson('/_test/write-only')
            ->assertStatus(200);
    });
});
