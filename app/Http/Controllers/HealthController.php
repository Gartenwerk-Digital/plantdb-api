<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];

        $healthy = collect($checks)->every(fn (array $c): bool => $c['status'] === 'up');

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
            'version' => config('app.version', 'dev'),
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    /** @return array{status: string, error?: string} */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'up'];
        } catch (Throwable $throwable) {
            return ['status' => 'down', 'error' => $throwable->getMessage()];
        }
    }

    /** @return array{status: string, error?: string} */
    private function checkCache(): array
    {
        try {
            $key = 'health:probe:'.uniqid();
            Cache::put($key, '1', 5);
            $ok = Cache::get($key) === '1';
            Cache::forget($key);

            return $ok ? ['status' => 'up'] : ['status' => 'down', 'error' => 'read-after-write mismatch'];
        } catch (Throwable $throwable) {
            return ['status' => 'down', 'error' => $throwable->getMessage()];
        }
    }

    /** @return array{status: string, error?: string, failed_jobs?: int} */
    private function checkQueue(): array
    {
        try {
            $failed = DB::table('failed_jobs')->count();
            /** @var int $threshold */
            $threshold = config('app.failed_jobs_threshold', 50);

            return $failed >= $threshold
                ? ['status' => 'down', 'error' => sprintf('failed jobs: %d >= %d', $failed, $threshold)]
                : ['status' => 'up', 'failed_jobs' => $failed];
        } catch (Throwable $throwable) {
            return ['status' => 'down', 'error' => $throwable->getMessage()];
        }
    }
}
