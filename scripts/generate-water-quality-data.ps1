<#
.DESCRIPTION
  Generate realistic water quality sample data and POST to the server endpoint.

.SYNOPSIS
  PS> .\generate-water-quality-data.ps1 -UserId 104 -Count 500 -IntervalMs 500

.PARAMETER UserId
  numeric user id to send (default 104)

.PARAMETER Count
  number of records to send (default 100)

.PARAMETER IntervalMs
  milliseconds between requests (default 2000)

.PARAMETER Start
  Start timestamp (string) or DateTime. Defaults to now.

.PARAMETER ApiKey
  API key for the Authorization header. Defaults to the key you provided.

.PARAMETER Url
  Endpoint URL to POST to (default http://127.0.0.1:8000/store-water-quality-data)

.PARAMETER DryRun
  If set, prints payloads and curl commands instead of sending.
#>

param(
  [int]$UserId = 104,
  [int]$Count = 100,
  [int]$IntervalMs = 2000,
  [string]$Start = $(Get-Date).ToString('yyyy-MM-dd HH:mm:ss'),
  [string]$ApiKey = 'UIAQaKS1V4MWzuptW1EtDRc4C9zchyf2',
  [string]$Url = 'http://127.0.0.1:8000/store-water-quality-data',
  [int]$Retries = 3,
  [int]$RetryDelayMs = 1000,
  [int]$TimeStepMinutes = 10,
  [int]$Seed = 0,
  [switch]$SkipHostCheck,
  [switch]$Infinite,
  [switch]$DryRun
)

function Get-RandomFloat([double]$min, [double]$max, [int]$round = 2) {
    $r = (Get-Random -Minimum 0 -Maximum 10000) / 10000.0
    $val = $min + $r * ($max - $min)
    return [math]::Round($val, $round)
}

# Parse start date; allow passing a date string
try {
    [datetime]$startDt = [datetime]::Parse($Start)
} catch {
    Write-Warning "Could not parse Start ('$Start'), using now."
    $startDt = Get-Date
}

# Basic host/port connectivity check (unless skipped)
try {
  $uri = [uri]$Url
} catch {
  Write-Warning "Could not parse Url '$Url' as URI. Skipping host check."
  $uri = $null
}

  if (-not $SkipHostCheck -and $uri -ne $null) {
  $targetHost = $uri.Host
  $targetPort = $uri.Port
  $reachable = $false
  try {
    $reachable = Test-NetConnection -ComputerName $targetHost -Port $targetPort -InformationLevel Quiet
  } catch {
    # On non-Windows or if Test-NetConnection isn't available, attempt a simple TCP connect
    try {
      $sock = New-Object System.Net.Sockets.TcpClient
      $async = $sock.BeginConnect($targetHost, $targetPort, $null, $null)
      $wait = $async.AsyncWaitHandle.WaitOne(1000)
      if ($wait -and $sock.Connected) { $reachable = $true }
      $sock.Close()
    } catch {
      $reachable = $false
    }
  }

  if (-not $reachable) {
    Write-Host "Could not reach $($targetHost):$($targetPort). Ensure the Laravel dev server is running (e.g. php artisan serve --host=127.0.0.1 --port=$($targetPort)) or use -SkipHostCheck to force sending." -ForegroundColor Yellow
    exit 1
  }
}

if ($Infinite) {
  Write-Host "Sending records indefinitely for user_id=$UserId to $Url (interval ${IntervalMs}ms) starting at $($startDt.ToString('yyyy-MM-dd HH:mm:ss')) - INFINITE MODE (Ctrl+C to stop)" -ForegroundColor Cyan
} else {
  Write-Host "Sending $Count records for user_id=$UserId to $Url (interval ${IntervalMs}ms) starting at $($startDt.ToString('yyyy-MM-dd HH:mm:ss'))" -ForegroundColor Cyan
}

if ($Infinite) {
  $i = 0
  while ($true) {
  # Typical brackish pond values (realistic ranges):
  # temperature: 20.0 - 32.0 C
  # salinity: 0.5 - 35.0 ppt (brackish to saline); example used ~30
  # dissolved_oxygen: 2.5 - 12.0 mg/L
  # pH: 6.0 - 8.5

  # Introduce diurnal variation and occasional events
  $minuteOffset = $i * $TimeStepMinutes
  $timestamp = $startDt.AddMinutes($minuteOffset)

  # seed randomness for reproducible outputs when requested
  if ($Seed -ne 0) { Get-Random -SetSeed $Seed + $i | Out-Null }

  # Diurnal temperature: base + sinusoidal daily cycle
  $baseTempCenter = Get-RandomFloat -min 25.0 -max 29.0 -round 2
  $hourFraction = ($timestamp.TimeOfDay.TotalHours) / 24.0
  $diurnalAmp = Get-RandomFloat -min 0.8 -max 2.2 -round 2
  $diurnal = [math]::Round([math]::Sin($hourFraction * 2.0 * [math]::PI) * $diurnalAmp, 2)
  $tempNoise = Get-RandomFloat -min -0.6 -max 0.6 -round 2
  $temp = [math]::Round($baseTempCenter + $diurnal + $tempNoise, 2)

  # Salinity: mostly stable with slow drift, occasional rain/flush events
  $salBase = 29.0
  $salDrift = Get-RandomFloat -min -1.5 -max 1.5 -round 2
  $sal = [math]::Round($salBase + $salDrift + (Get-RandomFloat -min -0.3 -max 0.3 -round 2), 2)

  # Simulate occasional rain every ~120 records that reduces salinity and lowers pH slightly
  if (($i % 120) -eq 10) {
    $sal = [math]::Round($sal - (Get-RandomFloat -min 3.0 -max 6.0 -round 2), 2)
    $rainEvent = $true
  } else { $rainEvent = $false }

  # Dissolved oxygen: inversely correlated with temperature plus some noise
  $doBase = Get-RandomFloat -min 5.0 -max 9.0 -round 2
  $do = [math]::Round($doBase - ($diurnal * 0.3) + (Get-RandomFloat -min -0.8 -max 0.8 -round 2), 2)

  # Small chance for low-oxygen event
  if (($i % 200) -eq 150) { $do = Get-RandomFloat -min 2.2 -max 4.0 -round 2 }

  # pH: mildly buffered but affected by rain events and organic loading
  $phEffect = 0
  if ($rainEvent) { $phEffect = Get-RandomFloat -min 0.3 -max 0.6 -round 2 }
  $ph = [math]::Round(7.0 + (Get-RandomFloat -min -0.4 -max 0.6 -round 2) - $phEffect, 2)

  # Clamp values to safe ranges
  $do = [math]::Max(1.0, [math]::Min(14.0, $do))
  $temp = [math]::Max(0.0, [math]::Min(45.0, $temp))
  $sal = [math]::Max(0.0, [math]::Min(42.0, $sal))
  $ph = [math]::Max(3.0, [math]::Min(11.0, $ph))

    $payload = @{
        user_id = $UserId
        recorded_at = $timestamp.ToString('yyyy-MM-dd HH:mm:ss')
        temperature = $temp
        salinity = $sal
        dissolved_oxygen = $do
        ph_level = $ph
    }

    $json = $payload | ConvertTo-Json -Depth 3

    if ($DryRun) {
      Write-Host "[DRYRUN] POST $Url -H 'Authorization: Bearer $ApiKey' -d '$json'"
    } else {
      $headers = @{ 'Authorization' = "Bearer $ApiKey"; 'Content-Type' = 'application/json' }
      $attempt = 0
      $sent = $false
      while (-not $sent -and $attempt -lt $Retries) {
        try {
          $attempt++
          $resp = Invoke-RestMethod -Uri $Url -Method Post -Body $json -Headers $headers -ErrorAction Stop
          Write-Host "[$($i+1)/$Count] $($payload.recorded_at) -> SUCCESS: $(if ($null -ne $resp) { (ConvertTo-Json $resp -Depth 2) } else { 'no body' })" -ForegroundColor Green
          $sent = $true
        } catch {
          $err = $_.Exception.Message
          if ($attempt -lt $Retries) {
            Write-Host "[$($i+1)/$Count] attempt $attempt/$Retries failed: $err -- retrying in ${RetryDelayMs}ms..." -ForegroundColor Yellow
            Start-Sleep -Milliseconds $RetryDelayMs
          } else {
            Write-Host "[$($i+1)/$Count] attempt $attempt/$Retries failed: $err -- giving up." -ForegroundColor Red
          }
        }
      }

      Start-Sleep -Milliseconds $IntervalMs
    }
  }

  Write-Host "Done sending $Count records." -ForegroundColor Cyan

} else {
  for ($i = 0; $i -lt $Count; $i++) {
    # Typical brackish pond values (realistic ranges):
    # temperature: 20.0 - 32.0 C
    # salinity: 0.5 - 35.0 ppt (brackish to saline); example used ~30
    # dissolved_oxygen: 2.5 - 12.0 mg/L
    # pH: 6.0 - 8.5

    # Introduce diurnal variation and occasional events
    $minuteOffset = $i * $TimeStepMinutes
    $timestamp = $startDt.AddMinutes($minuteOffset)

    # seed randomness for reproducible outputs when requested
    if ($Seed -ne 0) { Get-Random -SetSeed $Seed + $i | Out-Null }

    # Diurnal temperature: base + sinusoidal daily cycle
    $baseTempCenter = Get-RandomFloat -min 25.0 -max 29.0 -round 2
    $hourFraction = ($timestamp.TimeOfDay.TotalHours) / 24.0
    $diurnalAmp = Get-RandomFloat -min 0.8 -max 2.2 -round 2
    $diurnal = [math]::Round([math]::Sin($hourFraction * 2.0 * [math]::PI) * $diurnalAmp, 2)
    $tempNoise = Get-RandomFloat -min -0.6 -max 0.6 -round 2
    $temp = [math]::Round($baseTempCenter + $diurnal + $tempNoise, 2)

    # Salinity: mostly stable with slow drift, occasional rain/flush events
    $salBase = 29.0
    $salDrift = Get-RandomFloat -min -1.5 -max 1.5 -round 2
    $sal = [math]::Round($salBase + $salDrift + (Get-RandomFloat -min -0.3 -max 0.3 -round 2), 2)

    # Simulate occasional rain every ~120 records that reduces salinity and lowers pH slightly
    if (($i % 120) -eq 10) {
      $sal = [math]::Round($sal - (Get-RandomFloat -min 3.0 -max 6.0 -round 2), 2)
      $rainEvent = $true
    } else { $rainEvent = $false }

    # Dissolved oxygen: inversely correlated with temperature plus some noise
    $doBase = Get-RandomFloat -min 5.0 -max 9.0 -round 2
    $do = [math]::Round($doBase - ($diurnal * 0.3) + (Get-RandomFloat -min -0.8 -max 0.8 -round 2), 2)

    # Small chance for low-oxygen event
    if (($i % 200) -eq 150) { $do = Get-RandomFloat -min 2.2 -max 4.0 -round 2 }

    # pH: mildly buffered but affected by rain events and organic loading
    $phEffect = 0
    if ($rainEvent) { $phEffect = Get-RandomFloat -min 0.3 -max 0.6 -round 2 }
    $ph = [math]::Round(7.0 + (Get-RandomFloat -min -0.4 -max 0.6 -round 2) - $phEffect, 2)

    # Clamp values to safe ranges
    $do = [math]::Max(1.0, [math]::Min(14.0, $do))
    $temp = [math]::Max(0.0, [math]::Min(45.0, $temp))
    $sal = [math]::Max(0.0, [math]::Min(42.0, $sal))
    $ph = [math]::Max(3.0, [math]::Min(11.0, $ph))

    $payload = @{
        user_id = $UserId
        recorded_at = $timestamp.ToString('yyyy-MM-dd HH:mm:ss')
        temperature = $temp
        salinity = $sal
        dissolved_oxygen = $do
        ph_level = $ph
    }

    $json = $payload | ConvertTo-Json -Depth 3

    if ($DryRun) {
      Write-Host "[DRYRUN] POST $Url -H 'Authorization: Bearer $ApiKey' -d '$json'"
    } else {
      $headers = @{ 'Authorization' = "Bearer $ApiKey"; 'Content-Type' = 'application/json' }
      $attempt = 0
      $sent = $false
      while (-not $sent -and $attempt -lt $Retries) {
        try {
          $attempt++
          $resp = Invoke-RestMethod -Uri $Url -Method Post -Body $json -Headers $headers -ErrorAction Stop
          Write-Host "[$($i+1)/$Count] $($payload.recorded_at) -> SUCCESS: $(if ($null -ne $resp) { (ConvertTo-Json $resp -Depth 2) } else { 'no body' })" -ForegroundColor Green
          $sent = $true
        } catch {
          $err = $_.Exception.Message
          if ($attempt -lt $Retries) {
            Write-Host "[$($i+1)/$Count] attempt $attempt/$Retries failed: $err -- retrying in ${RetryDelayMs}ms..." -ForegroundColor Yellow
            Start-Sleep -Milliseconds $RetryDelayMs
          } else {
            Write-Host "[$($i+1)/$Count] attempt $attempt/$Retries failed: $err -- giving up." -ForegroundColor Red
          }
        }
      }

      Start-Sleep -Milliseconds $IntervalMs
    }
  }

  Write-Host "Done sending $Count records." -ForegroundColor Cyan

}
