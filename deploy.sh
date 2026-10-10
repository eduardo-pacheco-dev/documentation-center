#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPOSITORY_URL="https://github.com/eduardo-pacheco-dev/documentation-center.git"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
WEB_USER="${WEB_USER:-}"

log() { printf '\n==> %s\n' "$*"; }
fail() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }

# Resolve the OS user that actually runs PHP-FPM / the web server, so the
# SQLite database and writable directories are owned by the process that
# needs to write to them.
resolve_web_user() {
    local owner

    if [ -f storage/logs/laravel.log ]; then
        owner="$(stat -c '%U' storage/logs/laravel.log 2>/dev/null || true)"
        if [ -n "$owner" ] && [ "$owner" != "root" ] && id -u "$owner" >/dev/null 2>&1; then
            printf '%s' "$owner"
            return 0
        fi
    fi

    if [ -n "$WEB_USER" ] && [ "$WEB_USER" != "root" ] && id -u "$WEB_USER" >/dev/null 2>&1; then
        printf '%s' "$WEB_USER"
        return 0
    fi

    for candidate in www-data apache nginx; do
        if id -u "$candidate" >/dev/null 2>&1; then
            printf '%s' "$candidate"
            return 0
        fi
    done

    return 1
}

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
    log "Updating repository to origin/$DEPLOY_BRANCH"
    git fetch --prune origin "$DEPLOY_BRANCH"
    if ! git merge --ff-only FETCH_HEAD; then
        log "Fast-forward not possible; resetting to origin/$DEPLOY_BRANCH (tracked server-side changes are discarded)"
        git reset --hard "origin/$DEPLOY_BRANCH"
    fi
    export DEPLOY_REEXEC=1
    exec bash "$APP_DIR/deploy.sh"
fi

if grep -qE '^APP_DEBUG=true\r?$' .env; then
    log "Warning: APP_DEBUG is true in .env - set it to false in production"
fi

log "Installing PHP dependencies"
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

log "Clearing stale caches"
php artisan optimize:clear

log "Ensuring storage directories and writable paths"
mkdir -p storage/framework/cache/data storage/framework/views storage/framework/sessions storage/logs database bootstrap/cache
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    log "Created database/database.sqlite"
fi

# SQLite needs to write both the database file and its directory (for the
# journal/WAL files), so the whole `database` directory must be writable.
if [ "$(id -u)" -eq 0 ]; then
    if RESOLVED_WEB_USER="$(resolve_web_user)"; then
        log "Granting ownership of writable paths to '$RESOLVED_WEB_USER'"
        chown -R "$RESOLVED_WEB_USER":"$RESOLVED_WEB_USER" storage database bootstrap/cache
        chmod -R ug+rwX storage database bootstrap/cache
        chmod ug+rw database/database.sqlite
    else
        fail "Could not determine the web user; set the WEB_USER secret to the user that runs PHP-FPM"
    fi
else
    log "Warning: running as non-root; cannot change ownership. Ensure '$(id -un)' owns storage, database and bootstrap/cache."
    chmod -R ug+rwX storage database bootstrap/cache 2>/dev/null || true
fi

if grep -qE '^APP_KEY=\r?$' .env; then
    log "Generating application key"
    php artisan key:generate --force
fi

trap 'php artisan up >/dev/null 2>&1 || true' EXIT

log "Entering maintenance mode"
php artisan down --retry=30

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
php artisan migrate --force

log "Deploy completed"
