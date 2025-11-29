<#
.SYNOPSIS
Send batches of sample water-quality POSTs to the application's ingestion endpoint.

.DESCRIPTION
This script posts multiple simulated water-quality readings for one or more
users to the API endpoint. It's meant for development/demo use so you can
populate the UI and observe real-time broadcasts.

.EXAMPLE
.\send-sample-water-data.ps1 -ApiKey 'jYuc...' -UserIds 2,22 -CountPerUser 50

#>

[CmdletBinding()]
param(
    [string]$BaseUrl = 'http://127.0.0.1:8000',
    [string]$ApiPath = '/store-water-quality-data',
    [string]$ApiKey = 'jYucjmIbrhs4YWo6R5zFrV1yIKE9qwV7',
    [int[]]$UserIds = @(2),
    [int]$CountPerUser = 10,
    [int]$DelayMs = 200,
    [string]$StartDate = (Get-Date).ToString('yyyy-MM-ddTHH:mm:ss')
)

function New-RandomReading {
    param($base)
    return [math]::Round(($base + (Get-Random -Minimum -0.8 -Maximum 0.8)), 2)
}

$uri = "$BaseUrl$ApiPath"
Write-Host "Posting to $uri" -ForegroundColor Cyan

foreach ($userId in $UserIds) {
    Write-Host "Generating $CountPerUser samples for user_id=$userId" -ForegroundColor Green

    for ($i = 0; $i -lt $CountPerUser; $i++) {
        # Create a plausible timestamp spaced backwards from StartDate
        $timestamp = ([datetime]::Parse($StartDate)).AddMinutes(-1 * $i)

        # Simulate values (tweak bases if you want different ponds)
        $temp = New-RandomReading -base 26.0
        $salinity = New-RandomReading -base 15.0
        $do = New-RandomReading -base 6.5
        $ph = New-RandomReading -base 7.2

        $body = @{
            user_id = $userId
            recorded_at = $timestamp.ToString('yyyy-MM-dd HH:mm:ss')
            temperature = $temp
            salinity = $salinity
            dissolved_oxygen = $do
            ph_level = $ph
        } | ConvertTo-Json -Depth 4

        try {
            # Use curl.exe to avoid PS alias differences, fall back to Invoke-RestMethod
            if (Get-Command curl.exe -ErrorAction SilentlyContinue) {
                $response = & curl.exe -s -w "\n%{http_code}" -X POST $uri -H "Content-Type: application/json" -H "Authorization: Bearer $ApiKey" -d $body
                if ($response) {
                    # curl printed body and then status code on last line
                    $lines = $response -split "\n"
                    $status = $lines[-1]
                    $bodyText = ($lines[0..($lines.Length - 2)] -join "`n")
                    Write-Host "[$timestamp] user:$userId -> HTTP $status" -ForegroundColor Yellow
                    Write-Host $bodyText -ForegroundColor Gray
                }
                else {
                    Write-Warning "Empty response from curl for user $userId at $timestamp"
                }
            }
            else {
                $resp = Invoke-RestMethod -Method Post -Uri $uri -Headers @{ Authorization = "Bearer $ApiKey" } -Body $body -ContentType 'application/json'
                # Invoke-RestMethod returns parsed object; print JSON
                $json = $resp | ConvertTo-Json -Depth 5
                Write-Host "[$timestamp] user:$userId -> response:" -ForegroundColor Yellow
                Write-Host $json -ForegroundColor Gray
            }
        } catch {
            Write-Warning "Failed to post sample for user $userId at $timestamp: $_"
        }

        Start-Sleep -Milliseconds $DelayMs
    }
}

Write-Host 'Done sending samples.' -ForegroundColor Cyan
