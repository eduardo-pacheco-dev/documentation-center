#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPOSITORY_URL="https://github.com/eduardo-pacheco-dev/documentation-center.git"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
WEB_USER="${WEB_USER:-}"

log() { printf '\n==> %s\n' "$*"; }
fail() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }

# Resolve the OS user that actually runs PHP-FPM / the web server, so the
# writable directories are owned by the process that needs to write to them.
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

# Install/refresh a systemd service that keeps a queue worker alive so queued
# jobs (e.g. backups) run without a manually started process. Falls back to a
# graceful worker restart when not running as root or systemd is unavailable.
configure_queue_worker() {
    local php_bin service_name service_path run_user

    php_bin="$(command -v php)"
    service_name="$(basename "$APP_DIR" | tr -c 'a-zA-Z0-9' '-')-worker"

    log "Restarting queue workers so they load the new code"
    php artisan queue:restart || true

    if ! command -v systemctl >/dev/null 2>&1; then
        log "systemctl not found; ensure 'php artisan queue:work' runs as the web user"
        return 0
    fi

    if [ "$(id -u)" -ne 0 ]; then
        log "Warning: not running as root; skipping systemd worker setup"
        log "Ensure a 'php artisan queue:work' process runs as the web user"
        return 0
    fi

    if ! run_user="$(resolve_web_user)"; then
        fail "Could not determine the web user for the queue worker; set the WEB_USER secret"
    fi

    service_path="/etc/systemd/system/${service_name}.service"

    log "Installing queue worker service '${service_name}'"
    cat > "$service_path" <<EOF
[Unit]
Description=Documentation Center queue worker
After=network.target

[Service]
Type=simple
User=${run_user}
Group=${run_user}
Restart=always
RestartSec=5
WorkingDirectory=${APP_DIR}
ExecStart=${php_bin} artisan queue:work --sleep=3 --tries=1 --timeout=1900 --max-time=3600
StopAsGroup=true
KillMode=mixed

[Install]
WantedBy=multi-user.target
EOF

    systemctl daemon-reload
    systemctl enable "$service_name" >/dev/null 2>&1 || true
    systemctl restart "$service_name"

    sleep 1
    if systemctl is-active --quiet "$service_name"; then
        log "Queue worker is running"
    else
        log "Warning: queue worker is not active; check 'journalctl -u ${service_name}'"
    fi
}

