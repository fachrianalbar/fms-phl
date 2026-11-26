# Step 02: Hutang Vendor — Izinkan Faktur/Nota & Pembayaran Antar PT Internal

- **Plan Utama:** [implementasi-catatan-operasional-vendor-filter-export.md](../implementasi-catatan-operasional-vendor-filter-export.md)
- **Prasyarat:** Step 01 selesai
- **Status:** `done`

---

## Tujuan Langkah

Mengizinkan modul **Hutang (Vendor)** memproses order **antar PT Internal** (armada bertipe `Internal` yang Perusahaan Induk-nya `fleet_company.companyCode` **berbeda** dengan Perusahaan Customer `customer.companyCode`, misalnya Customer milik *PT PUTRI HOKI LOGISTIK* diangkut armada *PT WIJAYA TRANS MAKMUR SEJAHTERA*), mulai dari daftar tunggu nota, generate nota, sinkronisasi nominal, cetak PDF nota, hingga pembayaran & pembatalan.

---

## Daftar File yang Disentuh

- `[MODIFY]` `app/Services/Finance/VendorPaymentService.php`
- `[MODIFY]` `app/Http/Controllers/Finance/VendorInvoiceController.php`
- `[NEW]` `tests/Feature/VendorInternalCompanyInvoiceTest.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Tambahkan helper kelayakan order di `VendorPaymentService.php`:**
  - Definisikan helper `isEligibleVendorOrder($order): bool`:
    ```php
    public function isEligibleVendorOrder($order): bool
    {
        if (! $order || ! $order->fleet || ! $order->fleet->company) {
            return false;
        }

        $fleetCompany = $order->fleet->company;
        $type = strtolower(trim((string) ($fleetCompany->type ?? '')));

        if ($type === 'external') {
            return true;
        }

        if ($type === 'internal') {
            $fleetParentCode = trim((string) ($fleetCompany->companyCode ?? ''));
            $customerCompanyCode = trim((string) ($order->customer?->companyCode ?? ($order->customer?->company?->code ?? '')));

            return $fleetParentCode !== '' && $customerCompanyCode !== '' && $fleetParentCode !== $customerCompanyCode;
        }

        return false;
    }
    ```
  - Definisikan scope query `applyEligibleVendorOrderScope($query)` untuk dipakai di `findAll()` dan `findWaitingOrders()`:
    - Cocok jika `fleet.company.type = 'external'` **ATAU** (`fleet.company.type = 'internal'` DAN `fleet.company.companyCode IS NOT NULL` DAN `customer.companyCode IS NOT NULL` DAN `fleet.company.companyCode != customer.companyCode`).

- [x] **2. Perbarui `vendorBaseAmount()` di `VendorPaymentService.php`** (baris 61–66):
  - Jika `$vendorPayment` sudah ada, gunakan `(float) ($vendorPayment->amount ?? 0)`.
  - Jika belum ada `$vendorPayment`, cek `(float) ($order->vendorPrice ?? 0) > 0` terlebih dahulu; bila `<= 0` (kasus antar PT Internal), fallback ke `(float) ($order->personalVendorPrice ?? 0)`.

- [x] **3. Perbarui validasi di `assignNota()` dan `store()` pada `VendorPaymentService.php`:**
  - Di `assignNota()` (baris 999–1014): eager load `customer.company`, `fleet.company.company`, lalu ganti pengecekan `!== 'external'` menjadi `! $this->isEligibleVendorOrder($order)` dengan pesan:
    `"Nota hanya dapat dibuat untuk order vendor eksternal atau antar PT internal yang berbeda perusahaan."`
  - Di `store()` (baris 479–498): eager load `order.fleet.company.company` dan `order.customer.company`, lalu ganti pengecekan `$companyType !== 'external'` menjadi `! $this->isEligibleVendorOrder($vendorPayment->order)`.

- [x] **4. Perbarui perhitungan subtotal PDF Nota di `VendorInvoiceController::buildNotaPdf()`** (baris 838–848):
  - Saat menghitung `$subtotal` & `$unitPrice` tiap order di PDF nota:
    - Jika `(float) ($order->vendorPrice ?? 0) > 0`, gunakan `vendorPrice` & `vendorPriceSingle`.
    - Jika `<= 0` (order antar PT Internal), gunakan `personalVendorPrice` & `personalVendorPriceSingle` sebelum fallback ke `route`.
  - Pastikan juga `VendorPaymentService::buildNotaSyncPlan()` menggunakan `vendorBillingAmount($order)` yang sudah memakai `vendorBaseAmount()` baru agar sinkronisasi nominal antar PT Internal konsisten.

- [x] **5. Buat Feature Test `tests/Feature/VendorInternalCompanyInvoiceTest.php`:**
  - Test order Internal **beda PT** (`PHL` customer + `WTMS` fleet) -> muncul di `findWaitingOrders()`, bisa `assignNota()`, dan bisa dibayar via `store()`.
  - Test order Internal **sama PT** (`PHL` customer + `PHL` fleet) -> **tidak** muncul di `findWaitingOrders()` dan ditolak saat `assignNota()`.
  - Test order `External` -> tetap berjalan normal tanpa regresi.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Lint PHP:
  ```bash
  php -l app/Services/Finance/VendorPaymentService.php && php -l app/Http/Controllers/Finance/VendorInvoiceController.php
  ```
- [x] Jalankan test:
  ```bash
  php artisan test --filter=VendorInternalCompanyInvoiceTest
  ```
