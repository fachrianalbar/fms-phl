# Step 01: Service Kalkulasi Order Detail & Unit Test

- **Plan Utama:** [order-detail-report-omset-komisi-gaji-pajak.md](../order-detail-report-omset-komisi-gaji-pajak.md)
- **Prasyarat:** Tidak ada
- **Status:** `done`

---

## Tujuan Langkah

Memusatkan seluruh query terfilter dan logika perhitungan baris **Order Detail Report** ke satu service terpadu `App\Services\Report\OrderDetailReportService` agar DataTable Web, Export Excel, dan Export PDF menggunakan rumus yang 100% identik tanpa duplikasi maupun *N+1 query*, dilengkapi Unit Test untuk seluruh skenario bisnis.

---

## Daftar File yang Disentuh

- `[NEW]` `app/Services/Report/OrderDetailReportService.php`
- `[NEW]` `tests/Unit/OrderDetailReportCalculationTest.php`

---

## Action Checklist & Detail Perubahan

- [ ] **1. Buat `app/Services/Report/OrderDetailReportService.php`:**
  - Inject `VendorPaymentService` dan `ProfitLossService` melalui constructor.
  - Sediakan `buildFilteredQuery(Request $request): Builder`:
    - Query `Order::whereIn('status', [4, 5])` dengan eager-load lengkap:
      - `'fleet.type'`, `'fleet.company'`, `'customer.company'`, `'driver'`, `'route.destinationLocation'`, `'route.originLocation'`, `'material'`, `'unit'`, `'cost.costComponent'`, `'invoiceDetail.invoice.details.order.cost'`, `'orderPayment'`.
    - Terapkan filter `plateNumber`, `driverName`, `customerName`, `fleetTypeName`, `shipmentNumber`, `origin`, `destination`, `orderTypeCode`, serta rentang `orderDate` (`startDate` s/d `endDate`) menggunakan `FilterHelper::applyFilters()`.
  - Sediakan `isCrossInternalCompany(Order $order): bool`:
    ```php
    public function isCrossInternalCompany(Order $order): bool
    {
        $fleetCompany = $order->fleet?->company;
        if (! $fleetCompany) {
            return false;
        }

        $type = strtolower(trim((string) ($fleetCompany->type ?? '')));
        if ($type !== 'internal') {
            return false;
        }

        $fleetParentCode = trim((string) ($fleetCompany->companyCode ?? ''));
        $customerCompanyCode = trim((string) ($order->customer?->companyCode ?? ($order->customer?->company?->code ?? '')));

        return $fleetParentCode !== '' && $customerCompanyCode !== '' && $fleetParentCode !== $customerCompanyCode;
    }
    ```
  - Sediakan `resolveOmsetAmounts(Order $order): array` yang mengembalikan `['qty' => float, 'unit_price' => float, 'omset' => float, 'is_cross_internal' => bool]`:
    - Jika `isCrossInternalCompany($order)` bernilai `true`: gunakan `$this->vendorPaymentService->resolveOrderVendorLineAmounts($order)` (`unit_price` & `subtotal`).
    - Jika `false`: `omset = (float) ($order->routeAmount ?? 0)`, `unit_price = (float) ($order->price ?? 0)` (fallback `omset / qty` bila `qty > 0`, atau `route->price`).
  - Sediakan `classifyOrderCosts(Order $order): array`:
    - Iterasi `$order->cost ?? []`:
      - Jika `strtolower(trim((string) $cost->type)) === 'on charge'` -> masuk ke `incomeDetails` & `incomeTotal`.
      - Selain `on charge`:
        - Ambil nama komponen via `$cost->componentName ?: ($cost->costComponent?->name ?? 'N/A')` dan tipe master `strtolower(trim((string) ($cost->costComponent?->type ?? '')))`.
        - **Komisi:** `$masterType === 'commission' || str_contains(mb_strtolower($name), 'komisi')` -> masuk ke `commissionDetails` & `commissionTotal`.
        - **Gaji:** `! $isCommission && ($masterType === 'salary' || str_contains(mb_strtolower($name), 'gaji'))` -> masuk ke `salaryDetails` & `salaryTotal`.
        - **Biaya Lainnya:** masuk ke `otherCostDetails` & `otherCostTotal`.
    - Hitung `totalCost = $otherCostTotal + $commissionTotal + $salaryTotal`.
  - Sediakan `calculateOrderBreakdown(Order $order, array &$invoiceWeightCache = []): array` & `prepareExportRows(iterable $orders): array`:
    - Hitung:
      - `gross_profit = $omset + $incomeTotal - $totalCost`
      - `tax = $this->profitLossService->resolveOrderTax($order, $invoiceWeightCache)`
      - `net_profit = $gross_profit - $tax`
    - `prepareExportRows()` mengubah koleksi `Order` menjadi array ringan siap render untuk cache chunk Excel maupun template PDF/Excel, plus helper `computeGrandTotals(array $rows): array`.

- [ ] **2. Buat Unit Test `tests/Unit/OrderDetailReportCalculationTest.php`:**
  - Uji order **Internal Sama PT** (`PHL` ke `PHL`): memakai `price` & `routeAmount`.
  - Uji order **Internal Beda PT** (`PHL` customer + `WTMS` fleet): memakai `personalVendorPriceSingle` & `personalVendorPrice` (serta preferensi `vendorPrice` jika `> 0`).
  - Uji order **Armada External**: tetap memakai `price` & `routeAmount` sebagai omset tagihan customer.
  - Uji pemisahan `On Charge`, `Komisi`, `Gaji`, dan `Biaya Lainnya`, serta pastikan `total_cost = other + commission + salary`.
  - Uji perhitungan `gross_profit`, `tax` (baik dari single/multi-order `Invoice` maupun `OrderPayment`), dan `net_profit = gross_profit - tax`.

---

## Kriteria Selesai & Verifikasi Langkah

- [ ] Lint syntax PHP:
  ```bash
  php -l app/Services/Report/OrderDetailReportService.php && php -l tests/Unit/OrderDetailReportCalculationTest.php
  ```
- [ ] Jalankan unit test:
  ```bash
  php artisan test --filter=OrderDetailReportCalculationTest
  ```
