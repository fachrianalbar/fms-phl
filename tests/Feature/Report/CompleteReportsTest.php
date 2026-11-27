<?php

namespace Tests\Feature\Report;

use App\Http\Controllers\Report\FactoryTaxController;
use App\Models\Finance\Invoice;
use App\Models\Finance\VendorPayment;
use App\Models\Inventory\Supplier;
use App\Models\Master\Employee;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\User;
use App\Services\Report\OverallReportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class CompleteReportsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::query()->where('roleCode', 'SPRADMIN')->first()
            ?? User::query()->create([
                'code' => 'TST-SPRADMIN',
                'name' => 'Test Super Admin',
                'username' => 'tst_spradmin_'.uniqid(),
                'password' => 'password',
                'roleCode' => 'SPRADMIN',
                'languange' => 'id',
            ]);
    }

    public function test_report_menus_and_role_permissions_are_registered(): void
    {
        $expectedCodes = [
            'OVERALL_REPORT',
            'FREIGHT_RATE_REPORT',
            'ITEM_PRICE_REPORT',
            'FACTORY_TAX_REPORT',
            'VENDOR_TAX_REPORT',
            'DRIVER_LIST_REPORT',
            'MAINTENANCE_SUPPLIER',
            'MAINTENANCE_FLEET',
        ];

        $registered = DB::table('menu')
            ->where('parentCode', 'REPORT')
            ->whereIn('code', $expectedCodes)
            ->pluck('code')
            ->all();

        $this->assertEqualsCanonicalizing($expectedCodes, $registered);

        $adminMenuCount = DB::table('role_menu')
            ->where('roleCode', 'SPRADMIN')
            ->whereIn('menuCode', $expectedCodes)
            ->count();

        $this->assertSame(count($expectedCodes), $adminMenuCount);
    }

    public function test_all_eight_report_index_and_detail_pages_return_200(): void
    {
        $pages = [
            route('report.overall.index'),
            route('report.freight-rates.index'),
            route('report.item-prices.index'),
            route('report.factory-tax.index'),
            route('report.vendor-tax.index'),
            route('report.drivers.index'),
            route('report.maintenance-fleet.index'),
            route('report.maintenance-supplier.index'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }

        $fleet = Fleet::query()->whereNull('deleted_at')->first();
        if ($fleet) {
            $this->actingAs($this->admin)
                ->get(route('report.maintenance-fleet.detail', ['fleetCode' => $fleet->code]))
                ->assertOk();
        }

        $supplier = Supplier::query()->whereNull('deleted_at')->first();
        if ($supplier) {
            $this->actingAs($this->admin)
                ->get(route('report.maintenance-supplier.detail', ['supplierCode' => $supplier->code]))
                ->assertOk();
        }
    }

    public function test_all_eight_datatables_support_listing_and_global_search_without_sql_error(): void
    {
        $fleet = Fleet::query()->whereNull('deleted_at')->firstOrFail();
        $supplier = Supplier::query()->whereNull('deleted_at')->firstOrFail();

        $endpoints = [
            [
                route('dt.maintenance-fleet'),
                ['plateNumber', 'fleetCompanyName', 'fleetCompanyType'],
            ],
            [
                route('dt.maintenance-fleet-detail', ['fleetCode' => $fleet->code]),
                ['code', 'maintenanceDate', 'itemCode', 'itemName', 'supplierName', 'qty', 'price', 'total', 'dueDate', 'paymentStatus'],
            ],
            [
                route('dt.maintenance-supplier'),
                ['supplierName'],
            ],
            [
                route('dt.maintenance-supplier-detail', ['supplierCode' => $supplier->code]),
                ['code', 'maintenanceDate', 'itemCode', 'itemName', 'fleetCompanyName', 'plateNumber', 'qty', 'price', 'total', 'dueDate', 'paymentStatus'],
            ],
            [
                route('dt.report-drivers'),
                ['name', 'joinDate', 'nik', 'sim', 'address', 'phone', 'work_duration'],
            ],
            [
                route('dt.report-freight-rates'),
                ['customerName', 'originName', 'destinationName', 'inputDate', 'price', 'personalVendorPrice', 'vendorName', 'externalVendorPrice'],
            ],
            [
                route('dt.report-item-prices'),
                ['supplierName', 'itemName', 'inputDate', 'price'],
            ],
            [
                route('dt.report-factory-tax'),
                ['invoiceDate', 'npwp', 'customerName', 'orderDateRange', 'materials', 'customerAddress', 'invoiceNumber', 'tonase', 'oa', 'dpp', 'dppLain', 'ppn', 'tax_invoice_number', 'lastPaymentDate', 'withholding_tax_number', 'withholding_tax_amount', 'withholding_tax_date'],
            ],
            [
                route('dt.report-vendor-tax'),
                ['notaDate', 'npwp', 'vendorName', 'orderDate', 'materialName', 'vendorAddress', 'notaNumber', 'tonase', 'oa', 'dpp', 'tax_invoice_number', 'lastPaymentDate', 'withholding_tax_number', 'withholding_tax_amount', 'withholding_tax_date'],
            ],
            [
                route('dt.report-overall'),
                ['order_date', 'plate_number', 'customer_name'],
            ],
        ];

        foreach ($endpoints as [$url, $searchableColumns]) {
            $columns = array_map(fn (string $col) => [
                'data' => $col,
                'name' => $col,
                'searchable' => 'true',
                'orderable' => 'true',
                'search' => ['value' => '', 'regex' => 'false'],
            ], $searchableColumns);

            // 1. Default listing
            $listResp = $this->actingAs($this->admin)
                ->withHeader('X-Requested-With', 'XMLHttpRequest')
                ->getJson($url.'?'.http_build_query([
                    'draw' => 1,
                    'start' => 0,
                    'length' => 5,
                    'search' => ['value' => '', 'regex' => 'false'],
                    'columns' => $columns,
                ]))
                ->assertOk();

            $this->assertArrayNotHasKey('error', $listResp->json(), "SQL error on listing {$url}");

            // 2. Non-existent keyword -> 0 records, no false positive & no SQL error
            $noneResp = $this->actingAs($this->admin)
                ->withHeader('X-Requested-With', 'XMLHttpRequest')
                ->getJson($url.'?'.http_build_query([
                    'draw' => 2,
                    'start' => 0,
                    'length' => 5,
                    'search' => ['value' => 'KEYWORD-TIDAK-MUNGKIN-ADA-99999', 'regex' => 'false'],
                    'columns' => $columns,
                ]))
                ->assertOk();

            $this->assertArrayNotHasKey('error', $noneResp->json(), "SQL error on empty search {$url}");
            $this->assertSame(0, $noneResp->json('recordsFiltered'), "Expected 0 results for non-existent keyword on {$url}");

            // 3. Status/date/numeric search smoke (must not throw SQL error)
            foreach (['Lunas', '2026', '100'] as $term) {
                $termResp = $this->actingAs($this->admin)
                    ->withHeader('X-Requested-With', 'XMLHttpRequest')
                    ->getJson($url.'?'.http_build_query([
                        'draw' => 3,
                        'start' => 0,
                        'length' => 5,
                        'search' => ['value' => $term, 'regex' => 'false'],
                        'columns' => $columns,
                    ]))
                    ->assertOk();

                $this->assertArrayNotHasKey('error', $termResp->json(), "SQL error searching '{$term}' on {$url}");
            }
        }
    }

    public function test_factory_tax_input_and_dpp_lain_ppn_formula(): void
    {
        $invoice = Invoice::query()->whereNull('deleted_at')->firstOrFail();

        $this->actingAs($this->admin)
            ->postJson(route('report.factory-tax.update-tax', ['id' => $invoice->id]), [
                'tax_invoice_number' => '010.001-26.12345678',
                'withholding_tax_number' => 'BPP-FT-001',
                'withholding_tax_amount' => '1.500.000',
                'withholding_tax_date' => '2026-11-27',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.tax_invoice_number', '010.001-26.12345678')
            ->assertJsonPath('data.withholding_tax_number', 'BPP-FT-001')
            ->assertJsonPath('data.withholding_tax_date', '2026-11-27');

        $invoice->refresh();
        $this->assertSame('1500000.00', (string) $invoice->withholding_tax_amount);

        // Verify DPP Lain = DPP * 11/12 & PPN = DPP Lain * 12%
        $dpp = (float) (($invoice->routeAmount + $invoice->onChargeAmount) > 0
            ? ($invoice->routeAmount + $invoice->onChargeAmount)
            : $invoice->invoiceAmount);
        $expectedDppLain = round($dpp * 11 / 12, 2);
        $expectedPpn = round($expectedDppLain * 0.12, 2);

        $row = app(FactoryTaxController::class)
            ->filteredQuery(['customerCode' => null, 'taxStatus' => 'filled', 'startDate' => null, 'endDate' => null])
            ->where('invoice.id', $invoice->id)
            ->toBase()
            ->first();

        $this->assertNotNull($row);
        $this->assertEqualsWithDelta($expectedDppLain, (float) $row->dppLain, 0.01);
        $this->assertEqualsWithDelta($expectedPpn, (float) $row->ppn, 0.01);
    }

    public function test_vendor_tax_input_and_npwp_sim_fields(): void
    {
        $fleetCompany = FleetCompany::query()->whereNull('deleted_at')->firstOrFail();
        $fleetCompany->update(['npwp' => '01.234.567.8-999.000']);
        $this->assertSame('01.234.567.8-999.000', $fleetCompany->fresh()->npwp);

        $driver = Employee::query()->whereNull('deleted_at')->firstOrFail();
        $driver->update(['sim' => 'SIM-B2-998877']);
        $this->assertSame('SIM-B2-998877', $driver->fresh()->sim);

        $vp = VendorPayment::query()->whereNull('deleted_at')->firstOrFail();

        $this->actingAs($this->admin)
            ->postJson(route('report.vendor-tax.update-tax', ['id' => $vp->id]), [
                'tax_invoice_number' => '010.002-26.87654321',
                'withholding_tax_number' => 'BPV-VT-002',
                'withholding_tax_amount' => '750.000',
                'withholding_tax_date' => '2026-11-26',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.tax_invoice_number', '010.002-26.87654321');

        $this->assertSame('750000.00', (string) $vp->fresh()->withholding_tax_amount);
    }

    public function test_overall_report_profit_formula_matches_excel_sheet_examples(): void
    {
        $this->assertSame(-100000.0, OverallReportService::calculateProfit(7840000, 100000, 7840000));
        $this->assertSame(880000.0, OverallReportService::calculateProfit(7840000, 100000, 6860000));
    }

    public function test_all_eight_excel_and_pdf_exports_succeed(): void
    {
        $shortRange = ['startDate' => '2026-11-01', 'endDate' => '2026-11-05'];
        $fleet = Fleet::query()->whereNull('deleted_at')->firstOrFail();
        $supplier = Supplier::query()->whereNull('deleted_at')->firstOrFail();

        $excelUrls = [
            route('report.overall.excel', $shortRange),
            route('report.freight-rates.excel'),
            route('report.item-prices.excel', ['type' => 'part']),
            route('report.factory-tax.excel', $shortRange),
            route('report.vendor-tax.excel', $shortRange),
            route('report.drivers.excel'),
            route('report.maintenance-fleet.excel-maintenance-fleet', $shortRange),
            route('report.maintenance-fleet.excel-maintenance-fleet-detail', array_merge(['fleetCode' => $fleet->code], $shortRange)),
            route('report.maintenance-supplier.excel-maintenance-supplier', $shortRange),
            route('report.maintenance-supplier.excel-maintenance-supplier-detail', array_merge(['supplierCode' => $supplier->code], $shortRange)),
        ];

        foreach ($excelUrls as $url) {
            $resp = $this->actingAs($this->admin)->get($url);
            $resp->assertOk();
            $this->assertInstanceOf(BinaryFileResponse::class, $resp->baseResponse);
        }

        $pdfUrls = [
            route('report.overall.pdf', $shortRange),
            route('report.freight-rates.pdf', $shortRange),
            route('report.item-prices.pdf', array_merge(['type' => 'jasa'], $shortRange)),
            route('report.factory-tax.pdf', $shortRange),
            route('report.vendor-tax.pdf', $shortRange),
            route('report.drivers.pdf'),
            route('report.maintenance-fleet.pdf-maintenance-fleet', $shortRange),
            route('report.maintenance-fleet.pdf-maintenance-fleet-detail', array_merge(['fleetCode' => $fleet->code], $shortRange)),
            route('report.maintenance-supplier.pdf-maintenance-supplier', $shortRange),
            route('report.maintenance-supplier.pdf-maintenance-supplier-detail', array_merge(['supplierCode' => $supplier->code], $shortRange)),
        ];

        foreach ($pdfUrls as $url) {
            $resp = $this->actingAs($this->admin)->get($url);
            $resp->assertOk();
            $this->assertStringStartsWith('%PDF', $resp->getContent());
        }
    }
}
