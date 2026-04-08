<?php

use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Inventory\ItemLocationController;
use App\Http\Controllers\Inventory\ItemUnitController;
use App\Http\Controllers\Inventory\StockController;
use App\Http\Controllers\Inventory\StockTransactionController;
use App\Http\Controllers\Inventory\SupplierController;
use App\Http\Controllers\Inventory\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::post('items/sync-latest-purchase-price', [ItemController::class, 'syncLatestPurchasePrice'])->name('items.sync-latest-purchase-price');
    Route::get('items/{itemCode}/purchase-history', [ItemController::class, 'purchaseHistory'])->name('items.purchase-history');
    Route::resource('items', ItemController::class);
    Route::resource('item-category', ItemCategoryController::class);
    Route::resource('warehouse', WarehouseController::class);
    Route::resource('supplier', SupplierController::class);
    Route::resource('item-unit', ItemUnitController::class);
    Route::resource('item-location', ItemLocationController::class);
    Route::resource('transaction-stock', StockTransactionController::class);
    Route::resource('stock', StockController::class);
    Route::get('pdf-stock', [StockController::class, 'pdfStock'])->name('stock.pdf-stock');
    Route::post('stock/update-initial', [StockController::class, 'updateInitialStock'])->name('stock.update-initial');
    Route::get('stock-detail', [StockController::class, 'getItemDetail'])->name('stock.detail');
    Route::get('stock-detail-datatable', [StockController::class, 'getDetailDatatable'])->name('stock.detail-datatable');
    Route::get('stock-detail-summary', [StockController::class, 'getDetailSummary'])->name('stock.detail-summary');
    Route::get('pdf-stock-detail', [StockController::class, 'pdfStockDetail'])->name('stock.pdf-stock-detail');
    Route::get('pdf-stock-transaction', [StockTransactionController::class, 'pdfStockTransaction'])->name('transaction-stock.pdf-transaction-stock');
    Route::get('transaction-stock-detail', [StockTransactionController::class, 'getWarehouseDetail'])->name('transaction-stock.detail');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('items', [ItemController::class, 'datatable'])->name('items');
    Route::get('item-category', [ItemCategoryController::class, 'datatable'])->name('item-category');
    Route::get('warehouse', [WarehouseController::class, 'datatable'])->name('warehouse');
    Route::get('supplier', [SupplierController::class, 'datatable'])->name('supplier');
    Route::get('item-unit', [ItemUnitController::class, 'datatable'])->name('item-unit');
    Route::get('item-location', [ItemLocationController::class, 'datatable'])->name('item-location');
    Route::get('stock', [StockController::class, 'datatable'])->name('stock');
    Route::get('transaction-stock', [StockTransactionController::class, 'datatable'])->name('transaction-stock');
});
