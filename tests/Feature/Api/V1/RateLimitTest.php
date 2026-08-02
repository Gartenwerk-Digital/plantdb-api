<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
});

describe('Tier-based rate limiting', function (): void {
    it('returns X-RateLimit-Limit and Remaining headers for a free user', function (): void {
        $user = User::factory()->create();
        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me');

        $response->assertOk()
            ->assertHeader('X-RateLimit-Limit', '1000')
            ->assertHeader('X-RateLimit-Remaining', '999');
    });

    it('gives pro users a higher daily limit', function (): void {
        $user = User::factory()->pro()->create();
        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me');

        $response->assertOk()->assertHeader('X-RateLimit-Limit', '50000');
    });

    it('does not throttle enterprise users', function (): void {
        $user = User::factory()->enterprise()->create();
        $token = $user->createToken('t')->plainTextToken;

        RateLimiter::hit(md5('apitoken:'.$user->tokens()->first()->id), 86_400);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me');

        $response->assertOk();
    });

    it('returns 429 with correct error shape once the free daily limit is exhausted', function (): void {
        $user = User::factory()->create();
        $newToken = $user->createToken('t');
        $plain = $newToken->plainTextToken;
        $tokenId = $newToken->accessToken->getKey();

        for ($i = 0; $i < 1_000; $i++) {
            RateLimiter::hit(md5('apitoken:'.$tokenId), 86_400);
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$plain)
            ->getJson('/api/v1/me');

        $response->assertStatus(429)
            ->assertJsonPath('error.code', 'too_many_requests')
            ->assertHeader('Retry-After')
            ->assertHeader('X-RateLimit-Limit', '1000')
            ->assertHeader('X-RateLimit-Remaining', '0');
    });

    it('counts per token, not per user', function (): void {
        $user = User::factory()->create();
        $tokenA = $user->createToken('a');
        $tokenB = $user->createToken('b');

        for ($i = 0; $i < 1_000; $i++) {
            RateLimiter::hit(md5('apitoken:'.$tokenA->accessToken->getKey()), 86_400);
        }

        $this->withHeader('Authorization', 'Bearer '.$tokenA->plainTextToken)
            ->getJson('/api/v1/me')
            ->assertStatus(429);

        Auth::forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$tokenB->plainTextToken)
            ->getJson('/api/v1/me')
            ->assertOk();
    });

    it('limits unauthenticated public reads per IP', function (): void {
        for ($i = 0; $i < 100; $i++) {
            RateLimiter::hit(md5('apiip:127.0.0.1'), 86_400);
        }

        $response = $this->getJson('/api/v1/families');

        $response->assertStatus(429)
            ->assertJsonPath('error.code', 'too_many_requests')
            ->assertHeader('Retry-After');
    });

    it('sets per-IP headers for unauthenticated requests', function (): void {
        $response = $this->getJson('/api/v1/families');

        $response->assertOk()
            ->assertHeader('X-RateLimit-Limit', '100')
            ->assertHeader('X-RateLimit-Remaining', '99');
    });
});
