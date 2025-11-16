<?php
// Usage: php scripts/simulate-realtime.php --user=admin@admin.com --count=10 --interval=1

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\SampleWaterQualityService;
use Illuminate\Support\Carbon;

// Parse args
$opts = [];
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--')) {
        $pair = explode('=', substr($arg, 2), 2);
        $opts[$pair[0]] = $pair[1] ?? true;
    }
}

$userEmail = $opts['user'] ?? 'admin@admin.com';
$count = isset($opts['count']) ? (int)$opts['count'] : (int)($opts['n'] ?? 0);
$interval = isset($opts['interval']) ? (int)$opts['interval'] : 1;
$loop = isset($opts['loop']);
$noBroadcast = isset($opts['no-broadcast']) || isset($opts['no_broadcast']);
$start = $opts['start'] ?? null; // optional start date

if (!$loop && $count <= 0) {
    echo "No count specified. Use --count=N to run a finite demo, or add --loop for continuous run.\n";
    exit(1);
}

$user = User::where('email', $userEmail)->first();
if (!$user) {
    echo "User not found: {$userEmail}\n";
    exit(1);
}

echo "Simulating {$count} water-quality samples for {$userEmail} (user_id={$user->user_id}) every {$interval}s\n";

// If loop flag present and no count, run indefinitely until Ctrl+C. If loop + count, run count times.
if ($loop) {
    $i = 0;
    while (true) {
        if ($count > 0 && $i >= $count) {
            break;
        }

        $recordedAt = $start ? Carbon::parse($start)->addSeconds($i * $interval) : Carbon::now();

        $service = new SampleWaterQualityService();
        $data = $service->createSampleForUser($user, $recordedAt, $noBroadcast);
        echo sprintf("[%s] Created id=%d temp=%s pH=%s DO=%s sal=%s\n", $recordedAt->toDateTimeString(), $data->id, $data->temperature, $data->ph_level, $data->dissolved_oxygen, $data->salinity);

        $i++;
        sleep($interval);
    }

} else {
    for ($i = 0; $i < $count; $i++) {
        $recordedAt = $start ? Carbon::parse($start)->addSeconds($i * $interval) : Carbon::now();

        $service = new SampleWaterQualityService();
        $data = $service->createSampleForUser($user, $recordedAt, $noBroadcast);
        echo sprintf("[%s] Created id=%d temp=%s pH=%s DO=%s sal=%s\n", $recordedAt->toDateTimeString(), $data->id, $data->temperature, $data->ph_level, $data->dissolved_oxygen, $data->salinity);

        if ($i < $count - 1) {
            sleep($interval);
        }
    }
}

echo "Done.\n";
