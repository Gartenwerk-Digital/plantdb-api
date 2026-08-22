#!/usr/bin/env bash
#
# PlantDB API — Forge Deploy Script
# In Forge unter Site → App → Deploy Script eintragen.
# Läuft im Site-Root nach `git pull`.

set -euo pipefail

cd "$FORGE_SITE_PATH" 2>/dev/null || cd "$(dirname "$0")"

echo "→ git pull"
git pull origin "${FORGE_SITE_BRANCH:-main}"

echo "→ composer install"
"$FORGE_COMPOSER" install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "→ npm ci && build"
npm ci
npm run build

echo "→ php artisan migrate --force"
"$FORGE_PHP" artisan migrate --force

echo "→ caches"
"$FORGE_PHP" artisan config:cache
"$FORGE_PHP" artisan route:cache
"$FORGE_PHP" artisan view:cache
"$FORGE_PHP" artisan event:cache

echo "→ restart queue workers"
( flock -w 10 9 || exit 1
  "$FORGE_PHP" artisan queue:restart
) 9>/tmp/plantdb-api-deploy.lock

echo "✓ deploy complete"
