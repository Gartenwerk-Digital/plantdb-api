<?php

declare(strict_types=1);

it('responds to ping with status ok', function (): void {
    $this->getJson('/api/v1/ping')
        ->assertOk()
        ->assertExactJson(['status' => 'ok']);
});

it('is publicly accessible without authentication', function (): void {
    $this->getJson('/api/v1/ping')->assertOk();
});
