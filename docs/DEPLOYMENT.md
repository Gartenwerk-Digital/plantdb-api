# Deployment Runbook — PlantDB API

Zielumgebung: **Laravel Forge auf Hetzner VPS** (EU, DSGVO). Diese Doku beschreibt den End-to-End-Setup von Server-Bestellung bis Post-Deploy-Smoke-Test.

Repo-lokale Bausteine sind bereits vorbereitet (siehe #108): `deploy.sh`, `.env.production.example`, Sentry, Backups, `/health`.

---

## 1. Server-Provisioning (Hetzner)

- **VPS-Typ**: Hetzner CX22 oder CPX21 als Startpunkt (2 vCPU, 4 GB RAM, 40 GB SSD, Location Nürnberg/Falkenstein)
- **OS**: Ubuntu 24.04 LTS (Forge-Standard)
- **Netzwerk**: IPv4 + IPv6, keine zusätzliche Firewall (Forge managed UFW)
- SSH-Key aus Forge in Hetzner-Cloud-Console eintragen, dann Server bestellen. IP notieren.

## 2. Forge-Server verbinden

1. Forge → **Create Server** → „Custom VPS"
2. IP, Provider „Hetzner", PHP 8.4, Database „PostgreSQL 16", Meilisearch **nein**
3. Warten bis Forge provisioniert ist (~10 min). Prüfen: SSH als `forge` funktioniert.
4. Forge → Server → **Daemons**: Redis nachrüsten (optional, für Follow-up-Redis-Migration)

## 3. Domain & DNS

- Domain-Provider (INWX, Namecheap, o. ä.) → DNS-Records:
  - `A @ → <server-ip>`
  - `AAAA @ → <server-ipv6>`
  - `A www → <server-ip>` (redirect via Forge)
- TTL 3600, warten bis Propagation durch (`dig plantdb.example`)

## 4. Site anlegen

1. Forge → Server → **New Site**
   - Root Domain: `plantdb.example`
   - Project Type: „General PHP / Laravel"
   - Web Directory: `/public`
2. Site → **Git Repository**: `Gartenwerk-Digital/plantdb-api`, Branch `main`, Composer install: **an**
3. Site → **Deploy Script**: Inhalt von `deploy.sh` einfügen (oder auf `bash deploy.sh` verweisen). Forge injiziert `$FORGE_PHP`, `$FORGE_COMPOSER`, `$FORGE_SITE_PATH`, `$FORGE_SITE_BRANCH`.
4. Site → **Environment**: Inhalt von `.env.production.example` einfügen, alle `# set via Forge`-Werte ausfüllen (siehe Secrets-Sektion unten).

## 5. Secrets (in Forge Environment)

| Variable | Quelle |
| --- | --- |
| `APP_KEY` | `php artisan key:generate --show` lokal, dann eintragen |
| `DB_USERNAME`, `DB_PASSWORD` | Forge → Database → User |
| `ADMIN_PASSWORD` | Erster Admin-Login (nach Seed rotieren) |
| `MAIL_HOST/USERNAME/PASSWORD` | Postmark/SES-Credentials |
| `R2_*` | Cloudflare Dashboard → R2 → API Token |
| `SENTRY_LARAVEL_DSN` | Sentry-Projekt „plantdb-api" → Settings → Client Keys |
| `BACKUP_ARCHIVE_PASSWORD` | `openssl rand -base64 32`, sicher ablegen |

## 6. Database

1. Forge → Server → **Database** → neue Datenbank `plantdb_api`, User `plantdb`
2. SSH auf Server: `cd /home/forge/plantdb.example && php artisan migrate --force && php artisan db:seed --force`
3. Admin-User prüfen: Login unter `https://plantdb.example/admin`

## 7. SSL

Forge → Site → **SSL** → „Let's Encrypt" → aktivieren. Cert-Renewal läuft automatisch.

## 8. Queue-Worker

Forge → Site → **Queue** → New Worker:
- Connection: `database`
- Queue: `default`
- Processes: 2
- Sleep: 3, Timeout: 60, Tries: 3

## 9. Scheduler

Forge → Server → **Scheduler** → „Add Scheduled Job"
- Command: `php /home/forge/plantdb.example/artisan schedule:run`
- Frequency: „Every Minute"

Laravel-Scheduler ruft dann `backup:clean` (01:00) und `backup:run` (02:00) auf (definiert in `routes/console.php`).

## 10. Sentry

1. Sentry-Projekt anlegen: Platform „Laravel", Org „gartenwerk"
2. DSN in `SENTRY_LARAVEL_DSN` eintragen
3. Test: SSH auf Server → `php artisan tinker` → `throw new RuntimeException('sentry-test')` → Event muss in Sentry auftauchen (Ausnahme wird durch `Integration::handles($exceptions)` in `bootstrap/app.php` reportet)

## 11. Backups

- Ziel: Cloudflare R2 Bucket `plantdb-prod-backups` (separat vom Media-Bucket!)
- Test nach erstem Deploy: `php artisan backup:run` → Zip landet in R2 unter `PlantDB API/`
- **Restore-Test einmal jährlich**: Zip aus R2 laden, in Test-DB einspielen, Login prüfen
- Failure-Notifications: `BACKUP_NOTIFICATION_MAIL_TO` empfängt Mails bei Fehlern

## 12. Monitoring

- **Uptime-Monitor**: Better Stack (kostenlos bis 10 Monitore) → HTTP-Check auf `https://plantdb.example/health`, Interval 3 min, Alert per Mail + Slack
- `/up` (Laravel Standard) bleibt für Forge-interne Health-Checks; `/health` liefert DB/Cache/Queue-Detail (JSON, 200 oder 503)

## 13. Erster Deploy

Forge → Site → **Deploy Now**. Danach:

```bash
curl -s https://plantdb.example/health | jq
curl -s https://plantdb.example/api/v1/ping
curl -sI https://plantdb.example/                # 200 mit HTML
curl -sI https://plantdb.example/sitemap.xml     # 200, application/xml
```

Alle 200 → Go-Live-Smoke-Test (#109) laufen lassen.

## 14. Rollback

Forge zeigt letzte 10 Deploys. Bei Regression:
1. Forge → Site → **Deploy History** → früheren Commit „Redeploy"
2. Falls DB-Migration involviert: `php artisan migrate:rollback --step=1` **manuell auf Server** (Forge macht das nicht automatisch)

## 15. Follow-ups

- Redis für Cache/Queue (Forge → Server → Daemons → Redis, dann `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`)
- Laravel Pulse Dashboard (eigenes Issue)
- CDN vor Media-Bucket (Cloudflare Domain-Fronting oder direkte R2-Public-URL)
