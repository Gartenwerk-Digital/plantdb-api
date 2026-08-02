<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    Role::findOrCreate('admin', 'web');
});

it('redirects guests from the admin panel to the login page', function (): void {
    $this->get('/admin')
        ->assertRedirect('/admin/login');
});

it('denies access to authenticated users without the admin role', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('allows access to users with the admin role and shows the brand name', function (): void {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('PlantDB Admin', false);
});
