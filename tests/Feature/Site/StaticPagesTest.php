<?php

declare(strict_types=1);

beforeEach(function (): void {
    $this->withoutVite();
});

it('renders the developers page', function (): void {
    $this->get('/developers')
        ->assertOk()
        ->assertSee('Für Entwickler')
        ->assertSee('Rate Limits');
});

it('renders the contribute page', function (): void {
    $this->get('/contribute')
        ->assertOk()
        ->assertSee('Mitmachen')
        ->assertSee('CC BY-SA 4.0');
});

it('renders the about page', function (): void {
    $this->get('/about')
        ->assertOk()
        ->assertSee('Über PlantDB')
        ->assertSee('Roadmap');
});

it('renders the impressum page', function (): void {
    $this->get('/impressum')
        ->assertOk()
        ->assertSee('Impressum')
        ->assertSee('Angaben gemäß §5 DDG');
});

it('renders the datenschutz page', function (): void {
    $this->get('/datenschutz')
        ->assertOk()
        ->assertSee('Datenschutzerklärung')
        ->assertSee('Server-Logfiles');
});
