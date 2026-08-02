<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            $user = $request->user();
            $token = $user instanceof User ? $user->currentAccessToken() : null;

            if (! $user instanceof User || ! $token instanceof PersonalAccessToken) {
                return Limit::perDay(100)->by('ip:'.$request->ip());
            }

            $tier = $user->tier;
            if ($tier->isUnlimited()) {
                return Limit::none();
            }

            return Limit::perDay($tier->dailyLimit())->by('token:'.$token->id);
        });

        // Auth endpoints - restrictive brute-force protection.
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
