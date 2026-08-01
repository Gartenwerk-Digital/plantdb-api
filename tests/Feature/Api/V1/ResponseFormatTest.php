<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    Route::middleware('api')->prefix('api/v1')->group(function (): void {
        Route::get('_test/single', fn () => response()->json(['data' => ['id' => 1, 'name' => 'Rose']]));

        Route::get('_test/validation', function (): void {
            throw ValidationException::withMessages([
                'email' => ['The email field is required.'],
            ]);
        });

        Route::get('_test/forbidden', function (): void {
            abort(403);
        });

        Route::get('_test/boom', function (): void {
            throw new RuntimeException('kaboom');
        });
    });
});

describe('Success envelope', function (): void {
    it('returns { data } for a single resource', function (): void {
        $this->getJson('/api/v1/_test/single')
            ->assertOk()
            ->assertJson(['data' => ['id' => 1, 'name' => 'Rose']])
            ->assertJsonMissing(['error']);
    });

    it('sets the X-API-Version header on v1 responses', function (): void {
        $this->getJson('/api/v1/ping')
            ->assertOk()
            ->assertHeader('X-API-Version', 'v1');
    });
});

describe('Error envelope', function (): void {
    it('wraps 404 in { error: { code: not_found } }', function (): void {
        $this->getJson('/api/v1/does-not-exist')
            ->assertStatus(404)
            ->assertJsonStructure(['error' => ['code', 'message']])
            ->assertJsonPath('error.code', 'not_found');
    });

    it('wraps validation errors with code + details', function (): void {
        $this->getJson('/api/v1/_test/validation')
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['code', 'message', 'details' => ['email']]])
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonPath('error.details.email.0', 'The email field is required.');
    });

    it('wraps missing auth in { error: { code: unauthenticated } }', function (): void {
        $this->getJson('/api/v1/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'unauthenticated');
    });

    it('wraps 403 abort in { error: { code: forbidden } }', function (): void {
        $this->getJson('/api/v1/_test/forbidden')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'forbidden');
    });

    it('wraps uncaught exceptions in { error: { code: server_error } }', function (): void {
        $this->getJson('/api/v1/_test/boom')
            ->assertStatus(500)
            ->assertJsonPath('error.code', 'server_error');
    });
});
