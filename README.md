# Agwasuri

Water quality monitoring system for fishponds using IoT sensors, real-time broadcasting, and AI-driven fish recommendations.

## Table of Contents

-   [Background](#background)
-   [Features](#features)
-   [Requirements](#requirements)
-   [Install](#install)
-   [Usage](#usage)
-   [Dev-start helper](#dev-start-helper)
-   [API / Architecture](#api--architecture)
-   [Testing](#testing)
-   [Contributing](#contributing)
-   [License](#license)

## Background

Agwasuri helps small-scale aquaculture operators, researchers, and municipal officers collect, visualize, and act on pond water-quality data. It combines sensor ingestion (ESP32/Arduino devices), real-time broadcasts (Laravel Reverb), and a lightweight ML service to recommend suitable fish species and reduce stock loss.

Goals:

-   Improve decision-making for pond management
-   Provide reproducible data for research and evaluation
-   Demonstrate a complete IoT → web → ML pipeline for aquaculture

## Features

-   Real-time ingestion of temperature, pH, dissolved oxygen, and salinity
-   Live charts and dashboards (Livewire + Tailwind)
-   AI-driven fish-species recommendations (external ML service)
-   Web endpoint for sensor data ingestion: `POST /store-water-quality-data`
-   Broadcasts new measurements on channel `water-quality-data-created`

## Requirements

-   PHP 8.3+
-   Composer
-   Node.js 18+
-   npm
-   SQLite (recommended for quick local setup) or MySQL/PostgreSQL for production
-   PowerShell (Windows) or bash (macOS/Linux)

## Install

Follow these steps to get a development instance running. The PowerShell examples assume you're on Windows; for macOS/Linux use the POSIX equivalents shown.

1. Clone and install dependencies

```pwsh
git clone https://github.com/lokodata/agwasuri_prod.git
cd agwasuri_prod
composer install
npm install
```

2. Copy environment and generate app key

```pwsh
copy .env.example .env
php artisan key:generate
```

On POSIX systems:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure the database in `.env` (SQLite quickstart)

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Create the SQLite file if it doesn't exist:

```pwsh
if (-not (Test-Path database\database.sqlite)) { New-Item database\database.sqlite -ItemType File }
```

4. Run migrations, seeders and install optional packages

```pwsh
php artisan migrate --seed
php artisan reverb:install
php artisan livewire-charts:install
```

5. (Optional) Create or reset the admin account

```pwsh
pwsh ./scripts/create-admin.ps1
```

## Usage

Start the full development stack (recommended):

```pwsh
composer run dev
```

This runs the web server, Reverb (real-time) and Vite concurrently. Alternatively, start pieces manually:

```pwsh
php artisan serve --host=0.0.0.0 --port=8000
php artisan reverb:start
npm run dev
```

Open the app at `http://127.0.0.1:8000`.

Sensor ingestion endpoint (sample):

-   URL: `POST http://127.0.0.1:8000/store-water-quality-data`
-   Headers: `Authorization: Bearer <API_KEY>`, `Content-Type: application/json`

Example curl request:

```bash
curl -X POST http://127.0.0.1:8000/store-water-quality-data \\
    -H "Authorization: Bearer <API_KEY>" \\
    -H "Content-Type: application/json" \\
    -d '{"user_id":1,"recorded_at":"2025-11-06 12:00:00","temperature":28.5,"ph_level":7.2,"dissolved_oxygen":6.8,"salinity":15.3}'
```

Real-time event channel: `water-quality-data-created` (event `App\\Events\\WaterQualityCreated`).

## Dev-start helper

Use the included cross-platform helper scripts to simplify starting and resetting local environments:

```bash
npm run dev-start
```

-   Default: runs non-destructive migrations/seeds and starts the dev stack.
-   Destructive reset: `npm run dev-start -- --fresh` (POSIX) or `npm run dev-start -- -Fresh` (PowerShell) — this drops and rebuilds the DB after confirmation.

See `scripts/` for platform-specific behaviour.

## API / Architecture

A brief overview of the main components and important files:

-   Routes: `routes/web.php` — registers `POST /store-water-quality-data` and site routes
-   Controller: `app/Http/Controllers/Api/WaterQualityDataController.php` — validates requests, authenticates via `Authorization: Bearer <API_KEY>` (matching `user_id` + `api_key`), creates `WaterQualityData` records and broadcasts `WaterQualityCreated` events
-   Models: `app/Models/WaterQualityData.php`, `app/Models/User.php` (primary key: `user_id`), `app/Models/ModelMetadata.php`
-   Events: `app/Events/WaterQualityCreated.php` — broadcasts on channel `water-quality-data-created` (ShouldBroadcastNow)
-   Livewire: `app/Livewire/ClassifyFish.php` — calls external ML endpoints (`/predict` and `/train_model` at `https://api.agwasuri.app`)

Event payload example:

```json
{
    "waterQualityData": {
        "recorded_at": "2025-11-06 12:00:00",
        "temperature": 28.5,
        "salinity": 15.3,
        "dissolved_oxygen": 6.8,
        "ph_level": 7.2,
        "user_id": 1
    }
}
```

See the architecture diagram in `docs/architecture.md` for a rendered Mermaid diagram and component explanations.

## Testing

Tests use Pest. Run the full test suite:

```pwsh
php artisan test
# or
./vendor/bin/pest
```

Run a single test or filter by name:

```pwsh
php artisan test --filter=WaterQualityDataTest
```

Notes:

-   Ensure `.env.testing` is configured or your testing DB is available.
-   Some feature tests that rely on real-time broadcasts may require `php artisan reverb:start`.

### UI status classes

The project centralizes UI status styling in the `status_map` config entry so views and Livewire components render consistent badges, value coloring and row borders.

-   **Location:** [config/water_quality.php](config/water_quality.php)
-   **Canonical keys:** `GOOD`, `BAD`, `HARMFUL`
-   **Each entry contains:** `badge` (badge classes), `value` (value text classes), `border` (row border classes)

Example (simplified):

```php
'status_map' => [
    'GOOD' => ['badge' => 'text-green-700 bg-green-50', 'value' => 'text-green-700', 'border' => 'border-l-4 border-green-100'],
    'BAD' => ['badge' => 'text-amber-700 bg-amber-50', ...],
    'HARMFUL' => ['badge' => 'text-red-700 bg-red-50', ...],
]
```

When updating palette or accessibility contrast, update `status_map` and sweep Livewire components to keep UI consistent.

## Contributing

Contributions are welcome. Please follow these guidelines:

-   File issues for bugs or feature requests.
-   Fork the repo and create a branch named like `feat/short-description` or `fix/short-description`.
-   Run tests and linters before opening a PR: `php artisan test`.
-   Include screenshots and reproduction steps for UI changes.
-   Use small, focused pull requests — maintainers will run CI (Pest tests, static analysis) before merging.

See `CONTRIBUTORS.md` for project credits.

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.

---

If you'd like, I can also add a short CONTRIBUTING.md or an ASCII/Mermaid file for the architecture diagram separately. Happy to patch those next.
