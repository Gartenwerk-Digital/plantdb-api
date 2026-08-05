# PlantDB API

[![Tests](https://github.com/Gartenwerk-Digital/plantdb-api/actions/workflows/tests.yml/badge.svg)](https://github.com/Gartenwerk-Digital/plantdb-api/actions/workflows/tests.yml)
[![Code Quality](https://github.com/Gartenwerk-Digital/plantdb-api/actions/workflows/code-quality.yml/badge.svg)](https://github.com/Gartenwerk-Digital/plantdb-api/actions/workflows/code-quality.yml)

**Central community-curated plant database API for garden management applications.**

PlantDB ist eine offene, community-gepflegte REST-API für botanische Daten, Pflegehinweise, Fruchtfolge-Empfehlungen sowie Krankheiten und Schädlinge. Ziel ist eine gemeinsame Datenquelle für Garten-Apps, Beet-Planer und Landwirtschaftstools — ohne dass jede App ihre eigene Datenbank aufbauen muss.

## Features (Ziel)

- Pflanzen mit botanischer und mehrsprachiger Common-Name-Struktur
- Familien / Gattungen / Arten / Sorten
- Standort- und Pflegedaten (Licht, Wasser, Boden, pH, Frosthärte)
- Fruchtfolge & Companion Planting
- Krankheiten & Schädlinge
- Bilderverwaltung mit CDN
- Community-Contributions mit Moderationsworkflow

## Stack

Laravel 13 · PHP 8.3+ · PostgreSQL · Sanctum · Filament · Scramble · Pest 4

## Quickstart (lokal, macOS + Herd)

```bash
git clone git@github.com:Gartenwerk-Digital/plantdb-api.git
cd plantdb-api
composer install
cp .env.example .env
php artisan key:generate
createdb plantdb_api
php artisan migrate --seed
```

Herd-Site `plantdb-api` hinzufügen, dann:

- **App**: http://plantdb-api.test
- **Admin**: http://plantdb-api.test/admin (Login: `chrisganzert@gmail.com` / `password`)
- **API-Docs**: http://plantdb-api.test/docs/api

## Befehle

```bash
composer test           # Alles (lint + types + unit)
composer test:lint      # Pint + Rector dry-run
composer test:types     # PHPStan max
composer test:unit      # Pest
composer lint           # Rector + Pint apply
```

## API

Basis-URL: `/api/v1`

Vollständige interaktive Dokumentation unter `/docs/api` (Scramble).

## Internationalization

Die API liefert lokalisierte Inhalte (`common_name`, `description`) für
`Plant`, `Family` und `Genus` direkt als Top-Level-Felder in der
Response. Rohübersetzungen aller Sprachen sind weiterhin per
`?include=translations` verfügbar.

- **Supported Locales**: `de`, `en`
- **Default**: `de`
- **Fallback**: `en` (wenn eine Übersetzung in der angefragten Locale fehlt)
- **Config**: `config/i18n.php`

Die Locale wird pro Request aufgelöst über (Priorität in dieser Reihenfolge):

1. Query-Parameter `?locale=de|en`
2. Header `Accept-Language: de` bzw. `en`
3. Fallback auf `config('i18n.default')`

Ein explizit gesetztes `?locale=` außerhalb der Supported-Liste liefert
**HTTP 400** (kein Silent-Fallback). Jede Response enthält den Header
`Content-Language` mit der tatsächlich verwendeten Locale.

### Beispiele

```bash
# Deutsch via Accept-Language-Header
curl -H "Accept-Language: de" \
  http://plantdb-api.test/api/v1/plants/solanum-lycopersicum

# Englisch via Query-Parameter (überschreibt den Header)
curl "http://plantdb-api.test/api/v1/plants/solanum-lycopersicum?locale=en"
```

### Neue Locales hinzufügen

1. `supported` in `config/i18n.php` erweitern.
2. Übersetzungen in den `*_translations`-Tabellen seeden.
3. Bruno-Requests unter `bruno/**/i18n/` sinngemäß ergänzen.

## Contributing

Wir freuen uns über Beiträge! Siehe [CONTRIBUTING.md](CONTRIBUTING.md).

## Lizenzen

- **Code**: [MIT](LICENSE)
- **Pflanzendaten**: [CC BY 4.0](DATA_LICENSE.md)
