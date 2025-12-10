#!/usr/bin/env bash
set -euo pipefail

echo "--- Agwasuri: dev-start (POSIX) ---"

# Create .env if missing and generate app key
if [ ! -f .env ]; then
  echo ".env not found — copying .env.example and generating APP_KEY"
  cp .env.example .env
  php artisan key:generate
fi

# Ensure database dir and sqlite file exist (safe even when using mysql)
mkdir -p database
if [ ! -f database/database.sqlite ]; then
  touch database/database.sqlite
  echo "Created database/database.sqlite"
fi

# Clear config cache so any .env edits are picked up
php artisan config:clear || true
php artisan cache:clear || true

# By default run non-destructive migrations: php artisan migrate --seed.
# Pass --fresh to force a destructive fresh migrate (drops tables).
if [ "${1-}" = "--fresh" ]; then
  # Confirm destructive action
  read -r -p "You requested a destructive reset (migrate:fresh --seed). This will DROP ALL TABLES. Proceed? [y/N] " confirm
  case "$confirm" in
    [yY][eE][sS]|[yY])
      echo "Running: php artisan migrate:fresh --seed"
      php artisan migrate:fresh --seed
      ;;
    *)
      echo "Aborted destructive reset."
      exit 1
      ;;
  esac
else
  echo "Running: php artisan migrate --seed"
  php artisan migrate --seed
fi

echo "Starting dev stack: composer run dev"
exec composer run dev
