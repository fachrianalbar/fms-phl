<?php

use App\Http\Controllers\Finance\VendorInvoiceController;
use App\Http\Controllers\Finance\VendorPaymentListController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Menu Vendor — seluruh proses pembayaran ke vendor armada eksternal
|--------------------------------------------------------------------------
| 1. Order Menunggu Nota : order fleet external yang belum punya nota (generate nota)
| 2. Invoice Belum Lunas : nota pending/partial (bayar DP/cicilan/lunas)
| 3. Invoice Lunas       : nota yang semua ordernya sudah dibayar lunas
| 4. Daftar Pembayaran   : riwayat seluruh transaksi pembayaran (DP/cicilan)
*/

Route::prefix('vendor')->name('vendor.')->group(function () {
    // 1. Order Menunggu Nota (order belum dibuat invoice — nota digenerate di sini)
    Route::get('order/waiting', [VendorInvoiceController::class, 'indexWaiting'])->name('order.waiting');

    // 2. Invoice Belum Lunas
    Route::get('invoice/unpaid', [VendorInvoiceController::class, 'indexUnpaid'])->name('invoice.unpaid');

    // 2. Invoice Lunas
    Route::get('invoice/paid', [VendorInvoiceController::class, 'indexPaid'])->name('invoice.paid');

    // Operasi invoice vendor (dipanggil dari halaman unpaid/paid)
    Route::post('invoice/payment', [VendorInvoiceController::class, 'store'])->name('invoice.payment.store');
    Route::post('invoice/generate-nota', [VendorInvoiceController::class, 'generateNota'])->name('invoice.generate-nota');
    Route::post('invoice/cancel-nota/{orderCode}', [VendorInvoiceController::class, 'cancelNota'])->name('invoice.cancel-nota');
    Route::delete('invoice/payment/{orderCode}', [VendorInvoiceController::class, 'destroy'])->name('invoice.payment.cancel');
    Route::get('invoice/pdf/{orderCode}', [VendorInvoiceController::class, 'pdf'])->name('invoice.pdf');
    Route::get('invoice/pdf-nota/{orderCode}', [VendorInvoiceController::class, 'pdfNota'])->name('invoice.pdf-nota');
    Route::post('invoice/pdf-multi', [VendorInvoiceController::class, 'pdfMulti'])->name('invoice.pdf-multi');

    // 3. Daftar Pembayaran
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/', [VendorPaymentListController::class, 'index'])->name('index');
    });
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('vendor-invoice/waiting', [VendorInvoiceController::class, 'datatableWaiting'])->name('vendor-invoice.waiting');
    Route::get('vendor-invoice/unpaid', [VendorInvoiceController::class, 'datatableUnpaid'])->name('vendor-invoice.unpaid');
    Route::get('vendor-invoice/paid', [VendorInvoiceController::class, 'datatablePaid'])->name('vendor-invoice.paid');
    Route::get('vendor-payment-list', [VendorPaymentListController::class, 'datatable'])->name('vendor-payment-list');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('vendor-invoice-detail/{orderCode}', [VendorInvoiceController::class, 'getDetail'])->name('vendor-invoice-detail');
});
