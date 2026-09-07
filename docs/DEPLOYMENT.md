# Deployment Runbook — PlantDB API

Zielumgebung: **Laravel Cloud** (AWS `eu-central-1` / Frankfurt, DSGVO). DNS über Cloudflare (Proxy aus, „DNS only"), TLS von Laravel Cloud (Let's Encrypt).

Repo-lokale Bausteine: `.env.production.example`, Sentry, spatie/laravel-backup, `/health`, `/up`, Mailgun-Mailer.

---

## 1. Projekt in Laravel Cloud anlegen

1. cloud.laravel.com → **New Project**
2. Repo `Gartenwerk-Digital/plantdb-api` verbinden, Branch `main`
3. Region: **eu-central-1** (Frankfurt)
4. PHP 8.3, Compute-Size „Small" (später skalierbar)
5. **Automatic Deploys** auf `main` aktivieren

## 2. Postgres-Datenbank

1. Cloud → Project → **Add Database** → PostgreSQL 16, Name `plantdb_api`
2. Cloud injiziert `DB_HOST/PORT/DATABASE/USERNAME/PASSWORD` automatisch. Nichts manuell setzen.

## 3. Object Storage (Media + Backups)

1. Cloud → **Add Object Storage** → zwei Buckets:
   - `plantdb-media` — Visibility **public**
   - `plantdb-backups` — Visibility **private**
2. Access Keys generieren
3. Env-Vars in Cloud eintragen:
   - `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`
   - `AWS_DEFAULT_REGION=eu-central-1`
   - `AWS_ENDPOINT=<Cloud Object Storage Endpoint>`
   - `MEDIA_BUCKET=plantdb-media`
   - `MEDIA_URL=<public URL des Media-Buckets>`
   - `BACKUP_BUCKET=plantdb-backups`

## 4. Environment Variables

Vorlage: `.env.production.example`. Alle mit `# set via Cloud` markierten Werte manuell in der Cloud-UI eintragen.

| Variable | Quelle |
| --- | --- |
| `APP_KEY` | `php artisan key:generate --show` lokal, dann eintragen |
| `APP_URL` | `https://plantdb.dev` |
| `ADMIN_PASSWORD` | Erster Admin-Login (nach Seed rotieren) |
| `MAILGUN_DOMAIN`, `MAILGUN_SECRET` | Mailgun-Dashboard → EU-Region → Sending API Key |
| `SENTRY_LARAVEL_DSN` | Sentry-Projekt „plantdb-api" → Client Keys |
| `BACKUP_ARCHIVE_PASSWORD` | `openssl rand -base64 32`, sicher ablegen |

## 5. Queue-Worker

Cloud → Project → **Workers** → New Worker:
- Connection: `database`
- Queue: `default`
- Processes: 2
- Timeout: 60, Tries: 3

## 6. Scheduler

Cloud → Project → **Scheduler** → Enable (Cloud ruft `php artisan schedule:run` jede Minute).

Definiert in `routes/console.php`:
- `backup:clean` täglich 01:00
- `backup:run` täglich 02:00

## 7. Deploy-Hook

Cloud → Project → **Deploy** → Build Command / Deploy Steps:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 8. Domain & SSL

1. Cloud → Project → **Domains** → `plantdb.dev` hinzufügen. Cloud gibt CNAME/A-Records aus.
2. Cloudflare → DNS → Records eintragen:
   - Root/`@` → Cloud-Target (A oder CNAME laut Cloud-Instruktion)
   - `www` → CNAME auf Root
3. **Proxy-Status „DNS only" (graue Wolke)** — nicht orange, sonst kollidiert Cloudflare-SSL mit Cloud-SSL
4. Cloud stellt Let's Encrypt-Zertifikat automatisch aus, sobald DNS aufgelöst

## 9. Mailgun-Setup (EU)

1. Mailgun-Account → EU-Region → Domain `plantdb.dev` hinzufügen
2. DNS-Records (SPF/DKIM/MX) aus Mailgun in Cloudflare eintragen
3. Verifikation abwarten
4. Sending API Key → `MAILGUN_SECRET` in Cloud
5. Test (Cloud-Console):
   ```php
   Mail::raw('cloud-test', fn($m) => $m->to('me@example.com')->subject('cloud-test'));
   ```

## 10. Sentry

1. Sentry-Projekt „plantdb-api" (Platform Laravel) anlegen
2. DSN in `SENTRY_LARAVEL_DSN` eintragen
3. Test (Cloud-Console): `php artisan tinker` → `throw new RuntimeException('sentry-test')` → Event muss auftauchen

## 11. Erster Deploy + Seed

1. Push auf `main` → Cloud deployed automatisch
2. Cloud-Console (SSH-artige Shell):
   ```bash
   php artisan migrate --force
   php artisan db:seed --force   # nur beim allerersten Mal!
   ```
3. Admin-Login prüfen: `https://plantdb.dev/admin`

## 12. Smoke-Test

```bash
curl -sI https://plantdb.dev/                   # 200 HTML
curl -s  https://plantdb.dev/health | jq        # {"status":"ok",...}
curl -s  https://plantdb.dev/api/v1/ping        # {"status":"ok"}
curl -sI https://plantdb.dev/sitemap.xml        # 200 xml
curl -sI https://plantdb.dev/impressum          # 200
curl -sI https://plantdb.dev/datenschutz        # 200
```

## 13. Backup-Test

Cloud-Console:
```bash
php artisan backup:run
```
Zip muss im `plantdb-backups`-Bucket unter `PlantDB API/` erscheinen. Failure-Mails gehen an `BACKUP_NOTIFICATION_MAIL_TO`.

**Restore-Test einmal jährlich**: Zip laden, in Test-DB einspielen, Admin-Login prüfen.

## 14. Monitoring

- **Uptime-Monitor** (Better Stack o. ä.): HTTP-Check auf `https://plantdb.dev/health`, Intervall 3 min
- `/up` = Cloud-interner Health-Check
- `/health` = detaillierter DB/Cache/Queue-Status (JSON, 200 oder 503)

## 15. Rollback

Cloud → Deploy History → früheren Commit „Redeploy". Bei DB-Migration involved: `php artisan migrate:rollback --step=1` manuell in der Cloud-Console (Cloud rollt DB nicht automatisch zurück).

## 16. Follow-ups

- Redis für Cache/Queue (Cloud → Add Redis, dann `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`)
- Laravel Pulse Dashboard (eigenes Issue)
- Cloudflare-Proxy einschalten sobald sinnvoll (Origin-Rules + Cloud-IP-Whitelist beachten)
- R2-Legacy-Disks aus `config/filesystems.php` entfernen, sobald Migration vollständig verifiziert
