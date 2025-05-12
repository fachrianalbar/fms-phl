<?php

use App\Http\Controllers\Bank\BankAccountController;
use App\Http\Controllers\Bank\BankBookController;
use App\Http\Controllers\Bank\ConfigBankController;
use App\Http\Controllers\Bank\ExpenseController;
use App\Http\Controllers\Bank\TransferFundController;
use App\Http\Controllers\Bank\UserBankController;
use Illuminate\Support\Facades\Route;

Route::prefix('bank')->name('bank.')->group(function () {
    Route::resource('transfer-fund', TransferFundController::class);
    Route::resource('expense', ExpenseController::class);
    Route::resource('bank-book', BankBookController::class);
    Route::resource('bank-account', BankAccountController::class);
    Route::resource('user-bank', UserBankController::class);
    Route::resource('config-bank', ConfigBankController::class);
    Route::delete('config-bank/delete-by-user/{code}', [ConfigBankController::class, 'destroyByUser'])->name('config-bank.destroy-by-user');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('transfer-fund', [TransferFundController::class, 'datatable'])->name('transfer-fund');
    Route::get('expense', [ExpenseController::class, 'datatable'])->name('expense');
    Route::get('bank-book', [BankBookController::class, 'datatable'])->name('bank-book');
    Route::get('bank-account', [BankAccountController::class, 'datatable'])->name('bank-account');
    Route::get('user-bank', [UserBankController::class, 'datatable'])->name('bank-user');
    Route::get('config-bank', [ConfigBankController::class, 'datatable'])->name('config-bank');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('list-user-bank', [ConfigBankController::class, 'listUserBank'])->name('list-user-bank');
});
