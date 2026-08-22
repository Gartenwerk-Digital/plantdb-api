@php
    $pageTitle = 'Impressum';
    $pageDescription = 'Anbieterkennzeichnung gemäß §5 DDG für PlantDB.';
@endphp

@extends('site.layouts.base')

@section('title', $pageTitle)
@section('description', $pageDescription)

@push('head')
    <x-site.seo :title="$pageTitle" :description="$pageDescription" />
@endpush

@section('content')

<article class="space-y-12 max-w-3xl">

    {{-- Hero --}}
    <header>
        <x-site.badge>Rechtliches</x-site.badge>
        <h1 class="mt-4 text-4xl md:text-5xl font-bold tracking-tight">Impressum</h1>
        <p class="mt-2 text-sm text-muted-foreground">Stand: {{ date('d.m.Y') }}</p>
    </header>

    {{-- §5 DDG --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">Angaben gemäß §5 DDG</h2>
        <div class="space-y-2 text-foreground/90 leading-relaxed">
            <p><strong>[TODO Betreiber: Vor- und Nachname / Firma]</strong></p>
            <p>[TODO Straße und Hausnummer]</p>
            <p>[TODO PLZ und Ort]</p>
            <p>[TODO Land]</p>
        </div>
    </section>

    {{-- Kontakt --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">Kontakt</h2>
        <div class="space-y-2 text-foreground/90 leading-relaxed">
            <p>E-Mail: <a href="mailto:[TODO E-Mail-Adresse]" class="text-primary hover:underline">[TODO E-Mail-Adresse]</a></p>
        </div>
    </section>

    {{-- Verantwortlich für den Inhalt --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">Verantwortlich für den Inhalt nach §18 Abs. 2 MStV</h2>
        <div class="space-y-2 text-foreground/90 leading-relaxed">
            <p><strong>[TODO Vor- und Nachname]</strong></p>
            <p>[TODO Anschrift wie oben]</p>
        </div>
    </section>

    {{-- Haftung für Inhalte --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">Haftung für Inhalte</h2>
        <p class="text-foreground/90 leading-relaxed">
            Die Inhalte dieser Seite wurden mit größtmöglicher Sorgfalt erstellt. Für die Richtigkeit,
            Vollständigkeit und Aktualität der Inhalte kann jedoch keine Gewähr übernommen werden.
            Als Diensteanbieter sind wir gemäß §7 Abs. 1 DDG für eigene Inhalte auf dieser Seite nach
            den allgemeinen Gesetzen verantwortlich. Nach §§8 bis 10 DDG sind wir als Diensteanbieter
            jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen
            oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.
        </p>
    </section>

    {{-- Haftung für Links --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">Haftung für Links</h2>
        <p class="text-foreground/90 leading-relaxed">
            Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen
            Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen.
            Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der
            Seiten verantwortlich. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links
            umgehend entfernen.
        </p>
    </section>

    {{-- Urheberrecht --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">Urheberrecht &amp; Lizenzen</h2>
        <div class="space-y-4 text-foreground/90 leading-relaxed">
            <p>
                Der Quellcode von PlantDB steht unter der <strong>MIT-Lizenz</strong> und ist auf
                <a href="https://github.com/Gartenwerk-Digital/plantdb-api" class="text-primary hover:underline" target="_blank" rel="noopener">GitHub</a>
                einsehbar.
            </p>
            <p>
                Die von der Community beigetragenen Pflanzendaten stehen unter der Lizenz
                <strong>Creative Commons Namensnennung – Weitergabe unter gleichen Bedingungen (CC BY-SA 4.0)</strong>.
                Bei Weiterverwendung ist eine Attribution auf „PlantDB Community“ mit Verlinkung
                auf diese Seite anzugeben.
            </p>
        </div>
    </section>

</article>

@endsection
