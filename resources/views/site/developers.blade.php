@php
    $pageTitle = 'Für Entwickler';
    $pageDescription = 'PlantDB REST-API für Entwickler: Getting Started, Authentifizierung, Rate Limits und Code-Beispiele.';
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
        <x-site.badge>REST API · JSON</x-site.badge>
        <h1 class="mt-4 text-4xl md:text-5xl font-bold tracking-tight">Für Entwickler</h1>
        <p class="mt-4 text-lg text-muted-foreground max-w-2xl mx-auto">
            Baue deine App auf strukturierten, offenen Pflanzendaten. Sanctum-Bearer-Token,
            OpenAPI-Dokumentation, Mehrsprachigkeit per <code class="font-mono text-sm bg-muted px-1 rounded">Accept-Language</code>.
        </p>
        <div class="mt-8">
            <x-site.button href="/docs/api">Zur API-Dokumentation</x-site.button>
        </div>
    </header>

    {{-- Getting Started --}}
    <section>
        <h2 class="text-2xl font-bold mb-6">Getting Started</h2>
        <ol class="space-y-4 list-decimal list-inside marker:text-primary marker:font-semibold">
            <li>
                <strong>Account anlegen</strong> und im Admin-Panel einen <em>Named API Key</em> erstellen.
            </li>
            <li>
                <strong>Endpoint aufrufen</strong> — z. B. <code class="font-mono text-sm bg-muted px-1 rounded">GET /api/v1/plants</code> — mit
                <code class="font-mono text-sm bg-muted px-1 rounded">Authorization: Bearer &lt;token&gt;</code>.
            </li>
            <li>
                <strong>Response verarbeiten</strong> — alle Endpunkte liefern JSON, folgen konsistenter Fehler-Struktur und respektieren
                <code class="font-mono text-sm bg-muted px-1 rounded">Accept-Language</code>.
            </li>
        </ol>
    </section>

    {{-- Auth --}}
    <section>
        <h2 class="text-2xl font-bold mb-6">Authentifizierung</h2>
        <p class="text-muted-foreground max-w-3xl">
            PlantDB nutzt <strong>Laravel Sanctum</strong> mit Personal Access Tokens. Jeder API-Key ist einem Nutzer-Account zugeordnet,
            kann benannt und einzeln widerrufen werden. Sende den Token im <code class="font-mono text-sm bg-muted px-1 rounded">Authorization</code>-Header
            als <code class="font-mono text-sm bg-muted px-1 rounded">Bearer</code>-Token.
        </p>
    </section>

    {{-- Rate Limits --}}
    <section>
        <h2 class="text-2xl font-bold mb-6">Rate Limits</h2>
        <div class="overflow-x-auto rounded-[var(--radius-lg)] border border-border">
            <table class="w-full text-left text-sm">
                <thead class="bg-muted text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Tier</th>
                        <th class="px-4 py-3 font-medium">Limit</th>
                        <th class="px-4 py-3 font-medium">Zuordnung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border bg-card">
                    <tr><td class="px-4 py-3">Anonym</td><td class="px-4 py-3">100 / Tag</td><td class="px-4 py-3">IP-basiert</td></tr>
                    <tr><td class="px-4 py-3">Free</td><td class="px-4 py-3">1.000 / Tag</td><td class="px-4 py-3">API-Token</td></tr>
                    <tr><td class="px-4 py-3">Pro</td><td class="px-4 py-3">50.000 / Tag</td><td class="px-4 py-3">API-Token</td></tr>
                    <tr><td class="px-4 py-3">Enterprise</td><td class="px-4 py-3">unlimitiert</td><td class="px-4 py-3">auf Anfrage</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- Beispiele --}}
    <section>
        <h2 class="text-2xl font-bold mb-6">Beispiel-Snippets</h2>
        <div class="space-y-6">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-2">curl</h3>
                <pre class="bg-muted text-foreground rounded-[var(--radius)] p-4 overflow-x-auto text-sm"><code class="font-mono">curl -H "Authorization: Bearer $PLANTDB_TOKEN" \
     -H "Accept-Language: de" \
     https://plantdb-api.test/api/v1/plants</code></pre>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-2">PHP</h3>
                <pre class="bg-muted text-foreground rounded-[var(--radius)] p-4 overflow-x-auto text-sm"><code class="font-mono">use Illuminate\Support\Facades\Http;

$plants = Http::withToken($token)
    ->withHeaders(['Accept-Language' => 'de'])
    ->get('https://plantdb-api.test/api/v1/plants')
    -&gt;json('data');</code></pre>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-2">JavaScript</h3>
                <pre class="bg-muted text-foreground rounded-[var(--radius)] p-4 overflow-x-auto text-sm"><code class="font-mono">const res = await fetch('https://plantdb-api.test/api/v1/plants', {
  headers: {
    Authorization: `Bearer ${token}`,
    'Accept-Language': 'de',
  },
});
const { data } = await res.json();</code></pre>
            </div>
        </div>
    </section>

</article>

@endsection
