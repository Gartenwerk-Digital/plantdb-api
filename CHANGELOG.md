# Changelog

Alle nennenswerten Änderungen an diesem Projekt werden hier dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
und dieses Projekt hält sich an [Semantic Versioning](https://semver.org/lang/de/).

## [Unreleased]

## [0.5.0] — 2026-08-02

Sprint 4 — Community Contributions. Öffentlicher Einreiche-Endpoint
für Pflanzenvorschläge inkl. Admin-Review-Workflow.

### Added
- `POST /api/v1/contributions` — authentifizierter Endpoint zum
  Einreichen von Community-Vorschlägen (neue Pflanze oder Update zu
  bestehender Pflanze), Payload-Validierung inkl. Pflichtfelder
  `family_id`/`genus_id` bei `new_plant`, Persistenz als
  `Contribution` mit Status `pending` (#14)
- Filament Contribution-Review-Queue unter „Moderation": Listenansicht
  mit Status-Filter, Detail-View mit Payload-Diff sowie
  Approve/Reject-Actions (Reject verlangt `review_notes`, setzt
  `reviewed_by`/`reviewed_at`) (#15)
- Composer-Skript `composer dev` für lokalen Entwicklungs-Stack
- Bruno-Collection `bruno/contributions/` inkl. Auto-Populate von
  `familyId`/`genusId` für Sample-Requests

## [0.4.0] — 2026-08-02

Sprint 3 — Admin Panel. Filament-basierte Verwaltung für Taxonomie
und Pflanzen inkl. Moderations-Workflow.

### Added
- Filament-Panel hinter der `admin`-Rolle (spatie/permission) statt
  `app()->isLocal()`, `RolesAndPermissionsSeeder` legt die Rolle an
  und `AdminUserSeeder` weist sie dem lokalen Admin zu; Panel-Branding
  als „PlantDB Admin" (#11)
- Filament `FamilyResource` und `GenusResource` unter Navigations-
  Gruppe „Taxonomy" mit vollem CRUD, `plants_count`-Spalte (nur
  `PlantStatus::Approved`), `GeneraRelationManager` auf `Family` sowie
  Delete-Schutz via `FamilyPolicy`/`GenusPolicy`, sobald verknüpfte
  Plants existieren (#13)
- Filament `PlantResource` mit Tab-Formular (Botanik, Standort, Wuchs,
  Blüte/Frucht, Pflege, Eigenschaften, Moderation), Status-Badge-Spalte
  und Filtern (`status`, `family_id`, `life_cycle`); Approve/Reject als
  Row-, Bulk- und Header-Action (setzt `reviewed_by`/`reviewed_at`,
  Reject verlangt `review_notes`); RelationManagers für Images,
  Companions (self-referencing pivot mit `relationship`/`notes`) und
  CareTasks (#12)

## [0.3.0] — 2026-08-02

Sprint 2 — Auth & Rate Limiting. Named API-Keys via Sanctum und
tier-basierte Tageskontingente pro Token.

### Added
- Named-API-Key-Verwaltung via Sanctum: `GET/POST /api/v1/api-keys`
  und `DELETE /api/v1/api-keys/{id}`. Plaintext-Token wird nur einmal
  bei Erstellung zurückgegeben; Abilities via Enum `TokenAbility`
  (`read`, `write`). Erfordert verifizierte E-Mail (#9)
- Filament `UserResource` im Admin-Panel mit Read-only-Übersicht
  und `TokensRelationManager` zum Widerrufen einzelner API-Keys (#9)
- Bruno-Collection `bruno/api-keys/` (list, create, revoke) (#9)
- Tier-basierte Rate Limits pro API-Token via `UserTier`-Enum
  (free/pro/enterprise) mit täglichem Quota, `X-RateLimit-*`-Response-
  Headers und 429-Handling im globalen Error-Shape (#10)
- `tier`-Feld in `UserResource` und `/api/v1/me`-Response (#10)
- Filament: Tier auf `UserResource` Edit-Page verwaltbar (#10)
- Bruno-Requests `bruno/system/rate-limit-headers.bru` und
  `rate-limit-exceeded.bru` sowie Scramble-Doku zu Tier-Limits (#10)

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

[Unreleased]: https://github.com/Gartenwerk-Digital/plantdb-api/compare/v0.5.0...HEAD
[0.5.0]: https://github.com/Gartenwerk-Digital/plantdb-api/compare/v0.4.0...v0.5.0
[0.4.0]: https://github.com/Gartenwerk-Digital/plantdb-api/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/Gartenwerk-Digital/plantdb-api/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/Gartenwerk-Digital/plantdb-api/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/Gartenwerk-Digital/plantdb-api/releases/tag/v0.1.0
