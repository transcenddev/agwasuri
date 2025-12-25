## Purpose

This file gives AI coding agents the minimal, high-value context to be productive in this Laravel + Livewire project.
Focus on concrete, discoverable patterns, commands and file locations — not generic advice.

```
# Repo metadata
language: php
framework: laravel-11
frontend: livewire-3.4 + volt
styling: tailwindcss-3.1 (with custom cvsu color palette)
testing: pest-3.7
broadcasting: laravel-reverb-1.0
charting: asantibanez/livewire-charts-4.1 + chart.js-4.4 + apexcharts-5.3
dev-start: npm run dev-start
tests: php artisan test
last-updated: 2025-12-16
```

## Quick Commands

Copy-paste commands for common tasks:

```bash
# Install dependencies
composer install && npm install

# Safe start (creates sqlite and seeds non-destructively)
npm run dev-start

# Destructive fresh reset (prompts for confirmation)
npm run dev-start -- --fresh
# or shorthand
npm run dev-reset

# Create or reset admin account (idempotent)
pwsh ./scripts/create-admin.ps1

# Run tests
php artisan test
```

## Quick Architecture Summary

-   **Framework**: Laravel 11 monolith with Livewire 3.4 components (+ Volt for auth pages) driving the UI
-   **Real-time**: Laravel Reverb WebSocket server broadcasts water quality updates via `App\Events\WaterQualityCreated` on channel `water-quality-data-created`
-   **Frontend**: Vite bundler, Tailwind CSS 3.1 with custom CVSU color palette, Chart.js + ApexCharts for visualizations
-   **Authentication**: Laravel Breeze (Livewire/Volt variant) with custom middleware `IsAdmin` for admin routes
-   **Database**: SQLite (default for dev), migrations support MySQL/PostgreSQL
-   **API ingestion**: Single public route `POST /store-water-quality-data` (CSRF-exempt, Bearer token auth) handled by `App\Http\Controllers\Api\WaterQualityDataController`
-   **ML integration**: External Python API at `https://api.agwasuri.app` for fish classification predictions and model training (called from `app/Livewire/ClassifyFish.php`)
-   **Theme system**: Dark/light mode toggle with localStorage persistence and server-side cookie normalization via `NormalizeThemeCookie` middleware

## Core Data Flow

1. **Sensor → API**: ESP32/Arduino POST to `/store-water-quality-data` with Bearer auth
2. **Controller → Model**: `WaterQualityDataController::store()` validates, creates `WaterQualityData` record
3. **Event → Broadcast**: `WaterQualityCreated` event fires, Reverb broadcasts to subscribed clients
4. **Frontend → Updates**: Livewire components (`WaterQualityChart`, `WaterQualityCard`) listen and re-render in real-time
5. **ML workflow**: `ClassifyFish` component computes averages → POSTs to ML API → displays recommendations

## Key Files (start here)

### Routes & Controllers

-   **routes/web.php** — main route definitions: views, API endpoints, theme setter, logout
-   **routes/auth.php** — Volt-based auth routes (login, register, password reset, email verification)
-   **routes/channels.php** — Broadcast authorization for private channels (admin, user-specific)
-   **app/Http/Controllers/Api/WaterQualityDataController.php** — sensor data ingestion (`store()`) and latest data endpoint (`latest()`)
-   **app/Http/Controllers/Api/RecommendationController.php** — cached fish recommendation endpoint for dashboard widgets
-   **app/Http/Controllers/ThemeController.php** — server-side theme cookie setter

### Models & Database

