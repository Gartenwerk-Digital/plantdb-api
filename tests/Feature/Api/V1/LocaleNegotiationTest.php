<?php

declare(strict_types=1);

it('uses the default locale when Accept-Language header is empty', function (): void {
    $response = $this->call('GET', '/api/v1/ping', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_ACCEPT_LANGUAGE' => '',
    ]);

    $response->assertOk();

    expect($response->headers->get('Content-Language'))->toBe('de');
});

it('prefers the query parameter over Accept-Language', function (): void {
    $response = $this->getJson('/api/v1/ping?locale=en', ['Accept-Language' => 'de']);

    $response->assertOk();

    expect($response->headers->get('Content-Language'))->toBe('en');
});

it('accepts a valid Accept-Language header', function (): void {
    $response = $this->getJson('/api/v1/ping', ['Accept-Language' => 'en-US,en;q=0.9']);

    $response->assertOk();

    expect($response->headers->get('Content-Language'))->toBe('en');
});

it('falls back to default when Accept-Language has no supported match', function (): void {
    $response = $this->getJson('/api/v1/ping', ['Accept-Language' => 'fr,it;q=0.5']);

    $response->assertOk();

    expect($response->headers->get('Content-Language'))->toBe('de');
});

it('returns 400 with error envelope when explicit ?locale= is unsupported', function (): void {
    $response = $this->getJson('/api/v1/ping?locale=fr');

    $response->assertStatus(400)
        ->assertJsonPath('error.code', 'unsupported_locale')
        ->assertJsonPath('error.details.supported_locales', ['de', 'en']);
});
