<?php
// Wrapper: forward supported flags to the artisan command so the
// `sample:water-quality` command stays as the single source of truth.
// Usage examples:
//   php scripts/simulate-realtime.php --user=admin@admin.com --count=10 --interval=1
//   php scripts/simulate-realtime.php --users=test@test.com --count=5 --no-broadcast

chdir(__DIR__ . '/..');

$raw = $_SERVER['argv'];
array_shift($raw);

$map = [
    'user' => 'users',
    'users' => 'users',
    'count' => 'count',
    'n' => 'count',
    'interval' => 'interval',
    'loop' => 'loop',
    'start' => 'start',
    'no-broadcast' => 'no-broadcast',
    'no_broadcast' => 'no-broadcast',
    'yes' => 'yes',
];

$flags = [];
$extras = [];

foreach ($raw as $arg) {
    if (strpos($arg, '--') !== 0) {
        $extras[] = $arg;
        continue;
    }
    $pair = substr($arg, 2);
    if (strpos($pair, '=') !== false) {
        [$k, $v] = explode('=', $pair, 2);
    } else {
        $k = $pair;
        $v = true;
    }
    if (isset($map[$k])) {
        $flags[$map[$k]] = $v;
    } else {
        $extras[] = $arg;
    }
}

$cmd = ['php', 'artisan', 'sample:water-quality'];
foreach ($flags as $k => $v) {
    if ($v === true) {
        $cmd[] = "--$k";
    } else {
        $cmd[] = "--$k=" . escapeshellarg($v);
    }
}
foreach ($extras as $e) $cmd[] = escapeshellarg($e);

$shell = implode(' ', $cmd);
echo "Invoking: $shell\n";
passthru($shell, $rc);
exit($rc);
