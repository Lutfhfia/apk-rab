<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Rab;

$rab = Rab::where('status', 'selesai')->first();
if ($rab) {
    echo "RAB: {$rab->rab_number}\n";
    echo "Approvals count: {$rab->approvals->count()}\n";
    echo "Discussions count: {$rab->discussions->count()}\n";
} else {
    echo "No selesai RAB found.\n";
}
