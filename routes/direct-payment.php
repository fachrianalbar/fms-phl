<?php

use App\Http\Controllers\Finance\DirectPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Menu Pembayaran Langsung — pembayaran customer non-DO per order / per nota
|--------------------------------------------------------------------------
| 1. Order Belum Lunas : order standalone belum lunas + nota pending/partial.
|                        Nota multi-DO digenerate & dibayar di sini
|                        (DP/cicilan/lunas), termasuk pembayaran tunggal.
| 2. Order Lunas       : order standalone lunas + nota yang sudah lunas.
| 3. Daftar Pembayaran : riwayat seluruh transaksi pembayaran.
|
| Route lama direct-payment (tanpa submenu) dialihkan ke Order Belum Lunas
| agar bookmark tetap jalan; direct-payment.show tetap hidup untuk
| redirect legacy dari routes/finance.php.
*/

Route::prefix('direct-payment')->name('direct-payment.')->group(function () {
    // 1. Order Belum Lunas (generate nota + bayar tunggal + bayar nota)
    Route::get('order/unpaid', [DirectPaymentController::class, 'indexUnpaid'])->name('order.unpaid');

    // 2. Order Lunas (baca + cetak + batal pembayaran)
    Route::get('order/paid', [DirectPaymentController::class, 'indexPaid'])->name('order.paid');

    // 3. Daftar Pembayaran (riwayat transaksi)
    Route::get('payment', [DirectPaymentController::class, 'paymentIndex'])->name('payment.index');

    // Detail order (halaman show legacy, dipakai redirect routes/finance.php)
    Route::get('show/{id}', [DirectPaymentController::class, 'show'])->name('show');

    // Operasi pembayaran (dipanggil dari halaman unpaid/paid)
    Route::post('order/payment', [DirectPaymentController::class, 'storeBatch'])->name('order.payment.store');
    Route::post('order/payment-single', [DirectPaymentController::class, 'store'])->name('order.payment-single.store');
    Route::post('order/generate-nota', [DirectPaymentController::class, 'generateNota'])->name('order.generate-nota');
    Route::post('order/cancel-nota/{orderCode}', [DirectPaymentController::class, 'cancelNota'])->name('order.cancel-nota');
    Route::delete('order/payment/{orderCode}', [DirectPaymentController::class, 'cancelPayment'])->name('order.payment.cancel');

    // Cetak PDF
    Route::post('pdf-multi', [DirectPaymentController::class, 'pdfMulti'])->name('pdf-multi');
    Route::get('pdf-nota/{orderCode}', [DirectPaymentController::class, 'pdfNota'])->name('pdf-nota');
});

// URL lama direct-payment dialihkan ke halaman Order Belum Lunas.
// (direct-payment.index & direct-payment.show dipakai link legacy.)
Route::get('direct-payment', fn () => redirect()->route('direct-payment.order.unpaid'))->name('direct-payment.index');

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('direct-payment/unpaid', [DirectPaymentController::class, 'datatableUnpaid'])->name('direct-payment.unpaid');
    Route::get('direct-payment/paid', [DirectPaymentController::class, 'datatablePaid'])->name('direct-payment.paid');
    Route::get('direct-payment-payment-list', [DirectPaymentController::class, 'paymentDatatable'])->name('direct-payment-payment-list');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('direct-payment-detail/{orderCode}', [DirectPaymentController::class, 'orderDetailPayment'])->name('direct-payment-detail');
    Route::get('direct-payment-nota-detail/{orderCode}', [DirectPaymentController::class, 'notaDetail'])->name('direct-payment-nota-detail');
    Route::get('direct-payment-payment-detail/{transactionKey}', [DirectPaymentController::class, 'paymentDetail'])->name('direct-payment-payment-detail');
});
