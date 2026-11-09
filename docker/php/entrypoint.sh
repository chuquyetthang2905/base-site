#!/bin/sh
set -e
cd /var/www/html

run() { if [ "$(id -u)" = "0" ]; then su-exec app "$@"; else "$@"; fi; }

run php artisan optimize || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  run php artisan migrate --force
fi

exec "$@"
