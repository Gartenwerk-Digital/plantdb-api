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

        $email = is_string(config('admin.email')) ? config('admin.email') : '';
        $password = is_string(config('admin.password')) ? config('admin.password') : '';

        throw_if($email === '' || $password === '', RuntimeException::class, 'ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env for local seeding.');

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
