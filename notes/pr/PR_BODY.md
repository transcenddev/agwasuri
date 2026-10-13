PR: feat/frontend-redesign -> main

## Summary

This PR contains a frontend redesign and stability improvements for the Agwasuri project, focused on Livewire integrations, theme handling, and realtime charts.

## Key changes

-   Fix theme JavaScript parse error and expose `initTheme`/`toggleTheme` to the window.
-   Add a Livewire-aware chart initializer to avoid flicker/duplicate points on chart re-renders.
-   Implement a fullscreen mobile/tablet navigation panel and move the theme toggle into the mobile header.
-   Restore missing Volt auth routes (`/register`) and re-enable email verification routes used by tests.
-   Add compatibility support for `name` (maps to `first_name`/`last_name`) in profile and registration Volt components so existing tests and UI expectations remain valid.
-   Normalize font references and include Wulkan Display fonts in the asset build.
-   Add sample realtime event generator and scripts to simulate broadcasts locally.

## Test & build status

-   PHP unit & feature tests: all tests passing locally (26 passed).
-   Frontend build: `npm run build` completed, hashed assets written to `public/build/`. Some earlier font path warnings were resolved by updating `resources/css/app.css` to reference `resources/fonts` directly.
-   Realtime: `php artisan reverb:start` + `php artisan sample:water-quality --count=5` successfully emitted sample events to the `water-quality-data-created` broadcast channel.

## Files of interest (high level)

-   `resources/js/theme.js` — theme helpers
-   `resources/js/waterQualityInit.js` — Livewire-aware chart init
-   `resources/views/livewire/pages/auth/register.blade.php` — Volt state + register handler
-   `resources/views/livewire/profile/update-profile-information-form.blade.php` — added `name` compatibility and validation adjustments
-   `resources/css/app.css` — font-face references corrected
-   `scripts/simulate-realtime.php` and artisan command `sample:water-quality` — event generator

## Manual smoke test instructions

1. Start dev stack (PowerShell):

```powershell
pwsh .\scripts\dev-start.ps1
# or manually:
# php artisan serve --host=127.0.0.1 --port=8000
# npm run dev
# php artisan reverb:start
```

2. Open http://127.0.0.1:8000 and navigate to:

    - Dashboard pages with water-quality charts.
    - Profile page and registration page to verify forms and theme.

3. Simulate realtime events (in project root):

```powershell
$env:DB_CONNECTION='sqlite'; $env:DB_DATABASE='C:\Users\Admin\_repos\agwasuri_master\agwasuri_prod\database\database.sqlite'; php artisan sample:water-quality --count=10
```

4. Observe charts update with new points, verify no flicker/duplicates and no layout shifts.

## Notes / TODOs before merge

-   Run accessibility checks (Lighthouse/axe) on key pages and fix any critical issues.
-   Optimize fonts/images found in `resources/fonts` and `public/assets` (consider subsetting and Git LFS for very large files).
-   Add CI steps to build frontend assets and run PHP tests on PRs.
-   Capture screenshots or short recordings of chart update flow for the PR description.

## Checklist for reviewer

-   [ ] Confirm tests pass in CI (PHP + JS where applicable)
-   [ ] Review visual changes on mobile and desktop
-   [ ] Confirm realtime charts update correctly in a deployed/staging environment
-   [ ] Approve or request changes

--
Generated on 2025-12-10 by developer-run automation. Please attach screenshots under `/docs/screenshots/` (or provide links) and update changelog as needed.
