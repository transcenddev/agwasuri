<?php
// scripts/clear_water_quality.php
// Boot Laravel and truncate the water_quality_data table, printing counts before/after.

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

try {
    $before = DB::table('water_quality_data')->count();
    echo "Before: $before rows in water_quality_data\n";

    // Truncate (DELETE for sqlite is fine; use DB::table->truncate() which will execute DELETE for sqlite)
    DB::table('water_quality_data')->truncate();

    $after = DB::table('water_quality_data')->count();
    echo "After:  $after rows in water_quality_data\n";
    echo "Truncate completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
