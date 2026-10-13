# UI Changes Summary (frontend-redesign)

This document summarizes the UI/layout changes applied on the `feat/frontend-redesign` branch and provides quick steps to rebuild assets locally on Windows.

What changed

-   Standardized container and card usage across pages by introducing a shared `x-container` and `x-card` components.
-   Replaced manual page wrappers with the shared `x-container` in Dashboard, Optimal Fish, Historical Data, and Account Management.
-   Moved the navigation out of the old padded wrapper and wrapped the page slot with `x-container` so the header offset is applied site-wide.
-   Added a runtime script in the navigation component to measure header height and set the `--header-height` CSS variable; `x-container` uses this var for `padding-top`.

Files touched (high level)

-   `tailwind.config.js`
-   `resources/views/components/container.blade.php`
-   `resources/views/components/card.blade.php`
-   `resources/views/components/card-header.blade.php`
-   `resources/views/dashboard.blade.php`
-   `resources/views/optimal-fish.blade.php`
-   `resources/views/historical-data.blade.php`
-   `resources/views/account_management.blade.php`
-   `resources/views/livewire/user-table.blade.php`
-   `resources/views/layouts/app.blade.php`
-   `resources/views/livewire/layout/navigation.blade.php`

How to rebuild assets on Windows

1. Option A (quick): run the included helper script (recommended):

```powershell
# Quick build (run in repository root)
.\scripts\rebuild-assets-windows.ps1

# If you still hit permission errors, run with ForceClean (run PowerShell as Administrator):
.\scripts\rebuild-assets-windows.ps1 -ForceClean
```

2. Manual steps (equivalent):

```powershell
# Stop node/esbuild processes if needed
Get-Process -Name node,esbuild -ErrorAction SilentlyContinue | ForEach-Object { Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue }

# (Optional) Remove node_modules and package-lock.json
Remove-Item -Recurse -Force .\node_modules
Remove-Item -Force .\package-lock.json

# Install and build
npm ci --no-audit --no-fund
npm run build

# Clear Laravel caches
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

Troubleshooting

-   If you see `EPERM` unlinking `esbuild.exe`: close VS Code and terminals, reboot, or temporarily disable antivirus. Then re-run the script with `-ForceClean` as Administrator.

Questions or next steps

-   I can extract the inline header-height script into `resources/js/header-height.js` and register it with Vite if you want it built with the rest of the JS assets.
-   I can convert remaining Livewire partials to use `x-card` / `x-container` for consistency.
