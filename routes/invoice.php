<?php

use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\InvoicePaymentController;
use App\Http\Controllers\Finance\InvoicePaymentTransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('invoice')->name('invoice.')->group(function () {
    // 1. Create Invoice
    Route::get('create', [InvoiceController::class, 'create'])->name('create');
    Route::post('store', [InvoiceController::class, 'store'])->name('store');

    // 2. Unpaid Invoice
    Route::get('unpaid', [InvoiceController::class, 'indexUnpaid'])->name('unpaid');

    // 3. Paid Invoice
    Route::get('paid', [InvoiceController::class, 'indexPaid'])->name('paid');

    // Invoice Operations
    Route::get('{id}/edit', [InvoiceController::class, 'edit'])->name('edit');
    Route::put('{id}', [InvoiceController::class, 'update'])->name('update');
    Route::delete('{id}', [InvoiceController::class, 'destroy'])->name('destroy');
    Route::get('pdf/{id}', [InvoiceController::class, 'pdfInvoice'])->name('pdf');
    // Route proses pembayaran (POST {id}/payment) dihapus — pembayaran kini lewat
    // menu Transaksi Pembayaran (invoice/payment-transaction)
    Route::post('recalculate-all', [InvoiceController::class, 'recalculateAll'])->name('recalculate-all');
    Route::post('{id}/recalculate', [InvoiceController::class, 'recalculate'])->name('recalculate');
    Route::post('{id}/update-number', [InvoiceController::class, 'updateInvoiceNumber'])->name('update-number');
    Route::put('detail/{id}', [InvoiceController::class, 'storeInvoiceDetail'])->name('detail.store');
    Route::delete('detail/{id}', [InvoiceController::class, 'destroyInvoiceDetail'])->name('detail.destroy');

    // 4. Invoice Payment
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/', [InvoicePaymentController::class, 'index'])->name('index');
        Route::get('{id}/edit', [InvoicePaymentController::class, 'edit'])->name('edit');
        Route::put('{id}', [InvoicePaymentController::class, 'update'])->name('update');
        Route::get('export/pdf', [InvoicePaymentController::class, 'exportPdf'])->name('export-pdf');
        Route::get('export/excel', [InvoicePaymentController::class, 'exportExcel'])->name('export-excel');
    });

    // 5. Payment Transaction (Transaksi Pembayaran: 1 transaksi untuk banyak faktur + claim)
    Route::prefix('payment-transaction')->name('payment-transaction.')->group(function () {
        Route::get('/', [InvoicePaymentTransactionController::class, 'index'])->name('index');
        Route::get('create', [InvoicePaymentTransactionController::class, 'create'])->name('create');
        Route::post('/', [InvoicePaymentTransactionController::class, 'store'])->name('store');
        Route::get('{id}', [InvoicePaymentTransactionController::class, 'show'])->name('show');
        Route::get('customer/{customerCode}/invoices', [InvoicePaymentTransactionController::class, 'customerInvoices'])->name('customer-invoices');
    });
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('invoice/unpaid', [InvoiceController::class, 'datatableUnpaid'])->name('invoice.unpaid');
    Route::get('invoice/paid', [InvoiceController::class, 'datatablePaid'])->name('invoice.paid');
    Route::get('invoice/payment', [InvoicePaymentController::class, 'datatable'])->name('invoice.payment');
    Route::get('invoice/payment-transaction', [InvoicePaymentTransactionController::class, 'datatable'])->name('invoice.payment-transaction');
    Route::get('invoice/invoice-order', [InvoiceController::class, 'datatableOrder'])->name('invoice.invoice-order');
});

// Fallback redirects from /faktur/* to /invoice/*
Route::prefix('faktur')->group(function () {
    Route::get('create', fn () => redirect()->route('invoice.create'));
    Route::get('pembuatan', fn () => redirect()->route('invoice.create'));
    Route::get('unpaid', fn () => redirect()->route('invoice.unpaid'));
    Route::get('belum-lunas', fn () => redirect()->route('invoice.unpaid'));
    Route::get('paid', fn () => redirect()->route('invoice.paid'));
    Route::get('lunas', fn () => redirect()->route('invoice.paid'));
    Route::get('payment', fn () => redirect()->route('invoice.payment.index'));
    Route::get('pembayaran', fn () => redirect()->route('invoice.payment.index'));
    Route::get('{id}/edit', fn ($id) => redirect()->route('invoice.edit', $id));
    Route::get('pdf/{id}', fn ($id) => redirect()->route('invoice.pdf', $id));
});
