<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$purchases = \App\Models\Purchasing\Purchase::whereIn('status', [0, 1, 2, 3])->take(10)->get();
foreach($purchases as $row) {
    $class = '';
    if ($row->dueDate && $row->paymentStatus != 'Paid') {
        $due = \Carbon\Carbon::parse($row->dueDate)->startOfDay();
        $today = now()->startOfDay();
        $diff = $today->diffInDays($due, false);
        
        if ($diff < 0) {
            $class = 'table-danger text-danger';
        } elseif ($diff >= 0 && $diff <= 7) {
            $class = 'table-warning';
        }
    }
    echo "PO: {$row->code}, Due: {$row->dueDate}, Status: {$row->paymentStatus}, Diff: $diff, Class: $class\n";
}
