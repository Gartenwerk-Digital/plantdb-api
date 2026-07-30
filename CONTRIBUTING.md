# Contributing to PlantDB API

Danke, dass du mithelfen willst! PlantDB lebt von der Community.

## Arten von Beiträgen

1. **Pflanzendaten** — neue Pflanzen, Korrekturen, Übersetzungen, Bilder
2. **Code** — Bugfixes, Features, Tests, Docs
3. **Feedback** — Issues, Feature-Requests, Diskussionen

## Pflanzendaten beitragen

Community-Contributions laufen über einen Moderationsworkflow:

1. Contribution via API `POST /api/v1/contributions` oder Admin-UI einreichen
2. Moderatoren prüfen und ergänzen fehlende Belege
3. Nach Freigabe: Aufnahme in die Hauptdatenbank

Daten stehen unter [CC BY 4.0](DATA_LICENSE.md).

## Code beitragen

### Setup

Siehe [README.md](README.md#quickstart-lokal-macos--herd).

### Workflow

1. Issue prüfen oder neues Issue öffnen
2. Fork oder Branch `feat/<slug>` von `develop`
3. Änderungen commiten (Conventional Commits, englisch)
4. `composer test` muss grün sein
5. PR gegen `develop` öffnen

### Code-Standards

- `declare(strict_types=1);` in jeder PHP-Datei
- Klassen `final`
- Pest-Tests für neue Features (Feature-Tests bevorzugt, echte PostgreSQL, keine DB-Mocks)
- PHPStan Level `max` muss grün bleiben
- Pint & Rector: `composer lint` vor Commit

### Commit-Konventionen

```
feat: add plant taxonomy endpoint
fix: correct latin name normalization
docs: update API examples
test: cover contribution moderation
chore: bump dependencies
```

## Verhaltenskodex

Sei respektvoll, konstruktiv, geduldig. Wir tolerieren keinerlei Belästigung. Bei Verstößen bitte an chrisganzert@gmail.com wenden.

## Lizenz-Zustimmung

Mit deinem Beitrag stimmst du zu, dass:

- **Code**-Beiträge unter der [MIT-Lizenz](LICENSE) veröffentlicht werden
- **Daten**-Beiträge unter [CC BY 4.0](DATA_LICENSE.md) veröffentlicht werden
