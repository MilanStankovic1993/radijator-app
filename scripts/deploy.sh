#!/usr/bin/env bash
# ============================================
# Deploy: Projekat-Radijator-Inzenjering (root)
# Server: Hetzner Ubuntu (Nginx + PHP-FPM)
# App: Laravel + Vite
# ============================================

# -------- Podesive varijable --------
SSH_USER="root"
SERVER_IP="91.99.115.89"
APP_DIR="/var/www/Projekat-Radijator-Inzenjering"   # putanja NA SERVERU
GIT_BRANCH="main"

PHP_BIN="/usr/bin/php"
# Ako znaš tačnu putanju, postavi je; u suprotnom ostavi na /usr/bin/composer a skripta će auto-pronaći/instalirati.
COMPOSER_BIN="/usr/bin/composer"

PHP_FPM_SERVICE="php8.3-fpm"                        # "" ako ne želiš reload
PUBLIC_BASE_URL="https://radijatorapp.duckdns.org"  # informativno
WEB_USER="www-data"                                 # korisnik web servera (nginx/php-fpm)

# -------- Ne diraj ispod ovog reda --------
set -euo pipefail
echo "🚀 Deploy -> $SSH_USER@$SERVER_IP ($APP_DIR) branch=$GIT_BRANCH"

ssh -o StrictHostKeyChecking=accept-new "$SSH_USER@$SERVER_IP" \
  APP_DIR="$APP_DIR" \
  GIT_BRANCH="$GIT_BRANCH" \
  PHP_BIN="$PHP_BIN" \
  COMPOSER_BIN="$COMPOSER_BIN" \
  PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-}" \
  PUBLIC_BASE_URL="$PUBLIC_BASE_URL" \
  WEB_USER="$WEB_USER" \
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
PUBLIC_BASE_URL="${PUBLIC_BASE_URL:-}"
WEB_USER="${WEB_USER:-www-data}"

# 0) Provera da repo postoji
if [ ! -d "$APP_DIR/.git" ]; then
  echo "❌ APP_DIR ne postoji ili nije git repo: $APP_DIR"
  echo "   Na serveru uradi: mkdir -p $APP_DIR && cd $APP_DIR && git clone <REPO_URL> ."
  exit 1
fi

cd "$APP_DIR"

# 1) Composer – auto pronađi/instaliraj ako COMPOSER_BIN ne postoji
if [ ! -x "$COMPOSER_BIN" ]; then
  if command -v composer >/dev/null 2>&1; then
    COMPOSER_BIN="$(command -v composer)"
    log "Pronađen Composer: $COMPOSER_BIN"
  else
    log "Composer nije na $COMPOSER_BIN i nije u PATH-u → instaliram ga u /usr/local/bin/composer"
    EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig)"
    wget -q -O composer-setup.php https://getcomposer.org/installer
    ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
      echo "❌ Composer installer signature mismatch"; rm -f composer-setup.php; exit 1
    fi
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer >/dev/null
    rm -f composer-setup.php
    COMPOSER_BIN="/usr/local/bin/composer"
    log "Composer instaliran na: $COMPOSER_BIN"
  fi
fi

# 2) Git osvežavanje
log "Git fetch/reset → origin/$GIT_BRANCH"
git fetch --all --prune
git reset --hard "origin/$GIT_BRANCH"
git clean -fd

# 3) PHP depovi
log "Composer install (no-dev, optimized)"
"$COMPOSER_BIN" install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# 4) FRONTEND (Vite) BUILD
log "Provera Node & NPM"
if ! command -v node >/dev/null 2>&1 || ! command -v npm >/dev/null 2>&1; then
  echo "❌ Node/NPM nisu instalirani na serveru. Instaliraj Node LTS (npr. 18/20)."
  exit 1
fi

log "Čišćenje starog Vite build-a"
rm -rf public/build

log "NPM ci + build (Vite)"
npm ci --no-audit --no-fund
npm run build

if [ ! -f public/build/manifest.json ]; then
  echo "❌ Vite manifest.json nije generisan. Prekidam."
  exit 1
fi

# 5) Maintenance ON (sa secret-om)
SECRET=$("$PHP_BIN" -r 'echo bin2hex(random_bytes(16));')
log "Maintenance mode ON (secret=$SECRET)"
"$PHP_BIN" artisan down --secret="$SECRET" --render="errors::503" || true

# 6) Migracije i cache
log "Migrate + seed (force)"
"$PHP_BIN" artisan migrate --force --seed

log "Optimize & cache"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

# 7) storage:link (idempotentno)
if [ ! -L public/storage ]; then
  log "storage:link"
  "$PHP_BIN" artisan storage:link || true
fi

# 8) permisije za storage i cache (često bitno)
log "Permissions for storage/ & bootstrap/cache/"
chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache || true

# 9) Queue & PHP-FPM
log "Queue restart"
"$PHP_BIN" artisan queue:restart || true

if [ -n "$PHP_FPM_SERVICE" ]; then
  log "Reload PHP-FPM: $PHP_FPM_SERVICE"
  systemctl reload "$PHP_FPM_SERVICE" || true
fi

# 10) App UP
log "Bring app UP"
"$PHP_BIN" artisan up

echo
echo "✅ Deploy gotov."
[ -n "$PUBLIC_BASE_URL" ] && echo "ℹ️ Privremeni maintenance-bypass link (bio tokom down): ${PUBLIC_BASE_URL}/${SECRET}"

echo
log "Provera Vite fajlova"
ls -la public/build | sed 's/^/    /'
test -f public/build/manifest.json && echo "    ✔ manifest.json postoji"
REMOTE
