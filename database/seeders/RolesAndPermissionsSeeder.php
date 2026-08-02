<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('admin', 'web');
    }
}
