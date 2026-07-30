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

## Contributing

Wir freuen uns über Beiträge! Siehe [CONTRIBUTING.md](CONTRIBUTING.md).

## Lizenzen

- **Code**: [MIT](LICENSE)
- **Pflanzendaten**: [CC BY 4.0](DATA_LICENSE.md)
