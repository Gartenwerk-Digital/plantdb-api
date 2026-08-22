@extends('site.layouts.base')

@section('title', 'Datenschutzerklärung')
@section('description', 'Datenschutzerklärung gemäß Art. 13 DSGVO für PlantDB — Umgang mit personenbezogenen Daten, Server-Logs, Rechte der Betroffenen.')

@section('content')

<article class="space-y-12 max-w-3xl">

    {{-- Hero --}}
    <header>
        <x-site.badge>Rechtliches</x-site.badge>
        <h1 class="mt-4 text-4xl md:text-5xl font-bold tracking-tight">Datenschutzerklärung</h1>
        <p class="mt-2 text-sm text-muted-foreground">Stand: {{ date('d.m.Y') }}</p>
    </header>

    {{-- Verantwortlicher --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">1. Verantwortlicher</h2>
        <p class="text-foreground/90 leading-relaxed">
            Verantwortlich für die Verarbeitung personenbezogener Daten auf dieser Website im Sinne
            von Art. 4 Nr. 7 DSGVO ist der im
            <a href="{{ route('site.impressum') }}" class="text-primary hover:underline">Impressum</a>
            genannte Betreiber.
        </p>
    </section>

    {{-- Hosting & Server-Logs --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">2. Hosting und Server-Logfiles</h2>
        <div class="space-y-4 text-foreground/90 leading-relaxed">
            <p>
                Diese Website wird gehostet bei <strong>[TODO Hoster-Name, Anschrift, Land]</strong>.
                Mit dem Hoster besteht ein Auftragsverarbeitungsvertrag gemäß Art. 28 DSGVO.
            </p>
            <p>
                Bei jedem Aufruf der Website erhebt der Hoster automatisch technische Zugriffsdaten
                (Server-Logfiles), die Ihr Browser übermittelt:
            </p>
            <ul class="list-disc pl-6 space-y-1">
                <li>IP-Adresse (gekürzt oder gehasht, sofern technisch möglich)</li>
                <li>Datum und Uhrzeit der Anfrage</li>
                <li>Angeforderte URL und HTTP-Statuscode</li>
                <li>Übertragene Datenmenge</li>
                <li>Referrer-URL</li>
                <li>User-Agent (Browser und Betriebssystem)</li>
            </ul>
            <p>
                Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO. Unser berechtigtes Interesse besteht
                im sicheren, stabilen und funktionalen Betrieb der Website sowie in der Abwehr von
                Missbrauch. Logs werden spätestens nach 30 Tagen gelöscht, sofern nicht zur Aufklärung
                konkreter Sicherheitsvorfälle länger benötigt.
            </p>
        </div>
    </section>

    {{-- Cookies & Tracking --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">3. Cookies, Tracking und Analytics</h2>
        <p class="text-foreground/90 leading-relaxed">
            Diese Website setzt <strong>keine Cookies</strong>, keine Tracking-Pixel und keine
            Analytics-Dienste ein. Es findet keine Profilbildung und keine Reichweitenmessung statt.
        </p>
    </section>

    {{-- Externe Ressourcen --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">4. Externe Ressourcen und Verlinkungen</h2>
        <div class="space-y-4 text-foreground/90 leading-relaxed">
            <p>
                Schriftarten (Fonts) werden selbst gehostet. Es werden keine Google Fonts, keine CDNs
                und keine sonstigen externen Ressourcen nachgeladen. Beim Aufruf einer Seite werden
                daher keine Daten an Dritte übertragen.
            </p>
            <p>
                Die Website enthält Links zu externen Diensten, insbesondere zu
                <a href="https://github.com/Gartenwerk-Digital/plantdb-api" class="text-primary hover:underline" target="_blank" rel="noopener">GitHub</a>.
                Erst mit dem Anklicken eines solchen Links werden Daten (u. a. IP-Adresse, Referrer,
                User-Agent) an den jeweiligen Anbieter übertragen. Für die dortige Verarbeitung ist
                der jeweilige Anbieter verantwortlich.
            </p>
        </div>
    </section>

    {{-- API-Nutzung --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">5. Nutzung der API (<code>/api/v1/*</code>)</h2>
        <div class="space-y-4 text-foreground/90 leading-relaxed">
            <p>
                Für die Nutzung der API ist eine Registrierung erforderlich. Dabei werden
                E-Mail-Adresse und Passwort (nur als Hash) gespeichert, um API-Tokens (Laravel
                Sanctum) auszustellen und Anfragen zu authentifizieren.
            </p>
            <p>
                Zu jeder API-Anfrage werden IP-Adresse, Zeitstempel und Endpoint zeitweise
                verarbeitet, um Rate-Limits durchzusetzen (Art. 6 Abs. 1 lit. f DSGVO —
                Missbrauchsschutz). Diese Daten werden nach 24 Stunden verworfen.
            </p>
            <p>
                Die Löschung des Accounts inklusive aller ausgestellten Tokens ist jederzeit über
                das Admin-Panel oder per E-Mail an den Betreiber möglich.
            </p>
        </div>
    </section>

    {{-- Rechte der Betroffenen --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">6. Ihre Rechte als Betroffene:r</h2>
        <div class="space-y-4 text-foreground/90 leading-relaxed">
            <p>Sie haben nach der DSGVO folgende Rechte:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li>Auskunft über die zu Ihrer Person verarbeiteten Daten (Art. 15 DSGVO)</li>
                <li>Berichtigung unrichtiger Daten (Art. 16 DSGVO)</li>
                <li>Löschung Ihrer Daten („Recht auf Vergessenwerden“, Art. 17 DSGVO)</li>
                <li>Einschränkung der Verarbeitung (Art. 18 DSGVO)</li>
                <li>Datenübertragbarkeit (Art. 20 DSGVO)</li>
                <li>Widerspruch gegen die Verarbeitung (Art. 21 DSGVO)</li>
                <li>Widerruf erteilter Einwilligungen mit Wirkung für die Zukunft (Art. 7 Abs. 3 DSGVO)</li>
            </ul>
            <p>
                Zur Ausübung Ihrer Rechte genügt eine formlose E-Mail an den im
                <a href="{{ route('site.impressum') }}" class="text-primary hover:underline">Impressum</a>
                genannten Kontakt.
            </p>
            <p>
                Es steht Ihnen zudem ein Beschwerderecht bei einer Datenschutz-Aufsichtsbehörde zu
                (Art. 77 DSGVO). Zuständig ist die Aufsichtsbehörde des Bundeslandes, in dem der
                Betreiber seinen Sitz hat.
            </p>
        </div>
    </section>

    {{-- SSL --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">7. SSL-/TLS-Verschlüsselung</h2>
        <p class="text-foreground/90 leading-relaxed">
            Diese Website nutzt aus Sicherheitsgründen und zum Schutz der Übertragung vertraulicher
            Inhalte eine SSL-/TLS-Verschlüsselung. Eine verschlüsselte Verbindung erkennen Sie am
            <code>https://</code> in der Adresszeile Ihres Browsers.
        </p>
    </section>

    {{-- Änderungsvorbehalt --}}
    <section>
        <h2 class="text-2xl font-bold mb-4">8. Änderungen dieser Datenschutzerklärung</h2>
        <p class="text-foreground/90 leading-relaxed">
            Wir behalten uns vor, diese Datenschutzerklärung anzupassen, damit sie stets den
            aktuellen rechtlichen Anforderungen entspricht oder um Änderungen unserer Leistungen
            umzusetzen — z. B. bei der Einführung neuer Services. Für Ihren erneuten Besuch gilt
            dann die neue Datenschutzerklärung.
        </p>
    </section>

</article>

@endsection
