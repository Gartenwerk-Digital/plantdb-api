<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

it('renders the site home page', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    $response->assertSee('PlantDB', escape: false);
});

it('keeps the /docs redirect intact', function (): void {
    $this->get('/docs')->assertRedirect('/docs/api');
});
