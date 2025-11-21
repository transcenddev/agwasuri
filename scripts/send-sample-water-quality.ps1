<#
scripts/send-sample-water-quality.ps1
PowerShell helper to post sample water quality payloads from `scripts/sample-water-quality.json`.
#>
param(
    [string]$ApiKey = $env:SENSOR_API_KEY,
    [string]$Server = "http://127.0.0.1:8000",
    [string]$File = "$PSScriptRoot/sample-water-quality.json"
)

if (-not (Test-Path $File)) {
    Write-Error "Sample file not found: $File"
    exit 1
}

if (-not $ApiKey) {
    $ApiKey = Read-Host -Prompt "Enter API key (will not be saved)"
}

$payloads = Get-Content $File -Raw | ConvertFrom-Json

foreach ($p in $payloads) {
    $body = $p | ConvertTo-Json -Depth 5
    Write-Output "Posting: $($p.recorded_at) user:$($p.user_id)"
    try {
        $resp = Invoke-RestMethod -Method Post -Uri "$Server/store-water-quality-data" -Headers @{ Authorization = "Bearer $ApiKey"; "Content-Type" = "application/json" } -Body $body -ErrorAction Stop
        Write-Output ("Response: " + ($resp | ConvertTo-Json -Depth 3))
    } catch {
        Write-Warning "Request failed: $($_.Exception.Message)"
    }
    Start-Sleep -Milliseconds 300
}
