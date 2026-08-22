@php
    $pageTitle = 'Mitmachen';
    $pageDescription = 'PlantDB lebt von der Community: Pflanzen einreichen, moderieren, Code beisteuern. Alle Beiträge sind willkommen.';
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
        <x-site.badge variant="accent">Community</x-site.badge>
        <h1 class="mt-4 text-4xl md:text-5xl font-bold tracking-tight">Mitmachen</h1>
        <p class="mt-4 text-lg text-muted-foreground max-w-2xl mx-auto">
            PlantDB ist ein offenes Projekt. Ob Pflanzenwissen, Übersetzungen oder Code —
            jeder Beitrag hilft, die Datenbank besser zu machen.
        </p>
        <div class="mt-8">
            <x-site.button href="https://github.com/Gartenwerk-Digital/plantdb-api" target="_blank" rel="noopener">
                Auf GitHub beitragen
            </x-site.button>
        </div>
    </header>

    {{-- GitHub-Workflow --}}
    <section>
        <h2 class="text-2xl font-bold mb-6">GitHub-Workflow</h2>
        <ol class="space-y-4 list-decimal list-inside marker:text-primary marker:font-semibold max-w-3xl">
            <li><strong>Fork</strong> das Repository auf GitHub.</li>
            <li><strong>Feature-Branch</strong> anlegen: <code class="font-mono text-sm bg-muted px-1 rounded">feat/&lt;kurz-slug&gt;</code> von <code class="font-mono text-sm bg-muted px-1 rounded">develop</code>.</li>
            <li><strong>Pull Request</strong> gegen <code class="font-mono text-sm bg-muted px-1 rounded">develop</code> öffnen — Beschreibung, Tests, Screenshots wenn relevant.</li>
            <li><strong>Review</strong> abwarten. Wir helfen gerne bei Feedback und ersten Beiträgen.</li>
        </ol>
    </section>

    {{-- Moderation --}}
    <section>
        <h2 class="text-2xl font-bold mb-6">Moderation</h2>
        <p class="text-muted-foreground max-w-3xl">
            Neue Pflanzen-Einträge und Übersetzungen werden im Admin-Panel als <em>Contributions</em> eingereicht.
            Ein Moderations-Team prüft botanische Korrektheit, Quellen und Konsistenz, bevor Einträge öffentlich sichtbar
            und über die API abrufbar werden. So bleibt die Datenqualität hoch — und Beiträger:innen bekommen Feedback.
        </p>
    </section>

    {{-- Lizenzen --}}
    <section>
        <h2 class="text-2xl font-bold mb-6">Lizenzen</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-site.card>
                <x-site.badge>Code</x-site.badge>
                <h3 class="mt-3 text-lg font-semibold">MIT License</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Der Quellcode der API und aller offiziellen Frontend-Komponenten steht unter MIT-Lizenz —
                    frei zur Nutzung, Modifikation und kommerziellen Verwendung.
                </p>
                <div class="mt-4">
                    <x-site.button variant="ghost" href="https://github.com/Gartenwerk-Digital/plantdb-api/blob/main/LICENSE" target="_blank" rel="noopener">
                        LICENSE ansehen →
                    </x-site.button>
                </div>
            </x-site.card>

            <x-site.card>
                <x-site.badge variant="accent">Daten</x-site.badge>
                <h3 class="mt-3 text-lg font-semibold">CC BY-SA 4.0</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Die Pflanzendaten stehen unter Creative Commons BY-SA 4.0. Nutzung mit Namensnennung
                    und unter gleichen Bedingungen weitergeben.
                </p>
                <div class="mt-4">
                    <x-site.button variant="ghost" href="https://github.com/Gartenwerk-Digital/plantdb-api/blob/main/DATA_LICENSE.md" target="_blank" rel="noopener">
                        DATA_LICENSE.md ansehen →
                    </x-site.button>
                </div>
            </x-site.card>
        </div>
    </section>

</article>

@endsection
