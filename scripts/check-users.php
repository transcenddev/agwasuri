<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "USER_COUNT=" . \App\Models\User::count() . PHP_EOL;
$users = \App\Models\User::take(5)->get()->toArray();
echo json_encode($users, JSON_PRETTY_PRINT) . PHP_EOL;
