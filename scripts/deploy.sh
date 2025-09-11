#!/usr/bin/env bash
# ============================================
# Deploy: Projekat-Radijator-Inzenjering (root)
# Hetzner Ubuntu (Nginx + PHP-FPM)
# ============================================

# ---- Podesive varijable ----
SSH_USER="root"
SERVER_IP="91.99.115.89"
APP_DIR="/var/www/Projekat-Radijator-Inzenjering"
GIT_BRANCH="main"
PHP_BIN="/usr/bin/php"
COMPOSER_BIN="/usr/bin/composer"
PHP_FPM_SERVICE="php8.3-fpm"     # "" ako ne želiš reload

# ---- Ne diraj ispod ovog reda ----
set -euo pipefail
echo "🚀 Deploy -> $SSH_USER@$SERVER_IP ($APP_DIR) branch=$GIT_BRANCH"

ssh -o StrictHostKeyChecking=accept-new "$SSH_USER@$SERVER_IP" \
  APP_DIR="$APP_DIR" \
  GIT_BRANCH="$GIT_BRANCH" \
  PHP_BIN="$PHP_BIN" \
  COMPOSER_BIN="$COMPOSER_BIN" \
  PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-}" \
  COMPOSER_ALLOW_SUPERUSER=1 \
  COMPOSER_MEMORY_LIMIT=-1 \
  bash -s <<'REMOTE'
set -euo pipefail
log(){ echo -e "\n==> $*"; }

: "${APP_DIR:?APP_DIR is required}"
: "${GIT_BRANCH:?GIT_BRANCH is required}"
: "${PHP_BIN:?PHP_BIN is required}"
: "${COMPOSER_BIN:?COMPOSER_BIN is required}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-}"

cd "$APP_DIR"

log "Git fetch/reset → origin/$GIT_BRANCH"
git fetch --all --prune
git reset --hard "origin/$GIT_BRANCH"
git clean -fd

log "Composer install (no-dev, optimized)"
"$COMPOSER_BIN" install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Maintenance sa secret-om (zero-downtime provera)
SECRET=$("$PHP_BIN" -r 'echo bin2hex(random_bytes(16));')
log "Maintenance mode (secret=$SECRET)"
"$PHP_BIN" artisan down --secret="$SECRET" --render="errors::503" || true

log "Migrate + seed (FORCE)"
"$PHP_BIN" artisan migrate --force --seed

log "Optimize & cache"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

# (Idempotentno) link za storage
if [ ! -L public/storage ]; then
  log "storage:link"
  "$PHP_BIN" artisan storage:link || true
fi

# (Opcionalno) publish/upgrade asseta paketa (bezbedno je, ali nije obavezno)
# "$PHP_BIN" artisan filament:upgrade || true

log "Queue restart"
"$PHP_BIN" artisan queue:restart || true

if [ -n "$PHP_FPM_SERVICE" ]; then
  log "Reload PHP-FPM: $PHP_FPM_SERVICE"
  systemctl reload "$PHP_FPM_SERVICE" || true
fi

log "Bring app UP"
"$PHP_BIN" artisan up

echo
echo "✅ Deploy završen. Privremeni URL (dok je maintenance): https://tvoja-domena.com/$SECRET"
REMOTE