# Raise the upload limits so the app's 100 MB attachments work in production.
# PHP rejects anything bigger than upload_max_filesize/post_max_size and nginx
# rejects the whole request with 413 before PHP runs when the body is bigger
# than client_max_body_size (nginx defaults to 1 MB).
# Both settings are applied as drop-in files/directives and only when needed,
# so running the deploy twice keeps the server configuration stable.
configure_upload_limits() {
    local upload_max="${UPLOAD_MAX_SIZE:-100M}"
    local post_max="${POST_MAX_SIZE:-128M}"
    local body_size="${CLIENT_MAX_BODY_SIZE:-128m}"
    local host="" site_conf="" socket php_version="" ini_written=0
    local candidate dir target backup tmp

    if [ "$(id -u)" -ne 0 ]; then
        log "Warning: not running as root; skipping the PHP/nginx upload configuration"
        log "Manually set upload_max_filesize=${upload_max} and post_max_size=${post_max} in php.ini, add 'client_max_body_size ${body_size};' to the vhost, then reload php-fpm and nginx"
        return 0
    fi

    # Domain from APP_URL, used to locate the vhost file (e.g. sites-available/example.com.conf).
    host="$(grep -E '^[[:space:]]*APP_URL[[:space:]]*=' "$env_file" | head -n 1 | sed -E 's/^[^=]*=[[:space:]]*//; s/["'"'"']//g; s#^[a-zA-Z0-9]+://##; s#/.*$##; s/:[0-9]+$//' || true)"

    if [ -n "$host" ]; then
        for candidate in \
            "/etc/nginx/sites-available/${host}.conf" \
            "/etc/nginx/sites-available/${host}" \
            "/etc/nginx/sites-enabled/${host}.conf" \
            "/etc/nginx/sites-enabled/${host}" \
            "/etc/nginx/conf.d/${host}.conf"; do
            if [ -f "$candidate" ]; then
                site_conf="$candidate"
                break
            fi
        done
    fi

    # ---- nginx ----
    if ! command -v nginx >/dev/null 2>&1; then
        log "nginx not found; skipping the client_max_body_size configuration"
    elif [ -z "$site_conf" ]; then
        log "Warning: no nginx vhost found for '${host}'; add 'client_max_body_size ${body_size};' to its server blocks manually"
    else
        log "Configuring the upload limit in $site_conf"
        backup="$(mktemp)"
        tmp="$(mktemp)"
        cp "$site_conf" "$backup"

        if grep -qE '^[[:space:]]*client_max_body_size[[:space:]]' "$site_conf"; then
            sed -i -E "s|^([[:space:]]*)client_max_body_size[[:space:]]+[^;]*;|\1client_max_body_size ${body_size};|g" "$site_conf"
            log "client_max_body_size updated to ${body_size}"
        else
            awk -v size="$body_size" '
                /^[[:space:]]*server[[:space:]]*\{[[:space:]]*$/ {
                    print
                    print "        client_max_body_size " size ";"
                    next
                }
                { print }
            ' "$site_conf" > "$tmp"
            cat "$tmp" > "$site_conf"
            log "client_max_body_size ${body_size} added to every server block of the vhost"
        fi

        rm -f "$tmp"

        if nginx -t >/dev/null 2>&1; then
            if command -v systemctl >/dev/null 2>&1; then
                systemctl reload nginx || nginx -s reload || log "Warning: could not reload nginx; reload it manually"
            else
                nginx -s reload || log "Warning: could not reload nginx; reload it manually"
            fi

            # grep (not -q) so it keeps reading the pipe: an early exit could
            # break the pipeline under `set -o pipefail` when nginx -t is large.
            if nginx -T 2>/dev/null | grep -E "client_max_body_size[[:space:]]+${body_size}" >/dev/null; then
                log "nginx reloaded; the effective configuration uses client_max_body_size ${body_size}"
            else
                log "Warning: nginx reloaded, but client_max_body_size ${body_size} is not in the effective configuration - the vhost may not be enabled in sites-enabled"
            fi
        else
            cat "$backup" > "$site_conf"
            log "Warning: nginx rejected the edited vhost; the original file was restored - set 'client_max_body_size ${body_size};' manually"
        fi

        rm -f "$backup"
    fi

    # ---- PHP ----
    # Version serving the vhost (fastcgi_pass unix:/var/run/php/php8.4-fpm.sock).
    if [ -n "$site_conf" ]; then
        socket="$(grep -oE 'php[0-9]+\.[0-9]+-fpm' "$site_conf" | head -n 1 || true)"
        php_version="${socket#php}"
        php_version="${php_version%-fpm}"
    fi

    if [ -z "$php_version" ]; then
        php_version="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)"
    fi

    log "Setting upload_max_filesize=${upload_max} and post_max_size=${post_max} for PHP ${php_version:-unknown}"

    for dir in "/etc/php/${php_version}/fpm/conf.d" "/etc/php/${php_version}/cli/conf.d" /etc/php.d /etc/php/conf.d; do
        [ -d "$dir" ] || continue

        target="$dir/99-documentation-center-uploads.ini"
        printf 'upload_max_filesize = %s\npost_max_size = %s\n' "$upload_max" "$post_max" > "$target"
        log "Upload limits written to $target"
        ini_written=$((ini_written + 1))
    done

    if [ "$ini_written" -eq 0 ]; then
        log "Warning: no PHP ini directory found; set upload_max_filesize=${upload_max} and post_max_size=${post_max} in php.ini and reload php-fpm"
        return 0
    fi

    # php_admin_value in the pool wins over any ini file, so a value set there
    # would silently keep the old limit.
    if [ -n "$php_version" ] && grep -rqE 'php_admin_value\[upload_max_filesize\]' "/etc/php/${php_version}/fpm/pool.d" 2>/dev/null; then
        log "Warning: the PHP-FPM pool sets upload_max_filesize via php_admin_value, which overrides the ini file - remove it from the pool config"
    fi

    if command -v systemctl >/dev/null 2>&1; then
        if [ -n "$php_version" ] && systemctl reload "php${php_version}-fpm" >/dev/null 2>&1; then
            log "Reloaded php${php_version}-fpm"
        elif systemctl reload php-fpm >/dev/null 2>&1; then
            log "Reloaded php-fpm"
        else
            log "Warning: could not reload php-fpm; restart it to apply the upload limits"
        fi
    else
        log "systemctl not found; restart php-fpm to apply the upload limits"
    fi

    log "PHP reports upload_max_filesize=$(php -r 'echo ini_get("upload_max_filesize");' 2>/dev/null || true) post_max_size=$(php -r 'echo ini_get("post_max_size");' 2>/dev/null || true) (slow uploads may also need max_input_time/max_execution_time raised)"
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

env_file="$APP_DIR/.env"

if grep -qE '^APP_DEBUG=true' <(tr -d '\r' < "$env_file"); then
    log "Warning: APP_DEBUG is true in $env_file - set it to false in production"
fi

log "Checking the database connection in $env_file"

# Laravel only honours the first DB_CONNECTION definition (phpdotenv never
# overwrites an already-set variable), so validate that one and flag extras.
db_entries="$(grep -nE '^[[:space:]]*DB_CONNECTION[[:space:]]*=' "$env_file" | tr -d '\r' || true)"

if [ -z "$db_entries" ]; then
    log "Note: DB_CONNECTION is not set in $env_file; the default connection will be used"
else
    db_entry_count="$(printf '%s\n' "$db_entries" | grep -c . || true)"

    if [ "$db_entry_count" -gt 1 ]; then
        log "Warning: $db_entry_count DB_CONNECTION entries in $env_file - Laravel uses the first one:"
        printf '%s\n' "$db_entries" | sed 's/^/  /'
    fi

    db_connection="$(printf '%s\n' "$db_entries" | head -n 1 | sed -E 's/^[0-9]+://; s/^[[:space:]]*DB_CONNECTION[[:space:]]*=[[:space:]]*//; s/[[:space:]]+$//' | tr -d "\"'" )"

    if [ "$db_connection" = "sqlite" ]; then
        fail "DB_CONNECTION resolves to 'sqlite' in $env_file (offending line(s): $(printf '%s' "$db_entries" | tr '\n' ' ')). Configure MySQL/MariaDB credentials before deploying"
    fi

    log "Database connection: $db_connection"
fi

# Laravel reaches MySQL/MariaDB through PDO; verify the driver now so a missing
# extension fails fast with an actionable message instead of an obscure
# "could not find driver" while the app is already in maintenance mode.
case "${db_connection:-mysql}" in
    mysql|mariadb)
        if ! php -r 'exit(in_array("mysql", PDO::getAvailableDrivers(), true) ? 0 : 1);' 2>/dev/null; then
            fail "The PHP 'pdo_mysql' extension is missing for the CLI at '$(command -v php)'. A MySQL/MariaDB connection requires it (install 'php-mysql' or 'php8.x-mysql' and restart PHP-FPM), then redeploy"
        fi

        log "PHP PDO MySQL driver is available"
        ;;
