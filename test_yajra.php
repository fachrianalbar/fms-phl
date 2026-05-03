<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\Auth::login(\App\Models\User::first());
$request = request();
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

$controller = app(\App\Http\Controllers\Purchasing\PurchasePaymentController::class);
$response = $controller->datatable($request);

echo $response->content();
