## Purpose

This file gives AI coding agents the minimal, high-value context to be productive in this Laravel + Livewire project.
Focus on concrete, discoverable patterns, commands and file locations — not generic advice.

```
# Repo metadata
language: php
framework: laravel-11
frontend: livewire
dev-start: npm run dev-start
tests: php artisan test
last-updated: 2025-12-14
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

-   App is a Laravel 11 monolith with Livewire components driving the UI (see `app/Livewire`).
-   Real-time flows use `laravel/reverb` and an event `App\Events\WaterQualityCreated` (channel: `water-quality-data-created`).
-   Sensor data arrives at a single public route: `POST /store-water-quality-data` (defined in `routes/web.php`) and is processed by `App\Http\Controllers\Api\WaterQualityDataController`.
-   ML inference & training are performed by an external service; the UI Livewire component calls it at `https://api.agwasuri.app` (see `app/Livewire/ClassifyFish.php`).

## Key Files (start here)

-   `routes/web.php` — registers site views and `POST store-water-quality-data` route.
-   `app/Http/Controllers/Api/WaterQualityDataController.php` — request validation, Bearer API key extraction, user lookup (`user_id` + `api_key`), create `WaterQualityData`, broadcasts `WaterQualityCreated`.
-   `app/Models/WaterQualityData.php` — model and `$fillable` columns stored (`recorded_at`, `temperature`, `salinity`, `dissolved_oxygen`, `ph_level`, `user_id`).
-   `app/Models/User.php` — primary key is `user_id` (not `id`) and stores `api_key` in `$fillable`.
-   `app/Events/WaterQualityCreated.php` — broadcasts `waterQualityData` on channel `water-quality-data-created` (uses ShouldBroadcastNow).
-   `app/Livewire/ClassifyFish.php` — client-side integration to external ML API: constants `PREDICT_ENDPOINT` and `TRAIN_ENDPOINT`, request shapes and how averages are computed before sending.
-   `app/Models/ModelMetadata.php` and table `model_metadata` — used to record `last_trained_at` timestamp returned by the train API.

## Important Conventions & Gotchas

-   User primary key: `User::$primaryKey = 'user_id'`. Do not assume `id`.
-   Sensor API auth: controller expects `Authorization: Bearer <API_KEY>` header and matches `user_id` + `api_key` (see `WaterQualityDataController::store`).
-   The controller currently contains a guard `if ($request->temperature != 85.0000) { ... }` — this appears to be a special-case filter and may be a debugging/legacy artefact. Review before changing behavior.
-   Real-time: created water quality rows are broadcast via `WaterQualityCreated` event and consumed by front-end Livewire/Reverb. If you change the event payload, update listeners and livewire code accordingly.

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

-   If you change the water-quality DB schema, update `WaterQualityData::$fillable`, the migration `database/migrations/2024_10_30_080654_create_water_quality_data_table.php`, seeders under `database/seeders/WaterQualityDataSeeder.php`, and any averaging code in `ClassifyFish::predictFishType()`.
-   If you rename the broadcast channel or event properties, update `app/Events/WaterQualityCreated.php` and ensure front-end subscriptions (Livewire/Reverb) match.
-   Respect `User::$primaryKey = 'user_id'` (authentication and API key matching depends on it).

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

Last updated: 2025-12-14

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