esac

log "Installing PHP dependencies"
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

log "Clearing stale caches"
# The application cache lives in the database (CACHE_STORE=database) and its
# table only exists after the migrations below, so clear the bootstrap caches
# here and the application cache right after migrating.
php artisan optimize:clear --except=cache

log "Ensuring storage directories and writable paths"
mkdir -p storage/framework/cache/data storage/framework/views storage/framework/sessions storage/logs bootstrap/cache

if [ "$(id -u)" -eq 0 ]; then
    if RESOLVED_WEB_USER="$(resolve_web_user)"; then
        log "Granting ownership of writable paths to '$RESOLVED_WEB_USER'"
        chown -R "$RESOLVED_WEB_USER":"$RESOLVED_WEB_USER" storage bootstrap/cache
        chmod -R ug+rwX storage bootstrap/cache
    else
        fail "Could not determine the web user; set the WEB_USER secret to the user that runs PHP-FPM"
    fi
else
    log "Warning: running as non-root; cannot change ownership. Ensure '$(id -un)' owns storage and bootstrap/cache."
    chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
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

log "Clearing the application cache"
php artisan cache:clear

log "Configuring PHP and nginx upload limits"
configure_upload_limits

log "Configuring the queue worker"
configure_queue_worker

log "Deploy completed"
