<?php

use App\Http\Controllers\Purchasing\DirectPurchaseController;
use App\Http\Controllers\Purchasing\PurchaseController;
use App\Http\Controllers\Purchasing\PurchasePaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Menu Purchasing
|--------------------------------------------------------------------------
| 1. Purchase           : pembelian (PO) + stok masuk.
| 2. Purchase Payment   : Hutang Supplier Belum Lunas — bayar satu/banyak PO.
| 3. Purchase Paid      : Hutang Supplier Lunas — arsip + batal pembayaran.
|
| Catatan: halaman "paid" sengaja memakai path terpisah (purchase-paid),
| bukan di bawah purchase-payment, agar tidak bentrok dengan logika
| penanda menu aktif di sidebar (prefix match).
*/

Route::prefix('purchasing')->name('purchasing.')->group(function () {
    Route::resource('purchase', PurchaseController::class);
    Route::delete('purchase-detail/{id}', [PurchaseController::class, 'deletePurchaseDetail'])->name('purchase-detail.destroy');

    Route::resource('direct-purchase', DirectPurchaseController::class);

    Route::prefix('purchase-payment')->name('purchase-payment.')->group(function () {
        // Hutang Supplier Belum Lunas (bayar tunggal / batch)
        Route::get('/', [PurchasePaymentController::class, 'index'])->name('index');

        // Operasi pembayaran
        Route::post('batch', [PurchasePaymentController::class, 'storeBatch'])->name('batch.store');
        Route::delete('batch/{batchCode}', [PurchasePaymentController::class, 'cancelPayment'])->name('batch.cancel');

        // Detail pembelian + riwayat pembayaran (ajax)
        Route::get('detail/{purchaseCode}', [PurchasePaymentController::class, 'detail'])->name('detail');
    });

    // Hutang Supplier Lunas (arsip)
    Route::get('purchase-paid', [PurchasePaymentController::class, 'paid'])->name('purchase-payment.paid');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('purchase', [PurchaseController::class, 'datatable'])->name('purchase');
    Route::get('direct-purchase', [DirectPurchaseController::class, 'datatable'])->name('direct-purchase');
    Route::get('purchase-payment/unpaid', [PurchasePaymentController::class, 'datatableUnpaid'])->name('purchase-payment.unpaid');
    Route::get('purchase-payment/paid', [PurchasePaymentController::class, 'datatablePaid'])->name('purchase-payment.paid');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('item-by-supplier/{supplierCode}', [PurchaseController::class, 'itemBySupplier'])->name('item-by-supplier');
    Route::get('purchase-generate-code', [PurchaseController::class, 'generateCode'])->name('purchase-generate-code');
    Route::get('direct-purchase-generate-code', [DirectPurchaseController::class, 'generateCode'])->name('direct-purchase-generate-code');
});
