<?php

use App\Http\Controllers\Finance\DirectPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Menu Pembayaran Langsung — menu tersendiri (sebelumnya Finance → Order Payment)
|--------------------------------------------------------------------------
| Mencatat pembayaran customer atas satu order pengiriman:
| - PPN/PPH dapat diisi sebagai persentase maupun nominal;
| - pembayaran dapat partial (DP/cicilan) sampai lunas;
| - setiap pembayaran tercatat pada riwayat pembayaran order.
*/

Route::resource('direct-payment', DirectPaymentController::class);

Route::prefix('direct-payment')->name('direct-payment.')->group(function () {
    Route::post('pdf-multi', [DirectPaymentController::class, 'pdfMulti'])->name('pdf-multi');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('direct-payment', [DirectPaymentController::class, 'datatable'])->name('direct-payment');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('direct-payment-detail/{orderCode}', [DirectPaymentController::class, 'orderDetailPayment'])->name('direct-payment-detail');
});
