<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Rab;

$rab = Rab::where('rab_number', 'DMY-025/RAB/SBK/XII/2025')->first();

echo "RAB Number: " . $rab->rab_number . "\n";
echo "Items Count: " . $rab->operationalExpenseItems->count() . "\n";

foreach ($rab->operationalExpenseItems as $item) {
    echo "Item ID: {$item->id}\n";
    echo "  Group Name: " . json_encode($item->group_name) . "\n";
    echo "  Item Name: " . json_encode($item->item_name) . "\n";
    echo "  Total: {$item->total}\n";
}
