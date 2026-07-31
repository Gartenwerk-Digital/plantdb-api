# Changelog

Alle nennenswerten Änderungen an diesem Projekt werden hier dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
und dieses Projekt hält sich an [Semantic Versioning](https://semver.org/lang/de/).

## [Unreleased]

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

[Unreleased]: https://github.com/Gartenwerk-Digital/plantdb-api/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/Gartenwerk-Digital/plantdb-api/releases/tag/v0.1.0
