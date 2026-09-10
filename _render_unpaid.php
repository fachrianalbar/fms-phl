<?php

$errors = new \Illuminate\Support\ViewErrorBag();

$ctrl = app(\App\Http\Controllers\Finance\DirectPaymentController::class);

$html = null;

// Ambil data view seperti yang dilakukan indexUnpaid
try {
    $route = new \Illuminate\Http\Request();
    $route->headers->set('X-Requested-With', 'XMLHttpRequest');
    $ref = new ReflectionMethod($ctrl, 'indexUnpaid');
    // simpler: render langsung dengan data statis
    $ub = app(\App\Services\Bank\UserBankService::class)->findCompany();
    $stats = app(\App\Services\Finance\DirectPaymentService::class)->statsUnpaid();
    $html = view('direct-payment.order.unpaid', [
        'view' => 'direct-payment.order.',
        'title' => 'Order Belum Lunas',
        'userBank' => $ub,
        'stats' => $stats,
        'errors' => $errors,
    ])->render();
    echo 'render ok len=' . strlen($html) . PHP_EOL;
    file_put_contents(storage_path('app/_unpaid_render.html'), $html);
    echo 'saved' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'ERR: ' . $e->getMessage() . PHP_EOL;
}
