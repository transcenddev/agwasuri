# Development Seeding & Real-time Simulation

This project includes tools to generate sample water-quality data for local development and to simulate realtime events for the frontend.

Files:
- `scripts/simulate-realtime.php` — quick PHP script to simulate realtime inserts and broadcast `WaterQualityCreated` events.
- `app/Console/Commands/SampleWaterQuality.php` — artisan command `sample:water-quality` (preferred). Registered in `app/Console/Kernel.php`.

Command usage examples:

- Finite generation (3 samples, 1s apart) for admin and test users:
```powershell
php artisan sample:water-quality --users=admin@admin.com,test@test.com --count=3 --interval=1 --yes
```

- Continuous simulation until stopped (5s interval) without broadcasting:
```powershell
php artisan sample:water-quality --users=admin@admin.com --loop --interval=5 --no-broadcast --yes
```

Notes & safety
- The artisan command is restricted to local environment by default. Use `--yes` to override.
- `--no-broadcast` will insert rows but not broadcast events (useful for bulk seeding without front-end noise).
- Keep these tools local. Do NOT run against production databases.

Developer credentials (local/dev seeders)
- `admin@admin.com` / `admin1234`
