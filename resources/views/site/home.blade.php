@extends('site.layouts.base')

@section('title', 'Startseite')

@section('content')
    <div class="text-center py-16">
        <x-site.badge variant="accent">Beta</x-site.badge>
        <h1 class="mt-4 text-4xl font-bold tracking-tight">
            Die offene Pflanzendatenbank
        </h1>
        <p class="mt-4 text-lg text-muted-foreground max-w-xl mx-auto">
            PlantDB liefert strukturierte Pflanzendaten als REST-API — frei, offen und community-gepflegt.
        </p>
        <div class="mt-8 flex gap-3 justify-center">
            <x-site.button href="/docs/api">API-Dokumentation</x-site.button>
            <x-site.button variant="secondary" href="https://github.com/Gartenwerk-Digital/plantdb-api">GitHub</x-site.button>
        </div>
    </div>

    <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-site.card>
            <x-site.badge>REST API</x-site.badge>
            <h3 class="mt-3 text-lg font-semibold">Strukturierte Daten</h3>
            <p class="mt-2 text-sm text-muted-foreground">Taxonomie, Wuchsform, Pflegeanforderungen — alles über eine einheitliche API abrufbar.</p>
        </x-site.card>
        <x-site.card>
            <x-site.badge>i18n</x-site.badge>
            <h3 class="mt-3 text-lg font-semibold">Mehrsprachig</h3>
            <p class="mt-2 text-sm text-muted-foreground">Deutsch und Englisch unterstützt, weitere Sprachen geplant.</p>
        </x-site.card>
        <x-site.card>
            <x-site.badge variant="accent">Open Source</x-site.badge>
            <h3 class="mt-3 text-lg font-semibold">Community-Moderation</h3>
            <p class="mt-2 text-sm text-muted-foreground">Pflanzen-Einträge werden von der Community gepflegt und von Moderatoren freigegeben.</p>
        </x-site.card>
    </div>
@endsection