-   **app/Models/User.php** — primary key is `user_id` (NOT `id`), includes virtual `name` accessor combining `first_name` + `last_name`, `api_key` field, `isAdmin()` helper, backward-compatible `id` attribute getter
-   **app/Models/WaterQualityData.php** — fillable: `recorded_at`, `temperature`, `salinity`, `dissolved_oxygen`, `ph_level`, `user_id`
-   **app/Models/ModelMetadata.php** — tracks ML model training timestamps (table: `model_metadata`, columns: `model_name`, `last_trained_at`)
-   **database/migrations/0001_01_01_000000_create_users_table.php** — users schema with aquaculture-specific fields (fishpond_name, barangay, species_cultured, etc.)
-   **database/migrations/2024_10_30_080654_create_water_quality_data_table.php** — water quality data schema with foreign key to users.user_id
-   **database/seeders/** — `DatabaseSeeder`, `BulkUserSeeder`, `WaterQualityDataSeeder` (idempotent, use updateOrCreate)

### Livewire Components

-   **app/Livewire/ClassifyFish.php** — ML prediction UI: computes averages, calls external API, handles training, displays results (500 lines)
-   **app/Livewire/WaterQualityChart.php** — real-time chart component using Chart.js, listens to Reverb broadcasts
-   **app/Livewire/WaterQualityDataTable.php** — tabulated data display with user/date filters
-   **app/Livewire/WaterQualityCard.php** — latest measurement card widget
-   **app/Livewire/UserFilter.php** — admin user selection dropdown
-   **app/Livewire/DateFilter.php** — date range picker component
-   **app/Livewire/Forms/** — form objects for validation and reusability
-   **app/Livewire/Actions/Logout.php** — logout action component

### Events & Broadcasting

-   **app/Events/WaterQualityCreated.php** — broadcasts new water quality records on channel `water-quality-data-created` (implements `ShouldBroadcastNow`)

### Frontend Assets

-   **resources/js/app.js** — main JS entry: imports theme system, waterQualityInit, exposes global helpers
-   **resources/js/bootstrap.js** — axios + Echo initialization
-   **resources/js/echo.js** — Reverb configuration using Pusher protocol
-   **resources/js/theme.js** — dark/light mode toggle logic, localStorage persistence
-   **resources/js/waterQualityInit.js** — Livewire-aware chart initialization
-   **resources/css/app.css** — Tailwind directives + custom CVSU theme variables
-   **tailwind.config.js** — custom color palette (`cvsu.*`), dark mode class strategy, content paths

### Configuration & Helpers

-   **config/water_quality.php** — classification ranges for pH, temperature, salinity, DO (used by helper functions for status badges)
-   **app/Support/helpers.php** — global helper functions including `classifyWaterParam()` (206 lines)
-   **app/Http/Middleware/IsAdmin.php** — admin route guard
-   **app/Http/Middleware/NormalizeThemeCookie.php** — ensures theme cookie consistency
-   **app/Observers/UserObserver.php** — user model lifecycle hooks
-   **app/Services/** — `WaterQualityChartService.php`, `SampleWaterQualityService.php`

### Scripts & Automation

-   **scripts/dev-start.js** — cross-platform launcher for dev-start.ps1 or dev-start.sh
-   **scripts/dev-start.ps1** — PowerShell: creates/seeds SQLite, runs migrations, starts dev servers
-   **scripts/create-admin.ps1** — idempotent admin account creation
-   **scripts/send-sample-water-data.ps1** — test sensor data POST script
-   **scripts/generate-water-quality-data.ps1** — bulk data generation for testing
-   **package.json scripts** — `dev`, `dev-start`, `dev-reset`, `build`, `build:theme`
-   **composer.json scripts** — `dev` (concurrently runs server + reverb + vite)

## Important Conventions & Gotchas

### User Model & Authentication

-   **User primary key**: `User::$primaryKey = 'user_id'` (NOT `id`). Always use `user_id` in foreign keys and queries.
-   **Virtual name attribute**: User model has no `name` column; it's computed from `first_name + last_name` via accessor.
-   **API authentication**: Sensor API expects `Authorization: Bearer <API_KEY>` header + matching `user_id` in request body. Controller validates both in `WaterQualityDataController::store()`.
-   **Backward compatibility**: User model provides `getIdAttribute()` getter so `$user->id` works for libraries expecting `id`.

### Data Ingestion & Broadcasting

-   **Temperature filter**: `WaterQualityDataController::store()` contains guard `if ($request->temperature != 85.0000)` — legacy debugging code, review before removing.
-   **CSRF exemption**: `/store-water-quality-data` route is CSRF-exempt (see `bootstrap/app.php` middleware config).
-   **Real-time updates**: `WaterQualityCreated` event broadcasts immediately (`ShouldBroadcastNow`). Livewire components listen via Echo/Reverb.
-   **Latest endpoint**: `GET /api/water-quality/latest?user={userId}&limit={limit}` returns time-series data for charting (cached, reversed to oldest→newest).

### Frontend & Theme System

-   **Dark mode**: Uses Tailwind `class` strategy. Toggle stored in `localStorage` (`agwasuri_theme`), synced to server cookie via `NormalizeThemeCookie` middleware.
-   **Theme persistence**: `resources/js/theme.js` + `resources/js/app.js` ensure state survives Livewire partial updates via `message.processed` hook.
-   **CVSU colors**: Custom Tailwind palette in `tailwind.config.js` uses CSS variables (`--cvsu-*`) with fallbacks for runtime flexibility.
-   **Chart libraries**: Project uses BOTH Chart.js 4.4 (via Livewire Charts wrapper) AND ApexCharts 5.3. Check component before adding chart code.

### Database & Migrations

-   **SQLite default**: Development uses SQLite (`database/database.sqlite`). Production supports MySQL/PostgreSQL.
-   **Idempotent seeders**: Use `updateOrCreate` to avoid duplicate key errors. See `DatabaseSeeder`, `BulkUserSeeder`.
-   **Foreign keys**: Water quality data foreign key references `users(user_id)` with `onDelete('cascade')`.

### ML Integration

-   **External API**: ML service at `https://api.agwasuri.app` (NOT hosted in this repo). Endpoints: `/predict`, `/train_model`.
-   **Request timeout**: ClassifyFish uses 30s timeout for training requests (see `Http::timeout(30)`).
-   **Model metadata**: After training, timestamp saved to `model_metadata` table (`model_name='fish_classifier'`, `last_trained_at`).
-   **Admin-only training**: Only users with `isAdmin()` can trigger model training via UI.

## Dev / Run / Test Workflows

Install and bootstrap (PowerShell / Bash compatible):

```
composer install
npm install
copy .env.example .env   # on PowerShell use Copy-Item if preferred
php artisan key:generate
php artisan migrate
php artisan reverb:install
php artisan livewire-charts:install
```

Run the development stack (the project provides a composer script that starts server, reverb and vite concurrently):

```
composer run dev
```

If you prefer manual starts:

```
php artisan serve --host=0.0.0.0 --port=8000
php artisan reverb:start
npm run dev
```

Tests:

-   Unit/feature tests use Pest. Run with `php artisan test` or `./vendor/bin/pest`.

Logs and debugging:

-   Laravel logs: `storage/logs/laravel.log` (use `Log::info` already sprinkled across Livewire components for diagnostics).
-   Broadcast debugging: ensure `php artisan reverb:start` is running when testing real-time behavior.

## API examples (copy/paste)

POST sensor data (controller expects `user_id` and Authorization header):

Headers:

```
Authorization: Bearer <USER_API_KEY>
Content-Type: application/json
```

Body (example):

```json
{
    "user_id": 1,
    "recorded_at": "2025-11-06 12:00:00",
    "temperature": 28.5,
    "ph_level": 7.2,
    "dissolved_oxygen": 6.8,
    "salinity": 15.3
}
```

ML endpoints called from UI (see `ClassifyFish`):

-   Predict: `POST https://api.agwasuri.app/predict` — payload keys: `optimal_temperature_C`, `optimal_dissolved_oxygen_mgL`, `optimal_salinity_ppt`, `optimal_ph_value`. Response expected keys: `predictions`, `model_accuracy_percentage`, etc.
-   Train: `POST https://api.agwasuri.app/train_model` — ClassifyFish expects a `timestamp` in response it will persist to `model_metadata`.

## When Editing / Refactoring

### Schema Changes

-   If you change the water-quality DB schema, update ALL of:
    1. `WaterQualityData::$fillable` array
    2. Migration: `database/migrations/2024_10_30_080654_create_water_quality_data_table.php`
    3. Seeders: `database/seeders/WaterQualityDataSeeder.php`
    4. ML averaging logic in `ClassifyFish::predictFishType()`
    5. Chart data serialization in `WaterQualityDataController::latest()`

### Broadcasting Changes

-   If you rename the broadcast channel or event properties:
    1. Update `app/Events/WaterQualityCreated.php` (`broadcastOn()`, `broadcastWith()`)
    2. Update Livewire listeners (check `WaterQualityChart`, `WaterQualityCard`)
    3. Update `routes/channels.php` if using private channels
    4. Test with `php artisan reverb:start` running

### User Model Changes

-   Respect `User::$primaryKey = 'user_id'` in ALL queries and relationships
-   If adding user fields, update:
    1. Migration: `database/migrations/0001_01_01_000000_create_users_table.php`
    2. Model fillable: `app/Models/User.php`
    3. Seeders: `database/seeders/BulkUserSeeder.php`
    4. Registration form if relevant: `resources/views/livewire/pages/auth/register.blade.php`

### Frontend & Styling

-   When adding Tailwind classes, ensure they're in `tailwind.config.js` content paths
-   For CVSU brand colors, use existing `cvsu-*` utilities (don't hardcode hex values)
-   Theme-aware components must handle both light/dark modes (test toggle)
-   After JS changes, restart Vite (`npm run dev`) to see updates

### Testing Changes

-   When adding features, write Pest tests in `tests/Feature/` or `tests/Unit/`
-   Test broadcasting events with `Event::fake()` and `Event::assertDispatched()`
-   For API tests, use `actingAs()` with real User models (remember `user_id` primary key)
-   Run full suite before committing: `php artisan test`

## Useful quick references

-   Route for sensor data: `routes/web.php` line with `Route::post('store-water-quality-data', ...)`
-   Controller: `app/Http/Controllers/Api/WaterQualityDataController.php`
-   Livewire ML client: `app/Livewire/ClassifyFish.php`
-   Model metadata: `app/Models/ModelMetadata.php` and table `model_metadata`

If anything above is unclear, tell me which files or behaviors you want expanded and I will iterate.

## Known Gotchas

-   User primary key is `user_id` (not `id`). Do not assume `id` in queries or auth flows.
-   Sensor API requires `Authorization: Bearer <API_KEY>` and matches `user_id + api_key`.
-   `WaterQualityDataController::store` contains a suspicious guard `if ($request->temperature != 85.0000) { ... }` — verify intent before removing.
-   Seeders were adjusted to be idempotent; prefer `updateOrCreate` for helper scripts to avoid duplicate-key errors.

## How Copilot Should Use This File

-   Treat this file as runbook and domain context: prefer repository conventions and existing code structure.
-   When modifying schema, update `$fillable`, migrations, and any averaging/aggregation logic in `ClassifyFish`.
-   For sensor ingestion tasks, validate against the `POST /store-water-quality-data` example in the API examples section.
-   Do not invent production credentials or endpoints. If missing values are required, ask for them.

## Example prompts for Copilot

-   "Add validation for `salinity` in `WaterQualityDataController::store` to enforce 0-50 ppt and return 422 on invalid input."
-   "Write a Pest feature test that posts sample sensor data to `store-water-quality-data` and asserts `WaterQualityCreated` was broadcast."
-   "Refactor `app/Livewire/ClassifyFish.php` to extract averaging logic into a service class and add unit tests for it."

---

Last updated: 2025-12-16

See design guidance and UX documentation in `docs/WEB_DESIGN.md` for UI/UX, sitemap, visual tokens, and handoff checklists.

## Project Overview

-   Water quality monitoring for fishponds using IoT sensors and AI-driven fish recommendations.
-   Real-time data flows from ESP32/Arduino devices via a REST API to the backend; data is visualized in dashboards.
-   Solar-powered hardware with offline microSD backup and cloud synchronization.
-   Machine learning: lightweight decision tree model used to recommend fish species based on pH, dissolved oxygen, salinity, and temperature.
-   Target users: fishpond operators, Municipal Agriculture Office, and aquaculture stakeholders.
-   Current status: thesis proposal with working prototype; next steps include field testing and expanded parameter integration.

## Overview & Significance

Agwasuri addresses critical challenges in aquaculture water quality monitoring that impact food security and economic stability. This undergraduate thesis project for BS Computer Science at Cavite State University-Naic develops a solar-powered IoT system combined with predictive analytics to optimize fish species selection, enhancing sustainability and productivity in local fishponds.

### Purpose and Significance

The project aims to reduce fish mortality and financial losses by providing fishpond operators in San Juan I, Ternate, Cavite with data-driven insights for water quality management. By aligning with UN Sustainable Development Goals (SDG 14: Life Below Water, SDG 9: Industry, Innovation and Infrastructure, SDG 17: Partnerships), Agwasuri promotes sustainable aquaculture practices and technological innovation.

### Core Team

-   Reymar V. Mirante: Web Design & Development, UI/UX Design, 3D Modeling, IoT Prototype Casing
-   Jazrel P. Mendoza: IoT Prototype Casing, Survey and Interviews, Data Collection, Statistical Analysis
-   John Mark A. Pachico: IoT Integration and Calibration, Artificial Intelligence, Data Analytics, Web Development

### Problem Statement

Fishpond operators currently lack reliable, data-driven tools for monitoring water quality. They rely on trial-and-error methods for species selection, leading to poor water management, high fish mortality, and economic setbacks.

### System Features

-   Real-time monitoring of pH, dissolved oxygen, salinity, and temperature
-   Predictive analytics via decision tree algorithm recommending fish species based on real-time data
-   Solar-powered system with offline microSD data backup and cloud synchronization
-   User-friendly web and mobile UI complemented by local LED display

### Hardware and Software Stack

-   ESP32 microcontroller and multiple water sensors
-   Solar panel with battery backup for sustainability
-   Software frontend with React and Tailwind CSS; backend with Laravel and Python
-   Machine learning done with a MicroPython decision tree algorithm
-   Cloud connectivity and offline backup for reliability

### Target Users and Impact

-   Fishpond operators: Benefit from precise water quality data and fish species recommendations to improve survival and yield.
-   Municipal Agriculture Office: Enhanced monitoring tools for better local aquaculture management.
-   Aquaculture industry: Supports food security through sustainable, technology-aided farming methods.

### Research Methodology

-   Iterative prototyping model with continuous feedback from fishpond operators to refine functionality
-   Evaluation under ISO/IEC 25010 quality standards for functionality, usability, and reliability
-   Stratified random sampling of 86 participants including operators, students, IT experts, and government officers
-   Alpha and beta testing conducted in real fishpond environments to validate system performance

### Expected Benefits

-   Reduction in fish mortality and financial losses due to better water management
-   Improvements in productivity and aquaculture sustainability
-   Data-driven, predictive decision-making replacing trial-and-error methods
-   Adoption of green technologies through solar power integration

## Current Status and Next Steps

Agwasuri has completed the thesis evaluation (software and hardware) and finished hardware testing in fishponds at Ternate. The prototype has been validated in real-world conditions and produced field data supporting the system's reliability and utility.

Next steps:

-   Finalize analysis of field-test data and incorporate findings into the thesis write-up and documentation.
-   Tune and retrain the ML model using collected field data; record `last_trained_at` in `model_metadata` after retraining.
-   Add incremental improvements: additional sensor parameters, robustness fixes, and low-power optimization for hardware.
-   Prepare a pilot rollout plan: user training materials, deployment checklist, and maintenance schedule for partner fishponds.
-   Publish results (thesis and technical report), and explore partnerships/funding for wider pilot and commercialization.

This expanded context enriches Copilot's understanding and better supports generating relevant code, documentation, or guidance related to your thesis. Use it as background context only and do not expose private data.
