# Seeding / Sending Sample Water Quality Data

This document explains how to send sample water quality sensor payloads to your local Agwasuri API and includes helper scripts for Windows (PowerShell) and Unix (Bash).

## Quick curl example

Replace `<YOUR_API_KEY>` with a valid API key for the user and run the command while your dev server is running (e.g., `php artisan serve`):

```bash
curl -X POST "http://127.0.0.1:8000/store-water-quality-data" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <YOUR_API_KEY>" \
  -d '{"user_id":"2","recorded_at":"2025-03-19 12:00:00","temperature":25.5,"salinity":30.2,"dissolved_oxygen":8.5,"ph_level":7.1}'
```

## Steps to obtain an API key

-   Create or log in as an admin user in your dev environment.
-   Use the admin UI to view or issue an API key for the target `user_id`, or run the provided helper `pwsh ./scripts/create-admin.ps1` to create an admin and note its API key.
-   Copy the API key and keep it private. Do not commit API keys to source control.

## Scripts included

-   `scripts/send-sample-water-quality.ps1` — PowerShell helper that reads `scripts/sample-water-quality.json` and posts each record to the API. It reads `SENSOR_API_KEY` from the environment or prompts for it.
-   `scripts/send-sample-water-quality.sh` — Bash helper for Unix/WSL with the same behavior (requires `jq`).
-   `scripts/sample-water-quality.json` — Example JSON array of sample payloads you can post.

## PowerShell usage

Set your API key in the current session:

```powershell
$env:SENSOR_API_KEY = "<YOUR_API_KEY>"
.\scripts\send-sample-water-quality.ps1
```

Or run and enter the key when prompted:

```powershell
.\scripts\send-sample-water-quality.ps1
```

## Bash usage (Linux / macOS / WSL)

Make the script executable and run it (you can set the `SENSOR_API_KEY` env var):

```bash
chmod +x scripts/send-sample-water-quality.sh
SENSOR_API_KEY=<YOUR_API_KEY> ./scripts/send-sample-water-quality.sh
```

## Seeder alternative (direct DB insert)

If you control the database for your dev environment and don't need to test controller auth or broadcasting, consider using a seeder that inserts records directly into the `water_quality_data` table (`database/seeders/WaterQualitySampleSeeder.php`). This is faster and avoids needing an API key.

## Security notes

-   Never commit real API keys to the repository. Use environment variables or secrets in CI.
-   Use HTTPS and PATs for remote/staging environments, and limit what keys can do.

## Troubleshooting

-   Ensure `php artisan serve` is running and the app is reachable at `http://127.0.0.1:8000` (or change the server URL in scripts).
-   If the POST returns 401, verify the `Authorization` header is `Bearer <API_KEY>`, and that the API key belongs to the `user_id` you are posting for.
-   If using broadcasts (Reverb), start `php artisan reverb:start` to see real-time events in the UI.

## PR / Collaboration

If you want I can also add a Laravel Artisan command to post samples from the app (useful for CI/dev). Open a PR or ask me to add it and register the command in `app/Console/Kernel.php`.
