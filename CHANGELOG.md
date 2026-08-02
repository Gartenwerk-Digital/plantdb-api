# Changelog

Alle nennenswerten Änderungen an diesem Projekt werden hier dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
und dieses Projekt hält sich an [Semantic Versioning](https://semver.org/lang/de/).

## [Unreleased]

### Added
- Named-API-Key-Verwaltung via Sanctum: `GET/POST /api/v1/api-keys`
  und `DELETE /api/v1/api-keys/{id}`. Plaintext-Token wird nur einmal
  bei Erstellung zurückgegeben; Abilities via Enum `TokenAbility`
  (`read`, `write`). Erfordert verifizierte E-Mail (#9)
- Filament `UserResource` im Admin-Panel mit Read-only-Übersicht
  und `TokensRelationManager` zum Widerrufen einzelner API-Keys (#9)
- Bruno-Collection `bruno/api-keys/` (list, create, revoke) (#9)

## [0.2.0] — 2026-08-01

Sprint 1 — Core API. Lesende v1-Endpunkte für Plants, Genera, Families
und einheitliches Response-Envelope.

### Added
- Einheitliches API-Response-Envelope `{data, meta, links}` und
  globaler Error-Shape `{error: {code, message, details}}`;
  Exception-Handler für 401/403/404/422/429/500;
  Scramble-Docs mit globalen Error-Schemas (#8)
- `GET /api/v1/families` und `GET /api/v1/genera` — Read-Endpunkte
  mit Pagination, Filter und plants_count (#7)
- `GET /api/v1/plants/{slug}` — Pflanzensteckbrief mit optionalen
  Includes `?include=images,companions,care_tasks,translations` (#6)
- `GET /api/v1/plants` — Pflanzenliste mit Filtern (life_cycle,
  sun_requirement, soil_moisture, bloom_month, edible,
  toxic_to_pets, q-Freitextsuche via PostgreSQL tsvector) (#5)

## [0.1.0] — 2026-07-31

Sprint 0 — Foundation. Erstes lauffähiges Skeleton der PlantDB API.

### Added
- Laravel 13 API-Skeleton auf Basis von Grazulex/laravel-api-kit (#1, #31)
- Health-Endpoint `GET /api/v1/ping` (#1)
- Core-Datenbankschema: `families`, `genera`, `plants` inkl. typed Enums,
  Eloquent-Modelle, Factories und Seeder mit drei Beispielpflanzen (#2, #32)
- GitHub-Repo-Setup: Issue-Templates, Branch-Protection auf `main` (#3, #33)
- CI-Pipeline (GitHub Actions): Pest-Tests, Pint, PHPStan `max`,
  Rector dry-run, Composer-Cache (#4, #34)
- Bruno-Collection für lokale API-Exploration
- `CLAUDE.md` mit Projekt-Konventionen und Git-Workflow

[Unreleased]: https://github.com/Gartenwerk-Digital/plantdb-api/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/Gartenwerk-Digital/plantdb-api/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/Gartenwerk-Digital/plantdb-api/releases/tag/v0.1.0
