<?php

use App\Http\Controllers\Report\CommissionReportController;
use App\Http\Controllers\Report\DriverReportController;
use App\Http\Controllers\Report\DriverSalaryController;
use App\Http\Controllers\Report\FactoryTaxController;
use App\Http\Controllers\Report\FreightRateReportController;
use App\Http\Controllers\Report\ItemPriceReportController;
use App\Http\Controllers\Report\MaintenancePerCompanyController;
use App\Http\Controllers\Report\MaintenancePerFleetController;
use App\Http\Controllers\Report\MaintenancePerSupplierController;
use App\Http\Controllers\Report\OmsetReportController;
use App\Http\Controllers\Report\OrderDetailController;
use App\Http\Controllers\Report\OverallReportController;
use App\Http\Controllers\Report\ProfitLossController;
use App\Http\Controllers\Report\ReceivableAgingController;
use App\Http\Controllers\Report\SupplierPurchaseController;
use App\Http\Controllers\Report\VendorTaxController;
use Illuminate\Support\Facades\Route;

Route::prefix('report')->name('report.')->group(function () {
    Route::middleware(['access:SPRADMIN,SPRUSER,TRL250604212202'])->group(function () {
        Route::resource('profit-loss', ProfitLossController::class)->only(['index', 'show']);
        Route::get('excel-profit-loss', [ProfitLossController::class, 'excelProfitLoss'])->name('profit-loss.excel-profit-loss');
        Route::get('pdf-profit-loss', [ProfitLossController::class, 'pdfProfitLoss'])->name('profit-loss.pdf-profit-loss');
    });
    Route::resource('driver-salary', DriverSalaryController::class)->except(['create']);
    Route::post('driver-salary/sync-existing-status', [DriverSalaryController::class, 'syncExistingStatus'])->name('driver-salary.sync-existing-status');
    Route::get('excel-driver-salary', [DriverSalaryController::class, 'excelDriverSalary'])->name('driver-salary.excel-driver-salary');
    Route::get('pdf-driver-salary', [DriverSalaryController::class, 'pdfDriverSalary'])->name('driver-salary.pdf-driver-salary');
    Route::get('pdf-driver-salary-processed/{id}', [DriverSalaryController::class, 'pdfDriverSalaryProcessed'])->name('driver-salary.pdf-processed');

    Route::post('commission/order-payment-date', [CommissionReportController::class, 'updateOrderPaymentDate'])->name('commission.order-payment-date');
    Route::resource('commission', CommissionReportController::class)->except(['create']);
    Route::post('commission/{id}/payment-date', [CommissionReportController::class, 'updatePaymentDate'])->name('commission.payment-date');
    Route::get('excel-commission', [CommissionReportController::class, 'excelCommission'])->name('commission.excel-commission');
    Route::get('pdf-commission', [CommissionReportController::class, 'pdfByDate'])->name('commission.pdf-by-date');
    Route::get('pdf-commission-processed/{id}', [CommissionReportController::class, 'pdfProcessed'])->name('commission.pdf-processed');

    Route::get('omset', [OmsetReportController::class, 'index'])->name('omset.index');
    Route::get('excel-omset', [OmsetReportController::class, 'exportExcel'])->name('omset.excel');
    Route::get('pdf-omset', [OmsetReportController::class, 'exportPdf'])->name('omset.pdf');
    Route::resource('order-detail', OrderDetailController::class)->only(['index']);
    Route::get('excel-order-detail', [OrderDetailController::class, 'excelOrderDetail'])->name('order-detail.excel-order-detail');
    Route::get('excel-order-detail-init', [OrderDetailController::class, 'excelOrderDetailInit'])->name('order-detail.excel-order-detail-init');
    Route::get('excel-order-detail-chunk', [OrderDetailController::class, 'excelOrderDetailChunk'])->name('order-detail.excel-order-detail-chunk');
    Route::get('excel-order-detail-download', [OrderDetailController::class, 'excelOrderDetailDownload'])->name('order-detail.excel-order-detail-download');
    Route::get('pdf-order-detail', [OrderDetailController::class, 'pdfOrderDetail'])->name('order-detail.pdf-order-detail');
    Route::get('maintenance-fleet', [MaintenancePerFleetController::class, 'index'])->name('maintenance-fleet.index');
    Route::get('maintenance-fleet/detail/{fleetCode}', [MaintenancePerFleetController::class, 'detail'])->name('maintenance-fleet.detail');
    Route::get('excel-maintenance-fleet', [MaintenancePerFleetController::class, 'excelMaintenanceFleet'])->name('maintenance-fleet.excel-maintenance-fleet');
    Route::get('pdf-maintenance-fleet', [MaintenancePerFleetController::class, 'pdfMaintenanceFleet'])->name('maintenance-fleet.pdf-maintenance-fleet');
    Route::get('excel-maintenance-fleet-detail/{fleetCode}', [MaintenancePerFleetController::class, 'excelMaintenanceFleetDetail'])->name('maintenance-fleet.excel-maintenance-fleet-detail');
    Route::get('pdf-maintenance-fleet-detail/{fleetCode}', [MaintenancePerFleetController::class, 'pdfMaintenanceFleetDetail'])->name('maintenance-fleet.pdf-maintenance-fleet-detail');
    Route::get('maintenance-supplier', [MaintenancePerSupplierController::class, 'index'])->name('maintenance-supplier.index');
    Route::get('maintenance-supplier/detail/{supplierCode}', [MaintenancePerSupplierController::class, 'detail'])->name('maintenance-supplier.detail');
    Route::get('excel-maintenance-supplier', [MaintenancePerSupplierController::class, 'excelMaintenanceSupplier'])->name('maintenance-supplier.excel-maintenance-supplier');
    Route::get('pdf-maintenance-supplier', [MaintenancePerSupplierController::class, 'pdfMaintenanceSupplier'])->name('maintenance-supplier.pdf-maintenance-supplier');
    Route::get('excel-maintenance-supplier-detail/{supplierCode}', [MaintenancePerSupplierController::class, 'excelMaintenanceSupplierDetail'])->name('maintenance-supplier.excel-maintenance-supplier-detail');
    Route::get('pdf-maintenance-supplier-detail/{supplierCode}', [MaintenancePerSupplierController::class, 'pdfMaintenanceSupplierDetail'])->name('maintenance-supplier.pdf-maintenance-supplier-detail');
    Route::get('maintenance-company-internal', [MaintenancePerCompanyController::class, 'index'])->name('maintenance-company-internal.index');
    Route::get('maintenance-company-internal/detail/{fleetCompanyCode}', [MaintenancePerCompanyController::class, 'detail'])->name('maintenance-company-internal.detail');
    Route::get('excel-maintenance-company-internal', [MaintenancePerCompanyController::class, 'excelMaintenanceCompany'])->name('maintenance-company-internal.excel');
    Route::get('pdf-maintenance-company-internal', [MaintenancePerCompanyController::class, 'pdfMaintenanceCompany'])->name('maintenance-company-internal.pdf');
    Route::get('supplier', [SupplierPurchaseController::class, 'index'])->name('supplier.index');
    Route::get('supplier/detail/{supplierCode}', [SupplierPurchaseController::class, 'detail'])->name('supplier.detail');
    Route::get('excel-supplier', [SupplierPurchaseController::class, 'excelSupplier'])->name('supplier.excel-supplier');
    Route::get('pdf-supplier', [SupplierPurchaseController::class, 'pdfSupplier'])->name('supplier.pdf-supplier');
    Route::get('excel-supplier-detail/{supplierCode}', [SupplierPurchaseController::class, 'excelSupplierDetail'])->name('supplier.excel-supplier-detail');
    Route::get('pdf-supplier-detail/{supplierCode}', [SupplierPurchaseController::class, 'pdfSupplierDetail'])->name('supplier.pdf-supplier-detail');

    Route::get('receivable-aging', [ReceivableAgingController::class, 'index'])->name('receivable-aging.index');
    Route::get('receivable-aging/detail/{customerCode}', [ReceivableAgingController::class, 'detail'])->name('receivable-aging.detail');
    Route::get('excel-receivable-aging', [ReceivableAgingController::class, 'excel'])->name('receivable-aging.excel');
    Route::get('pdf-receivable-aging', [ReceivableAgingController::class, 'pdf'])->name('receivable-aging.pdf');

    Route::get('drivers', [DriverReportController::class, 'index'])->name('drivers.index');
    Route::get('excel-drivers', [DriverReportController::class, 'exportExcel'])->name('drivers.excel');
    Route::get('pdf-drivers', [DriverReportController::class, 'exportPdf'])->name('drivers.pdf');

    Route::get('freight-rates', [FreightRateReportController::class, 'index'])->name('freight-rates.index');
    Route::get('excel-freight-rates', [FreightRateReportController::class, 'exportExcel'])->name('freight-rates.excel');
    Route::get('pdf-freight-rates', [FreightRateReportController::class, 'exportPdf'])->name('freight-rates.pdf');

    Route::get('item-prices', [ItemPriceReportController::class, 'index'])->name('item-prices.index');
    Route::get('excel-item-prices', [ItemPriceReportController::class, 'exportExcel'])->name('item-prices.excel');
    Route::get('pdf-item-prices', [ItemPriceReportController::class, 'exportPdf'])->name('item-prices.pdf');

    Route::get('factory-tax', [FactoryTaxController::class, 'index'])->name('factory-tax.index');
    Route::post('factory-tax/{id}/tax-info', [FactoryTaxController::class, 'updateTaxInfo'])->name('factory-tax.update-tax');
    Route::get('excel-factory-tax', [FactoryTaxController::class, 'exportExcel'])->name('factory-tax.excel');
    Route::get('pdf-factory-tax', [FactoryTaxController::class, 'exportPdf'])->name('factory-tax.pdf');

    Route::get('vendor-tax', [VendorTaxController::class, 'index'])->name('vendor-tax.index');
    Route::post('vendor-tax/{id}/tax-info', [VendorTaxController::class, 'updateTaxInfo'])->name('vendor-tax.update-tax');
    Route::get('excel-vendor-tax', [VendorTaxController::class, 'exportExcel'])->name('vendor-tax.excel');
    Route::get('pdf-vendor-tax', [VendorTaxController::class, 'exportPdf'])->name('vendor-tax.pdf');

    Route::get('overall', [OverallReportController::class, 'index'])->name('overall.index');
    Route::get('excel-overall', [OverallReportController::class, 'exportExcel'])->name('overall.excel');
    Route::get('pdf-overall', [OverallReportController::class, 'exportPdf'])->name('overall.pdf');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::middleware(['access:SPRADMIN,SPRUSER,TRL250604212202'])->group(function () {
        Route::get('profit-loss', [ProfitLossController::class, 'datatable'])->name('profit-loss');
        Route::get('profit-loss-order', [ProfitLossController::class, 'datatableOrder'])->name('profit-loss-order');
        Route::get('profit-loss-maintenance', [ProfitLossController::class, 'datatableMaintenance'])->name('profit-loss-maintenance');
    });
    Route::get('driver-salary', [DriverSalaryController::class, 'datatable'])->name('driver-salary');
    Route::get('driver-salary-processed', [DriverSalaryController::class, 'datatableProcessed'])->name('driver-salary-processed');
    Route::get('commission-orders', [CommissionReportController::class, 'datatableOrders'])->name('commission-orders');
    Route::get('commission-processed', [CommissionReportController::class, 'datatableProcessed'])->name('commission-processed');
    Route::get('omset', [OmsetReportController::class, 'datatable'])->name('omset');
    Route::get('order-detail', [OrderDetailController::class, 'datatable'])->name('order-detail');
    Route::get('maintenance-fleet', [MaintenancePerFleetController::class, 'datatable'])->name('maintenance-fleet');
    Route::get('maintenance-fleet-detail/{fleetCode}', [MaintenancePerFleetController::class, 'datatableDetail'])->name('maintenance-fleet-detail');
    Route::get('maintenance-supplier', [MaintenancePerSupplierController::class, 'datatable'])->name('maintenance-supplier');
    Route::get('maintenance-supplier-detail/{supplierCode}', [MaintenancePerSupplierController::class, 'datatableDetail'])->name('maintenance-supplier-detail');
    Route::get('maintenance-company-internal', [MaintenancePerCompanyController::class, 'datatable'])->name('maintenance-company-internal');
    Route::get('maintenance-company-internal-detail/{fleetCompanyCode}', [MaintenancePerCompanyController::class, 'datatableDetail'])->name('maintenance-company-internal-detail');
    Route::get('report-supplier', [SupplierPurchaseController::class, 'datatable'])->name('report-supplier');
    Route::get('report-supplier-detail/{supplierCode}', [SupplierPurchaseController::class, 'datatableDetail'])->name('report-supplier-detail');
    Route::get('receivable-aging', [ReceivableAgingController::class, 'datatable'])->name('receivable-aging');
    Route::get('receivable-aging-detail/{customerCode}', [ReceivableAgingController::class, 'datatableDetail'])->name('receivable-aging-detail');
    Route::get('report-drivers', [DriverReportController::class, 'datatable'])->name('report-drivers');
    Route::get('report-freight-rates', [FreightRateReportController::class, 'datatable'])->name('report-freight-rates');
    Route::get('report-item-prices', [ItemPriceReportController::class, 'datatable'])->name('report-item-prices');
    Route::get('report-factory-tax', [FactoryTaxController::class, 'datatable'])->name('report-factory-tax');
    Route::get('report-vendor-tax', [VendorTaxController::class, 'datatable'])->name('report-vendor-tax');
    Route::get('report-overall', [OverallReportController::class, 'datatable'])->name('report-overall');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('profit-loss-summary/{id}', [ProfitLossController::class, 'getProfitLossSummary'])
        ->middleware('access:SPRADMIN,SPRUSER,TRL250604212202')
        ->name('profit-loss-summary');
    Route::get('maintenance-fleet-detail-items/{maintenanceCode}', [MaintenancePerFleetController::class, 'detailItems'])->name('maintenance-fleet-detail-items');
    Route::get('maintenance-company-internal-detail-items/{maintenanceCode}', [MaintenancePerCompanyController::class, 'detailItems'])->name('maintenance-company-internal-detail-items');
    Route::get('supplier-purchase-detail-items/{purchaseCode}', [SupplierPurchaseController::class, 'detailItems'])->name('supplier-purchase-detail-items');
    Route::get('driver-salary-orders', [DriverSalaryController::class, 'getOrderSalary'])->name('driver-salary-orders');
    Route::get('driver-salary-detail/{id}', [DriverSalaryController::class, 'getDetail'])->name('driver-salary-detail');
    Route::get('commission-orders', [CommissionReportController::class, 'getOrderCommission'])->name('commission-orders');
    Route::get('receivable-aging-summary', [ReceivableAgingController::class, 'summary'])->name('receivable-aging-summary');
});
