# Step 04: Hutang Belum Lunas — Rincian Jumlah Pembayaran Masing-Masing Mobil dalam 1 Invoice/PO

- **Plan Utama:** [supplier-dan-inventori-perbaikan.md](../supplier-dan-inventori-perbaikan.md)
- **Prasyarat:** [Step 03](./step-03-hutang-belum-lunas-bugfix-sort-dan-pelunasan-historis.md)
- **Status:** `done`

---

## Tujuan Langkah

Menangani kebutuhan bisnis: **"jika dalam 1 invoice ada barang yang dipakai beberapa mobil maka ada jumlah pembayaran masing² mobil tersebut"**.
Ketika barang dalam satu PO/invoice dipakai oleh lebih dari satu mobil (melalui alokasi `MaintenanceFifo` ke `MaintenanceDetail` -> `Maintenance` -> `Fleet`), sistem menghitung dan menampilkan rincian nominal tagihan, jumlah yang sudah dibayar, dan sisa/alokasi pembayaran untuk **masing-masing mobil** secara akurat di tabel, modal detail pembelian, modal pembayaran batch, serta export Excel & PDF.

---

## Daftar File yang Disentuh

- `[MODIFY]` `app/Services/Purchasing/PurchasePaymentService.php`
- `[MODIFY]` `app/Http/Controllers/Purchasing/PurchasePaymentController.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/index.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/paid.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/partials/detail-modal.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/partials/detail-modal-script.blade.php`
- `[MODIFY]` `app/Exports/PurchasePaymentExport.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/report/purchase-unpaid-pdf.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/report/purchase-paid-pdf.blade.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Tambahkan kalkulasi Breakdown Per Mobil di `PurchasePaymentService`:**
  - Buat method `resolveVehicleBreakdownForPurchases(Collection $purchases): array` (bulk query bebas N+1 untuk DataTables & Export) dan `resolveVehicleBreakdown(Purchase $purchase): array` (untuk single PO / modal detail):
    - Query alokasi dari `purchase_detail` -> `maintenance_fifo` -> `maintenance_detail` -> `maintenance` -> `fleet` (+ `fleet.company` untuk nama pemilik kendaraan), hanya untuk `maintenance.deleted_at IS NULL` dan `maintenance_detail.deleted_at IS NULL`.
    - Untuk setiap `purchase_detail`:
      - Hitung nilai baris pemakaian per mobil: `usedQty * purchase_detail.price`.
      - Jika masih ada sisa kuantitas pada `purchase_detail` yang belum dipakai mobil mana pun (`unassignedQty = max(0, qty - sum(usedQty))`), bila `purchase.fleetCode` terisi maka alokasikan ke mobil `purchase.fleetCode`; bila `purchase.fleetCode` kosong maka kelompokkan sebagai `Stok Gudang (Belum Dipakai)` agar total breakdown selalu 100% rekonsiliasi dengan nilai PO.
    - Jika PO memiliki PPN (`ppn_amount > 0` atau `billing > sum(detail_dpp)`), distribusikan PPN secara **prorata** ke masing-masing porsi mobil (dengan pembulatan selisih Rp 1 ke baris terakhir agar `sum(vehicle.billing) === billingOf($purchase)` persis).
    - Hitung juga proporsi `paid` (`round(paidAmount * vehicle.billing / totalBilling)`) dan `remaining = vehicle.billing - vehicle.paid` untuk tiap mobil.
    - Struktur item breakdown per mobil:
      ```php
      [
          'fleet_code' => 'FMSF...', // null jika Stok Gudang
          'plate_number' => 'BE 8172 AEU', // atau 'Stok Gudang'
          'owner_name' => 'PT PUTRI HOKI LOGISTIK',
          'is_stock' => false,
          'dpp_amount' => 500000,
          'ppn_amount' => 55000,
          'billing_amount' => 555000,
          'paid_amount' => 200000,
          'remaining_amount' => 355000,
          'items' => [
              ['item_code' => '...', 'item_name' => '...', 'qty' => 2, 'price' => 250000, 'subtotal' => 500000],
          ],
      ]
      ```

- [x] **2. Tampilkan jumlah per mobil pada Tabel Hutang Belum Lunas (`index.blade.php`) & Hutang Lunas (`paid.blade.php`):**
  - Pada `PurchasePaymentController::datatableUnpaid()` dan `datatablePaid()`, sertakan array `vehicleBreakdown` per baris PO.
  - Pada render kolom **Mobil**:
    - Jika hanya 1 mobil dan tanpa sisa gudang: tampilkan badge nopol seperti biasa (`BE 8172 AEU`).
    - Jika PO dipakai **beberapa mobil** (atau kombinasi mobil + stok gudang): tampilkan daftar badge per baris lengkap dengan nominalnya:
      - Di **Hutang Belum Lunas**: `BE 8172 AEU · Rp 355.000` (sisa/tagihan per mobil) dan `BE 8307 ADU · Rp 200.000`.
      - Di **Hutang Lunas**: `BE 8172 AEU · Rp 555.000` (jumlah pembayaran per mobil).

- [x] **3. Tampilkan Rincian Pembayaran Per Mobil pada Modal Alokasi Pembayaran Batch (`index.blade.php`):**
  - Simpan `data-vehicle-breakdown` (JSON) pada checkbox baris PO.
  - Di dalam tabel `#batchPaymentAllocationBody` pada modal **Bayar Hutang Supplier**:
    - Ketika suatu PO memiliki lebih dari 1 alokasi mobil, tampilkan daftar rincian di kolom **Mobil** yang otomatis menghitung **jumlah pembayaran masing-masing mobil secara proporsional** saat user mengetik nominal bayar (baik mode *Lunasi Semua* maupun *Nominal Manual* / cicilan DP)!
    - Contoh tampilan *real-time* saat PO Rp 1.000.000 (Mobil A 60%, Mobil B 40%) dibayar Rp 500.000:
      - `BE 8172 AEU`: Bayar **Rp 300.000** (Sisa Rp 300.000)
      - `BE 8307 ADU`: Bayar **Rp 200.000** (Sisa Rp 200.000)

