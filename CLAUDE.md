# CLAUDE.md — PlantDB API

Zentraler Kontext für Claude Code beim Arbeiten an diesem Projekt.

## Projekt

**PlantDB API** — Open-Source, community-gepflegte Pflanzendatenbank-API für Garten-Apps. Zentrale Quelle für botanische Daten, Pflegehinweise, Fruchtfolge-Empfehlungen, Krankheiten/Schädlinge, mehrsprachige Namen.

- Konzept: `~/PhpstormProjects/plantdb-api-konzept.md`
- Sprint-Issues (Vorlage): `~/PhpstormProjects/plantdb-github-issues.md`
- Repository: https://github.com/Gartenwerk-Digital/plantdb-api

## Stack

- **Framework**: Laravel 13 (Basis: [Grazulex/laravel-api-kit](https://github.com/Grazulex/laravel-api-kit))
- **PHP**: 8.3+
- **Datenbank**: PostgreSQL (lokal via Laravel Herd)
- **Auth**: Laravel Sanctum (API-Tokens)
- **Admin-UI**: Filament v3 unter `/admin`
- **API-Docs**: Scramble (`/docs/api`)
- **Media**: spatie/laravel-medialibrary
- **Rollen**: spatie/laravel-permission
- **Tests**: Pest 4 (+ Arch-Plugin)
- **Static Analysis**: Larastan Level `max`
- **Code Style**: Pint (Laravel-Preset + strict_types, ordered_imports alpha)
- **Refactoring**: Rector (PHP 8.3 + Laravel)

## Konventionen

- Jede PHP-Datei beginnt mit `declare(strict_types=1);`
- Klassen sind `final` (via Pint-Rule)
- API-Versionierung: alle Endpunkte unter `/api/v1/*`
- **Actions-Pattern**: Business-Logik in `app/Actions/*` (single-purpose invokable classes)
- **DTOs**: `spatie/laravel-data` in `app/DTOs/`
- **Enums**: `app/Enums/` (typed backed enums)
- **Requests**: FormRequests in `app/Http/Requests/Api/V1/`
- **Resources**: JsonResources in `app/Http/Resources/Api/V1/`
- **Migrations**: PostgreSQL-Features nutzen (jsonb, generated columns, GIN-Indizes)
- **Tests**: Feature-Tests gegen echte PostgreSQL (keine Mocks für DB)
- **Commits**: Conventional Commits, englisch (`chore:`, `feat:`, `fix:`, `docs:`, `test:`)
- **Branches**: `main` = production, `develop` = active. Feature-Branches: `feat/<slug>`, PRs auf `develop`.

## Nützliche Befehle

```bash
composer test           # Lint + Types + Unit
composer test:lint      # Pint + Rector dry-run
composer test:types     # PHPStan
composer test:unit      # Pest

composer lint           # Rector + Pint apply

php artisan migrate:fresh --seed   # DB neu + Admin-User
php artisan route:list             # Alle API-Routen
php artisan filament:make-resource # Filament Resource generator
```

## URLs (lokal, Herd)

- App: http://plantdb-api.test
- Admin: http://plantdb-api.test/admin (Login: chrisganzert@gmail.com / password)
- API-Docs: http://plantdb-api.test/docs/api

## Wichtige Constraints

- Kein Mock der DB in Feature-Tests
- Kein hardcoded Secret — immer via `.env`
- Kein `--no-verify` bei Commits
- Nichts pushen, was `composer test` bricht
