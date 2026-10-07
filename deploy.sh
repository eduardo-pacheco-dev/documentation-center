#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPOSITORY_URL="https://github.com/eduardo-pacheco-dev/documentation-center.git"

log() { printf '\n==> %s\n' "$*"; }
fail() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }

command -v git >/dev/null 2>&1 || fail "git is not installed"
command -v php >/dev/null 2>&1 || fail "php is not installed"
command -v composer >/dev/null 2>&1 || fail "composer is not installed"
command -v npm >/dev/null 2>&1 || fail "npm is not installed (Node.js is required)"
[ -f "$APP_DIR/artisan" ] || fail "Laravel application not found in $APP_DIR"
[ -f "$APP_DIR/.env" ] || fail ".env file is missing in $APP_DIR"

cd "$APP_DIR"

if [ ! -d .git ]; then
    fail "$APP_DIR is not a git repository. Clone $REPOSITORY_URL here first."
fi

if [ -z "${DEPLOY_REEXEC:-}" ]; then
    log "Pulling latest code"
    git pull --ff-only origin main
    export DEPLOY_REEXEC=1
    exec bash "$APP_DIR/deploy.sh"
fi

trap 'php artisan up >/dev/null 2>&1 || true' EXIT

log "Entering maintenance mode"
php artisan down --retry=30

log "Installing PHP dependencies"
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

log "Building frontend assets"
npm ci
npm run build

log "Caching configuration, routes and views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

log "Linking storage"
php artisan storage:link

log "Running database migrations"
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    log "Created database/database.sqlite"
fi
php artisan migrate --force

log "Setting permissions"
chmod -R ug+rwx storage bootstrap/cache

log "Deploy completed"
