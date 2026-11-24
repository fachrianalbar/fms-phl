# Step 1: Sentralisasi Perhitungan di `ProfitLossService`

> **Referensi Plan:** [../profit-loss-report-fix.md](../profit-loss-report-fix.md)  
> **Prasyarat:** Tidak ada  
> **Status:** `done`

## Tujuan Langkah
Menjadikan `ProfitLossService` sebagai satu sumber kebenaran (*single source of truth*) untuk seluruh perhitungan Laba Rugi (Penjualan Dasar berbasis vendor price, Biaya Order lengkap, Biaya Perawatan, Pajak PPh proporsional dari Invoice & Pembayaran Langsung, serta Laba Bersih), lengkap dengan penjagaan null-safety agar tidak ada lagi `Attempt to read property ... on null`.

## Daftar File yang Disentuh
- `[MODIFY]` `app/Services/Report/ProfitLossService.php`

## Action Checklist & Detail Perubahan

- [x] Tambahkan helper `resolveOrderVendorSales(Order $order): float`:
  - Ambil `(float) ($order->vendorPrice ?? 0)` dan `(float) ($order->personalVendorPrice ?? 0)`.
  - Return `$vendorPrice > 0 ? $vendorPrice : $personalVendorPrice`.
- [x] Tambahkan helper `resolveOrderTax(Order $order): float`:
  - Jika `$order->invoiceDetail?->invoice` tersedia:
    - Ambil `$invoicePph = (float) ($invoice->pphAmount ?? 0)`.
    - Jika `$invoicePph <= 0`, return `0.0`.
    - Jika `$invoice->details->count() <= 1`, return `$invoicePph`.
    - Untuk invoice multi-order, hitung bobot tiap detail sesuai `$invoice->pphBaseType === 'route' ? 'route' : 'subtotal'` (untuk `'subtotal'`, jumlahkan `routeAmount` + nominal `cost` bertipe `'on charge'`).
    - Return `$totalWeight > 0 ? round($invoicePph * ($orderWeight / $totalWeight), 2) : 0.0`.
  - Jika tidak ada invoice, cek `$order->orderPayment`: return `(float) ($order->orderPayment->pph ?? 0)`.
  - Default return `0.0`.
- [x] Tambahkan helper `calculateOrderAllowance(Order $order): float`:
  - Iterasi `$order->route?->routeDetail ?? []` dengan null-safe `$itemRoute->costComponent?->type`.
  - Untuk tipe `'Allowance'` atau `'Allowance Office'`, tambahkan `$itemRoute->amount` (jika `!= 0`) dan `($order->route?->price ?? 0) * ($itemRoute->percentage / 100)` (jika `percentage` terisi).
- [x] Tambahkan helper `calculateFleetSummary(Fleet $fleet, ?Collection $tonaseBonuses = null): array`:
  - Gunakan cache collection `TonaseBonus` agar tidak query berulang di dalam loop.
  - Hitung `basicSales`, `basicAllowance`, `additionalCost`, `tonase`, `orderCost` (`basicAllowance + additionalCost + tonase`), `maintenance` (gunakan `$details->item?->price ?? 0`), `tax`, dan `netProfit` (`basicSales - orderCost - maintenance - tax`).
- [x] Tambahkan relasi eager-loading standar (`fleetReportRelations(?string $startDate, ?string $endDate): array`) untuk dipakai seragam oleh `ProfitLossController::datatable()`, `show()`, `getProfitLossSummary()`, dan `ProfitLossReport`:
  - `company`, `orders.route.routeDetail.costComponent`, `orders.cost`, `orders.invoiceDetail.invoice.details.order.cost`, `orders.orderPayment`, `maintenances.details.item`.
- [x] Perbarui `datatableOrder($fleetCode)` agar turut eager-load `cost` dan `route.routeDetail.costComponent`.

## Kriteria Selesai & Verifikasi Langkah
- Jalankan `php -l app/Services/Report/ProfitLossService.php` — tidak ada syntax error.
- Seluruh method baru memiliki return type jelas dan null-safe terhadap `route`, `costComponent`, `item`, `invoiceDetail`, dan `orderPayment`.
