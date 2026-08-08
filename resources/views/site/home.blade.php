@extends('site.layouts.base')

@section('title', 'PlantDB — Die offene Pflanzendatenbank')
@section('description', 'PlantDB ist eine offene, community-gepflegte Pflanzendatenbank mit REST-API für Garten-Apps und Entwickler.')

@section('content')

{{-- Hero --}}
<section class="py-20 text-center">
    <x-site.badge variant="accent">Open Source · Community-gepflegt</x-site.badge>
    <h1 class="mt-5 text-5xl font-bold tracking-tight leading-tight max-w-2xl mx-auto">
        Pflanzenwissen,<br>strukturiert und frei
    </h1>
    <p class="mt-5 text-xl text-muted-foreground max-w-xl mx-auto">
        PlantDB liefert zuverlässige Pflanzendaten als REST-API —
        offen, mehrsprachig und von einer aktiven Community gepflegt.
    </p>
    <div class="mt-8 flex gap-3 justify-center flex-wrap">
        <x-site.button href="/docs/api">API erkunden</x-site.button>
        <x-site.button variant="secondary" href="https://github.com/Gartenwerk-Digital/plantdb-api" target="_blank">
            GitHub
        </x-site.button>
    </div>
</section>

{{-- USP-Block --}}
<section class="py-16 border-t border-border">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div>
            <div class="text-3xl mb-3">🌿</div>
            <h3 class="text-lg font-semibold">Offen & frei</h3>
            <p class="mt-2 text-sm text-muted-foreground">
                MIT-Lizenz. Alle Daten und der Quellcode sind öffentlich zugänglich und frei nutzbar.
            </p>
        </div>
        <div>
            <div class="text-3xl mb-3">🌍</div>
            <h3 class="text-lg font-semibold">Mehrsprachig</h3>
            <p class="mt-2 text-sm text-muted-foreground">
                Deutsch und Englisch out of the box. Weitere Sprachen folgen — Beiträge willkommen.
            </p>
        </div>
        <div>
            <div class="text-3xl mb-3">👥</div>
            <h3 class="text-lg font-semibold">Community-Moderation</h3>
            <p class="mt-2 text-sm text-muted-foreground">
                Einträge werden von der Community eingereicht und von Moderatoren geprüft und freigegeben.
            </p>
        </div>
    </div>
</section>

{{-- Zielgruppen --}}
<section class="py-16 border-t border-border">
    <h2 class="text-3xl font-bold text-center mb-12">Für wen ist PlantDB?</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <x-site.card>
            <x-site.badge>Entwickler</x-site.badge>
            <h3 class="mt-4 text-xl font-semibold">Daten für deine App</h3>
            <p class="mt-3 text-muted-foreground">
                Taxonomie, Wuchsformen, Pflegeanforderungen — alles per JSON-API abrufbar.
                Named API Keys, OpenAPI-Dokumentation, Mehrsprachigkeit per <code class="font-mono text-sm bg-muted px-1 rounded">Accept-Language</code>-Header.
            </p>
            <div class="mt-6">
                <x-site.button href="/developers" variant="secondary">Zur Entwickler-Seite</x-site.button>
            </div>
        </x-site.card>

        <x-site.card>
            <x-site.badge variant="accent">Gartenfreunde</x-site.badge>
            <h3 class="mt-4 text-xl font-semibold">Wissen, das wächst</h3>
            <p class="mt-3 text-muted-foreground">
                Entdecke Pflanzen, trag dein Wissen bei und hilf mit, die Datenbank zu verbessern.
                Jeder Beitrag wird geprüft und bereichert die Community.
            </p>
            <div class="mt-6">
                <x-site.button href="/contribute" variant="secondary">Mitmachen</x-site.button>
            </div>
        </x-site.card>
    </div>
</section>

{{-- Pflanzen-Preview --}}
@if($featuredPlants->isNotEmpty())
<section class="py-16 border-t border-border">
    <h2 class="text-3xl font-bold text-center mb-4">Einblick in die Datenbank</h2>
    <p class="text-center text-muted-foreground mb-10">
        {{ number_format($approvedPlantCount, 0, ',', '.') }}+ Pflanzen und wächst täglich.
    </p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($featuredPlants as $plant)
            @php
                $translation = $plant->translations->first();
                $commonName = $translation?->common_name;
            @endphp
            <x-site.card>
                <x-site.badge>{{ $plant->family?->name ?? 'Pflanze' }}</x-site.badge>
                <h3 class="mt-3 text-lg font-semibold italic">{{ $plant->scientific_name }}</h3>
                @if($commonName)
                    <p class="text-sm text-muted-foreground mt-1">{{ $commonName }}</p>
                @endif
                @if($translation?->description)
                    <p class="mt-3 text-sm text-muted-foreground line-clamp-3">{{ $translation->description }}</p>
                @endif
            </x-site.card>
        @endforeach
    </div>
    <div class="text-center mt-8">
        <x-site.button href="/api/v1/plants" variant="ghost">Alle Pflanzen via API →</x-site.button>
    </div>
</section>
@endif

@endsection
