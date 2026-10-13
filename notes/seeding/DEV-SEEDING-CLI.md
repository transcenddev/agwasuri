DEV Seeding (CLI-first)

Recommendation

-   Use the artisan command `php artisan sample:water-quality` as the primary entrypoint for generating sample water-quality data. It is discoverable (`php artisan list`), testable, integrates with the app environment, and supports safety flags like `--no-broadcast` and `--yes`.

Why this file exists

-   `scripts/simulate-realtime.php` remains in the repo as a convenience wrapper that forwards flags to the artisan command. This keeps UX simple for designers who like running a single PHP file from their editor while keeping the command the single source of truth.

Examples (PowerShell)

-   Run 10 samples for a specific user (discrete run):

```powershell
php artisan sample:water-quality --users="test@test.com" --count=10
```

-   Run 5 samples without broadcasting (safe for bulk changes):

```powershell
php artisan sample:water-quality --users="test@test.com" --count=5 --no-broadcast
```

-   Continuous loop (Ctrl+C to stop):

```powershell
php artisan sample:water-quality --users="test@test.com" --loop
```

-   Fallback script (calls the artisan command under the hood):

```powershell
php scripts/simulate-realtime.php --user=test@test.com --count=5 --no-broadcast
```

Notes

-   No site restart is required to see DB changes. For immediate UI updates you must have the broadcast server running and ensure the event `user_id` matches the logged-in user in the browser.
-   The command supports both `--user` and `--users` flags for convenience; `--n` is also accepted as an alias for `--count` in the script wrapper.
-   If you want me to run the command now and create a small set of samples, tell me which user/email and how many rows to create.
