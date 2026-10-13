<#
.SYNOPSIS
Rebuild frontend assets on Windows with optional clean to avoid esbuild.exe EPERM errors.

.DESCRIPTION
This helper script attempts a safe rebuild of the Node frontend assets used by Vite/esbuild.
It will stop common processes that may lock the esbuild binary, optionally remove `node_modules`
and `package-lock.json`, run `npm ci` and `npm run build`, then clear Laravel view/cache files.

Usage:
  Open PowerShell as Administrator and run:
    .\scripts\rebuild-assets-windows.ps1        # quick build
    .\scripts\rebuild-assets-windows.ps1 -ForceClean  # removes node_modules and lockfile first

Notes:
- If you still see EPERM when unlinking esbuild, close editors (VS Code), reboot, or temporarily disable antivirus.
#>

[CmdletBinding()]
param(
    [switch]$ForceClean
)

function Log { param($m) Write-Host "[rebuild-assets] $m" }

try {
    Log "Stopping node/esbuild processes (if any)..."
    Get-Process -Name node,esbuild -ErrorAction SilentlyContinue | ForEach-Object {
        try { Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue; Log "Stopped PID $_.Id" } catch {}
    }

    if ($ForceClean) {
        Log "Removing node_modules and package-lock.json (this may take a while)..."
        if (Test-Path .\node_modules) {
            Remove-Item -Recurse -Force -LiteralPath .\node_modules -ErrorAction Stop
            Log "Removed node_modules"
        } else { Log "node_modules not found, skipping" }

        if (Test-Path .\package-lock.json) {
            Remove-Item -Force .\package-lock.json -ErrorAction Stop
            Log "Removed package-lock.json"
        } else { Log "package-lock.json not found, skipping" }
    }

    Log "Installing Node packages (npm ci)..."
    $rc = & npm ci --no-audit --no-fund
    if ($LASTEXITCODE -ne 0) { throw "npm ci failed with exit code $LASTEXITCODE" }

    Log "Running frontend build (npm run build)..."
    $rc = & npm run build
    if ($LASTEXITCODE -ne 0) { throw "npm run build failed with exit code $LASTEXITCODE" }

    Log "Clearing Laravel caches..."
    php artisan view:clear
    php artisan config:clear
    php artisan route:clear

    Log "Build completed successfully. Open your browser to verify the UI changes.";
} catch {
    Write-Error "Build script encountered an error: $_"
    Write-Host "If you saw EPERM errors when unlinking esbuild.exe, try these steps:"
    Write-Host "  1) Close VS Code and any terminals that might use Node/esbuild"
    Write-Host "  2) Reboot the machine or temporarily disable antivirus"
    Write-Host "  3) Re-run this script as Administrator: `powershell -ExecutionPolicy Bypass -File .\scripts\rebuild-assets-windows.ps1 -ForceClean`"
    exit 1
}
