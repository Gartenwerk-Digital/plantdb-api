<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->isLocal()) {
            return;
        }

        $email = (string) env('ADMIN_EMAIL', '');
        $password = (string) env('ADMIN_PASSWORD', '');

        if ($email === '' || $password === '') {
            throw new RuntimeException('ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env for local seeding.');
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Chris Ganzert',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );
    }
}
