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

## Git-Workflow (immer einhalten)

Jedes Issue wird nach diesem Schema abgearbeitet:

1. **Branch:** `git checkout develop && git pull && git checkout -b feat/<issue-nr>-<kurz-slug>`
2. **Implementieren** — Code, Tests, `composer test` grün
3. **Commit:** Conventional Commits auf Englisch (`feat:`, `fix:`, `chore:`, `test:`, `docs:`)
4. **Push:** `git push -u origin feat/<issue-nr>-<kurz-slug>`
5. **PR erstellen** via `gh pr create` mit:
   - `--base develop`
   - `--title` passend zum Issue
   - `--body` enthält zwingend `Closes #<nr>` (schließt Issue automatisch beim Merge)
   - `--label` dieselben Labels wie das Issue
   - `--milestone` derselbe Milestone wie das Issue
   - `--assignee ChrisPuh`
6. **PR-URL ausgeben**

Nie direkt auf `develop` oder `main` pushen. Alles über PRs.

## Sprint-Abschluss-Workflow (Release)

Wenn alle Issues eines Milestones geschlossen sind und Sprint N released werden soll:

1. **Version bestimmen** (SemVer)
   - Pro Sprint: MINOR-Bump auf `develop` (`v0.2.0` nach Sprint 1, `v0.3.0` nach Sprint 2, …)
   - Hotfixes zwischen Sprints: PATCH (`v0.1.1`)
   - Erst `v1.0.0`, wenn die öffentliche `/api/v1/*`-Oberfläche stabil ist (voraussichtlich nach Sprint 7)

2. **CHANGELOG-Eintrag** (Keep a Changelog Format)
   - Eigener Branch: `chore/release-vX.Y.Z-changelog` von `develop`
   - `## [Unreleased]` in `## [X.Y.Z] — YYYY-MM-DD` umbenennen
   - Neue leere `## [Unreleased]` Section darüber anlegen
   - Compare-Links am Datei-Ende aktualisieren
   - PR gegen `develop`, Titel `chore: changelog for vX.Y.Z`

3. **Release-PR** `develop → main`
   ```bash
   gh pr create --base main --head develop \
     --title "Release vX.Y.Z — Sprint N <Name>" \
     --body "Sprint N release. See CHANGELOG.md."
   ```
   Merge **via GitHub UI** mit „Create a merge commit" (nicht Squash, nicht Rebase). Der Merge-Commit ist der Release-Marker auf `main`.

4. **Tag + GitHub Release** auf `main`
   ```bash
   git checkout main && git pull
   git tag -a vX.Y.Z -m "Sprint N — <Name>"
   git push origin vX.Y.Z
   gh release create vX.Y.Z \
     --title "vX.Y.Z — Sprint N: <Name>" \
     --notes-file <(awk '/## \[X.Y.Z\]/,/## \[/{if(/## \[/ && !/X.Y.Z/)exit; print}' CHANGELOG.md) \
     --latest
   ```

5. **Milestone schließen**
   ```bash
   gh api -X PATCH repos/Gartenwerk-Digital/plantdb-api/milestones/<N> -f state=closed
   ```

6. **Sprint N+1 vorbereiten**
   - `git checkout develop && git pull` (Merge-Commit von `main` zurückholen, falls nötig)
   - Offene Issues im nächsten Milestone sichten, Reihenfolge klären
   - Ersten Feature-Branch nach dem Standard-Git-Workflow starten

Während eines Sprints: nichts davon — nur der Feature-Branch/PR-Workflow oben.
