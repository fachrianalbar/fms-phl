<?php

use Illuminate\Support\Facades\Route;

Route::get('/maintenance', function () {
    return response()->view('maintenance')->setStatusCode(503);
})->name('maintenance');

require __DIR__.'/auth.php';
require __DIR__.'/guest.php';

Route::middleware(['auth'])->group(function () {
    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

    require __DIR__.'/master.php';
    require __DIR__.'/data.php';
    require __DIR__.'/operational.php';
    require __DIR__.'/inventory.php';
    require __DIR__.'/administrator.php';
    require __DIR__.'/purchasing.php';
    require __DIR__.'/warehouse.php';
    require __DIR__.'/report.php';
    require __DIR__.'/bank.php';
    require __DIR__.'/finance.php';
    require __DIR__.'/dashboard.php';
    require __DIR__.'/general.php';
});