- [x] **4. Tampilkan Tabel Rincian Per Mobil di Modal Detail Pembelian (`partials/detail-modal*.blade.php`):**
  - Perluas `PurchasePaymentService::findPaymentDetail($purchaseCode)` agar menyertakan `'vehicle_breakdown'` dan `'ppn_amount'` / `'subtotal'`.
  - Di `detail-modal.blade.php` & `detail-modal-script.blade.php`, tambahkan seksi **"Rincian Pemakaian & Pembayaran Per Mobil"** berisi tabel: `No`, `Nopol & Pemilik`, `Barang & Qty`, `Tagihan Mobil`, `Sudah Dibayar`, dan `Sisa Hutang`.

- [x] **5. Perbarui Export Excel & PDF Hutang Supplier:**
  - Pada `PurchasePaymentExport.php`, `purchase-unpaid-pdf.blade.php`, dan `purchase-paid-pdf.blade.php`, format kolom **Mobil** agar mencantumkan rincian nominal per mobil bila PO digunakan oleh lebih dari satu mobil (mis. `BE 8172 AEU (Rp 355.000), BE 8307 ADU (Rp 200.000)`).

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Cek syntax PHP & kompilasi Blade pada seluruh file yang disentuh.
- [x] Buat Unit/Feature Test skenario 1 PO senilai Rp 1.000.000 (+ PPN 11% = Rp 1.110.000) yang dipakai 2 mobil melalui `MaintenanceFifo` (Mobil A 60%, Mobil B 40%):
  - Pastikan total breakdown kedua mobil sama persis dengan `Rp 1.110.000` (`Rp 666.000` + `Rp 444.000`).
  - Saat dibayar sebagian (`Rp 555.000`), pastikan rincian pembayaran masing-masing mobil tercatat proporsional (`Rp 333.000` dan `Rp 222.000`) dan tampil di JSON `findPaymentDetail` serta `datatableUnpaid`.
