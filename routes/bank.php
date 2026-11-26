<?php

use App\Http\Controllers\Bank\BankAccountController;
use App\Http\Controllers\Bank\BankBookController;
use App\Http\Controllers\Bank\ConfigBankController;
use App\Http\Controllers\Bank\ExpenseController;
use App\Http\Controllers\Bank\TransferFundController;
use App\Http\Controllers\Bank\UserBankController;
use Illuminate\Support\Facades\Route;

Route::prefix('bank')->name('bank.')->group(function () {
    Route::middleware(['access:SPRADMIN,SPRUSER'])->group(function () {
        Route::resource('transfer-fund', TransferFundController::class);
        Route::resource('expense', ExpenseController::class);
        Route::get('bank-book/export/excel', [BankBookController::class, 'exportExcel'])->name('bank-book.export-excel');
        Route::get('bank-book/export/pdf', [BankBookController::class, 'exportPdf'])->name('bank-book.export-pdf');
        Route::resource('bank-book', BankBookController::class)->only(['index', 'show']);
        Route::resource('config-bank', ConfigBankController::class);
        Route::delete('config-bank/delete-by-user/{code}', [ConfigBankController::class, 'destroyByUser'])->name('config-bank.destroy-by-user');
    });

    Route::middleware(['access:SPRADMIN,SPRUSER,DO,TRL250604212202,TRL250604212231,TRL250604212242'])->group(function () {
        Route::get('bank-account/export/excel', [BankAccountController::class, 'exportExcel'])->name('bank-account.export-excel');
        Route::get('bank-account/export/pdf', [BankAccountController::class, 'exportPdf'])->name('bank-account.export-pdf');
        Route::resource('bank-account', BankAccountController::class);
        Route::get('user-bank/export/excel', [UserBankController::class, 'exportExcel'])->name('user-bank.export-excel');
        Route::get('user-bank/export/pdf', [UserBankController::class, 'exportPdf'])->name('user-bank.export-pdf');
        Route::resource('user-bank', UserBankController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::middleware(['access:SPRADMIN,SPRUSER'])->group(function () {
        Route::get('transfer-fund', [TransferFundController::class, 'datatable'])->name('transfer-fund');
        Route::get('expense', [ExpenseController::class, 'datatable'])->name('expense');
        Route::get('bank-book', [BankBookController::class, 'datatable'])->name('bank-book');
        Route::get('config-bank', [ConfigBankController::class, 'datatable'])->name('config-bank');
    });

    Route::middleware(['access:SPRADMIN,SPRUSER,DO,TRL250604212202,TRL250604212231,TRL250604212242'])->group(function () {
        Route::get('bank-account', [BankAccountController::class, 'datatable'])->name('bank-account');
        Route::get('user-bank', [UserBankController::class, 'datatable'])->name('bank-user');
    });
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('list-user-bank', [ConfigBankController::class, 'listUserBank'])
        ->middleware('access:SPRADMIN,SPRUSER')
        ->name('list-user-bank');
    Route::get('user-bank/stats', [UserBankController::class, 'stats'])
        ->middleware('access:SPRADMIN,SPRUSER,DO,TRL250604212202,TRL250604212231,TRL250604212242')
        ->name('user-bank.stats');
});

Route::prefix('api')->name('api.')->group(function () {
    Route::get('user-bank/company', [UserBankController::class, 'getCompanyBanks'])
        ->middleware('access:SPRADMIN,SPRUSER,DO,TRL250604212202,TRL250604212231,TRL250604212242')
        ->name('user-bank.company');
});
