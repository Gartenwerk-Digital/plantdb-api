@php
    $pageTitle = 'Über PlantDB';
    $pageDescription = 'Vision, Roadmap und Team hinter PlantDB — der offenen, community-gepflegten Pflanzendatenbank.';
@endphp

@extends('site.layouts.base')

@section('title', $pageTitle)
@section('description', $pageDescription)

@push('head')
    <x-site.seo :title="$pageTitle" :description="$pageDescription" />
@endpush

@section('content')

<article class="space-y-16">

    {{-- Hero --}}
    <header class="text-center py-8">
        <x-site.badge>Über uns</x-site.badge>
        <h1 class="mt-4 text-4xl md:text-5xl font-bold tracking-tight">Über PlantDB</h1>
        <p class="mt-4 text-lg text-muted-foreground max-w-2xl mx-auto">
            Eine offene Pflanzendatenbank für Garten-Apps, Forschung und alle, die mit Pflanzen arbeiten.
        </p>
    </header>

    {{-- Vision --}}
    <section class="max-w-3xl">
        <h2 class="text-2xl font-bold mb-4">Vision</h2>
        <div class="space-y-4 text-foreground/90 leading-relaxed">
            <p>
                Pflanzenwissen ist verstreut — in Fachbüchern, Nischenforen und geschlossenen Datenbanken.
                Wir bauen eine <strong>strukturierte, mehrsprachige API</strong>, die dieses Wissen frei zugänglich macht:
                für Entwickler:innen von Garten-Apps, für Bildungsprojekte und für alle, die Pflanzen besser verstehen wollen.
            </p>
            <p>
                Das Projekt ist <strong>community-getragen</strong>: Beiträge werden geprüft, Daten stehen unter offener Lizenz,
                und der Code ist auf GitHub für alle einsehbar und veränderbar.
            </p>
        </div>
    </section>

    {{-- Roadmap --}}
    <section>
        <h2 class="text-2xl font-bold mb-6">Roadmap</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-site.card>
                <x-site.badge variant="accent">Jetzt</x-site.badge>
                <h3 class="mt-3 text-lg font-semibold">MVP-Launch</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Öffentliche API mit Pflanzen, Familien, Gattungen, Übersetzungen (de/en), Contribution-Flow und Admin-Panel.
                </p>
            </x-site.card>
            <x-site.card>
                <x-site.badge variant="muted">Als nächstes</x-site.badge>
                <h3 class="mt-3 text-lg font-semibold">Wachstum</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Mehr Pflanzen, mehr Sprachen, Import aus offenen Datenquellen, ausgebautes Community-Tooling.
                </p>
            </x-site.card>
            <x-site.card>
                <x-site.badge variant="muted">Später</x-site.badge>
                <h3 class="mt-3 text-lg font-semibold">v1.0 — Stabile API</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Vollständige Abdeckung der wichtigsten Kultur- und Zierpflanzen, versionierte API-Verträge, offizielle SDKs.
                </p>
            </x-site.card>
            <x-site.card>
                <x-site.badge variant="muted">Vision</x-site.badge>
                <h3 class="mt-3 text-lg font-semibold">Multi-Sprach-Ausbau</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Vollständige Lokalisierung in allen europäischen Hauptsprachen, regionale Anbau-Kalender.
                </p>
            </x-site.card>
        </div>
    </section>

    {{-- Team & Danksagungen --}}
    <section>
        <h2 class="text-2xl font-bold mb-6">Team &amp; Danksagungen</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-site.card>
                <h3 class="text-lg font-semibold">Team</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Initiiert und gepflegt von <a href="https://github.com/Gartenwerk-Digital" class="text-primary hover:underline" target="_blank" rel="noopener">Gartenwerk Digital</a>
                    mit Unterstützung der wachsenden Community von Beiträger:innen.
                </p>
            </x-site.card>
            <x-site.card>
                <h3 class="text-lg font-semibold">Danke an</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Diese großartigen Projekte, auf denen PlantDB aufbaut:
                    <strong>Laravel</strong>, <strong>Filament</strong>, <strong>Scramble</strong>,
                    <strong>spatie/laravel-medialibrary</strong>, <strong>spatie/laravel-permission</strong>,
                    <strong>laravel-api-kit</strong>, <strong>Pest</strong>, <strong>Larastan</strong>.
                </p>
            </x-site.card>
        </div>
    </section>

</article>

@endsection
