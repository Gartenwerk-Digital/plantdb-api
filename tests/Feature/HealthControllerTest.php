<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns ok when all systems are healthy', function (): void {
    $response = $this->getJson('/health');

    $response
        ->assertOk()
        ->assertJson([
            'status' => 'ok',
            'checks' => [
                'database' => ['status' => 'up'],
                'cache' => ['status' => 'up'],
                'queue' => ['status' => 'up'],
            ],
        ])
        ->assertJsonStructure(['status', 'checks', 'version', 'timestamp']);
});
