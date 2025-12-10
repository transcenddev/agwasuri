<#
  Agwasuri dev-start (PowerShell)
  - Creates `.env` from `.env.example` if missing and generates APP_KEY
  - Ensures `database/database.sqlite` exists
  - Clears config cache
  - Runs `php artisan migrate:fresh --seed` by default (use -NoFresh to skip fresh)
  - Starts the dev stack via `composer run dev`
#>

param(
    [switch]$Fresh
)

Write-Host '--- Agwasuri: dev-start (PowerShell) ---'

if (!(Test-Path -Path .env)) {
    Write-Host '.env not found — copying .env.example and generating APP_KEY'
    Copy-Item -Path .env.example -Destination .env -Force
    php artisan key:generate
}

# Ensure database folder and sqlite file exist
if (!(Test-Path -Path database)) { New-Item -ItemType Directory -Path database | Out-Null }
if (!(Test-Path -Path database/database.sqlite)) { New-Item -ItemType File -Path database/database.sqlite | Out-Null; Write-Host 'Created database/database.sqlite' }

php artisan config:clear | Out-Null
php artisan cache:clear | Out-Null

if ($Fresh) {
    $resp = Read-Host "You requested a destructive reset (migrate:fresh --seed). This will DROP ALL TABLES. Proceed? (y/N)"
    if ($resp -match '^[Yy](es)?$') {
        Write-Host 'Running: php artisan migrate:fresh --seed'
        php artisan migrate:fresh --seed
    } else {
        Write-Host 'Aborted destructive reset.' -ForegroundColor Yellow
        exit 1
    }
} else {
    Write-Host 'Running: php artisan migrate --seed'
    php artisan migrate --seed
}

Write-Host 'Starting dev stack: composer run dev'
& composer run dev
