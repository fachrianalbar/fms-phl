# Step 02: Pembelian & Pembelian Langsung — Auto Jatuh Tempo, Kolom PPN & Setelah PPN, Tombol Show, dan Nopol Pemilik Kendaraan

- **Plan Utama:** [supplier-dan-inventori-perbaikan.md](../supplier-dan-inventori-perbaikan.md)
- **Prasyarat:** [Step 01](./step-01-data-supplier-periode-jatuh-tempo.md)
- **Status:** `done`

---

## Tujuan Langkah

Menyempurnakan modul **Pembelian (`purchasing/purchase`)** dan **Pembelian Langsung (`purchasing/direct-purchase`)** dengan empat fitur terintegrasi:
1. **Jatuh tempo otomatis (`dueDate`)** dari `date + supplier.payment_term_days` saat memilih Supplier atau mengubah Tanggal Pembelian.
2. **Kolom PPN & Jumlah Setelah PPN** yang otomatis mengikuti tarif `supplier.ppn` (dapat diedit di form), menghitung `subtotal` (DPP), `ppn_rate`, `ppn_amount`, dan menyimpan `nominal = subtotal + ppn_amount` sebagai dasar tagihan Hutang Supplier.
3. **Tombol Show** yang selalu muncul di daftar Pembelian (baik yang belum dibayar maupun sudah lunas), lengkap dengan tampilan rincian berstandar PHL.
4. **Auto-fill Nama Pemilik Kendaraan** ketika Nopol (`fleetCode`) dipilih — opsional di Pembelian (PO) dan wajib di Pembelian Langsung.

---

## Daftar File yang Disentuh

- `[NEW]` `database/migrations/2026_11_26_131000_add_ppn_columns_to_purchase_table.php`
- `[MODIFY]` `app/Models/Purchasing/Purchase.php`
- `[MODIFY]` `app/Services/Purchasing/PurchaseService.php`
- `[MODIFY]` `app/Services/Purchasing/DirectPurchaseService.php`
- `[MODIFY]` `app/Http/Controllers/Purchasing/PurchaseController.php`
- `[MODIFY]` `app/Http/Controllers/Purchasing/DirectPurchaseController.php`
- `[MODIFY]` `resources/views/purchasing/purchase/index.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase/create.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase/edit.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase/show.blade.php`
- `[MODIFY]` `resources/views/purchasing/direct-purchase/index.blade.php`
- `[MODIFY]` `resources/views/purchasing/direct-purchase/create.blade.php`
- `[MODIFY]` `resources/views/purchasing/direct-purchase/edit.blade.php`
- `[MODIFY]` `resources/views/purchasing/direct-purchase/show.blade.php`
- `[MODIFY]` `app/Exports/PurchaseExport.php`
- `[MODIFY]` `app/Exports/DirectPurchaseExport.php`
- `[MODIFY]` `resources/views/purchasing/purchase/report/purchase-pdf.blade.php`
- `[MODIFY]` `resources/views/purchasing/direct-purchase/report/direct-purchase-pdf.blade.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Migrasi kolom PPN pada tabel `purchase` & update Model `Purchase`:**
  - Tambahkan kolom di tabel `purchase`:
    - `subtotal` (`decimal(18, 2)->default(0)->after('paymentCode')`) — total sebelum PPN (DPP).
    - `ppn_rate` (`decimal(5, 2)->default(0)->after('subtotal')`) — persentase PPN (mis. `11.00`).
    - `ppn_amount` (`decimal(18, 2)->default(0)->after('ppn_rate')`) — nilai rupiah PPN.
  - Lakukan backfill aman pada migration `up()` untuk data existing: `UPDATE purchase SET subtotal = COALESCE(nominal, 0), ppn_rate = 0, ppn_amount = 0 WHERE subtotal = 0 AND nominal IS NOT NULL`.
  - Tambahkan `'subtotal'`, `'ppn_rate'`, `'ppn_amount'` ke `$fillable` pada `App\Models\Purchasing\Purchase`.

- [x] **2. Kalkulasi PPN, Jatuh Tempo & `fleetCode` di `PurchaseService` dan `DirectPurchaseService`:**
  - Hitung `subtotal = sum(qty * price)` dari detail item.
  - Ambil `ppnRate = max(0, (float) ($request->input('ppn_rate') ?? 0))`.
  - Hitung `ppnAmount = round($subtotal * ($ppnRate / 100))` dan `grandTotal = $subtotal + $ppnAmount`.
  - Simpan `subtotal`, `ppn_rate`, `ppn_amount`, dan `nominal = $grandTotal` (Jumlah Setelah PPN).
  - Pada `DirectPurchaseService`: pastikan `paidAmount` (bila Tunai/Transfer) dan `maintenance.grand_total` juga selaras menggunakan `$grandTotal` (atau menyimpan rincian subtotal + PPN dengan konsisten).
  - Pada `PurchaseService`: simpan `fleetCode` (nullable) dari `$request->input('fleetCode') ?: null`, dan bila `dueDate` kosong saat simpan, hitung fallback `Carbon::parse($request->date)->addDays((int) ($supplier->payment_term_days ?? 0))->toDateString()`.
  - Eager-load `'fleet.company'` pada `PurchaseService::datatable()`, `PurchaseService::getById()`, `DirectPurchaseService::datatable()`, dan `DirectPurchaseService::getById()`.

- [x] **3. Form Create & Edit di Pembelian (`purchase/create`, `purchase/edit`) dan Pembelian Langsung (`direct-purchase/create`, `direct-purchase/edit`):**
  - **Atribut data pada `<option>` Supplier:** sertakan `data-payment-term="{{ (int) ($s->payment_term_days ?? 0) }}"` dan `data-ppn="{{ (float) ($s->ppn ?? 0) }}"`.
  - **Auto Jatuh Tempo (`dueDate`):**
    - Saat user memilih Supplier atau mengubah Tanggal Pembelian (`date`), hitung otomatis `dueDate = date + payment_term_days` hari.
    - Di form Edit, jangan menimpa `dueDate` tersimpan saat halaman pertama kali dibuka; hanya hitung ulang otomatis ketika user mengubah Supplier atau Tanggal Pembelian (kecuali `dueDate` diubah manual setelahnya).
  - **Input PPN (%) & Summary Panel:**
    - Tambahkan input `ppn_rate` (`name="ppn_rate"`, step `0.01`, min `0`, max `100`) yang otomatis terisi dari `data-ppn` Supplier saat Supplier dipilih (tetap dapat diubah manual, mis. jadi `0` bila transaksi non-PPN).
    - Pada Summary Panel bawah tabel item, tampilkan secara *real-time*:
      - **Subtotal (Sebelum PPN)**
      - **PPN (`X%` — `Rp ...`)**
      - **Jumlah Setelah PPN (Grand Total)**
  - **Pilihan Nopol (`fleetCode`) & Auto Nama Pemilik Kendaraan:**
    - Di `PurchaseController::create` & `edit`: kirim `$fleet = $this->fleetSvc->findAll()` (yang sudah memuat relasi `company`).
    - Di `purchase/create` & `edit`: tambahkan dropdown opsional **Nopol / Armada** (`name="fleetCode"`) + input readonly **Pemilik Kendaraan** di sebelahnya.
    - Di `direct-purchase/create` & `edit`: lengkapi `<option>` pada `#fleetCode` dengan `data-owner="{{ $f->company->name ?? '-' }}"` + tambahkan input readonly **Pemilik Kendaraan** yang langsung terisi saat Nopol dipilih maupun saat halaman Edit dimuat.

- [x] **4. Tabel Index, Tombol Show & Modal/Halaman Detail:**
  - **Tabel Pembelian (`purchasing/purchase/index.blade.php` & `PurchaseController::datatable`):**
    - Ubah kolom aksi agar **Tombol Show (`mdi-eye`, `bg-info-subtle`) selalu tampil** untuk semua baris (membuka modal ringkas detail PO / halaman `show`), berdampingan dengan tombol **Edit** (`mdi-pencil-outline`, tampil selama `paidAmount == 0 && status != 3`) dan **Delete**.
    - Tampilkan kolom: `Subtotal`, `PPN`, dan `Jumlah Setelah PPN` (menggantikan satu kolom `Total` tunggal), serta kolom `Nopol / Pemilik` bila ada.
    - Lengkapi global search DataTables sesuai `datatable-search-standard` (mencakup kode PO, supplier, nopol, pemilik kendaraan, tanggal, status, dan angka nominal/PPN).
  - **Tabel Pembelian Langsung (`purchasing/direct-purchase/index.blade.php` & `DirectPurchaseController`):**
    - Tampilkan Nama Pemilik Kendaraan di bawah badge Armada (atau kolom tersendiri) serta tampilkan kolom `Subtotal`, `PPN`, dan `Jumlah Setelah PPN`.
    - Lengkapi JSON `DirectPurchaseController::show()` dan modal `#detailModal` serta `direct-purchase/show.blade.php` & `purchase/show.blade.php` agar menampilkan **Pemilik Kendaraan**, **Subtotal**, **PPN (`ppn_rate%`)**, dan **Jumlah Setelah PPN**.

- [x] **5. Perbarui Export Excel & PDF Pembelian dan Pembelian Langsung:**
  - `PurchaseExport.php` & `purchase-pdf.blade.php`: tambahkan kolom `Nopol`, `Pemilik Kendaraan`, `Subtotal`, `PPN`, dan `Jumlah Setelah PPN`.
  - `DirectPurchaseExport.php` & `direct-purchase-pdf.blade.php`: tambahkan kolom `Pemilik Kendaraan`, `Subtotal`, `PPN`, dan `Jumlah Setelah PPN`.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Jalankan migrasi & lint seluruh file PHP:
  ```bash
  php artisan migrate
  php -l app/Models/Purchasing/Purchase.php app/Services/Purchasing/PurchaseService.php app/Services/Purchasing/DirectPurchaseService.php app/Http/Controllers/Purchasing/PurchaseController.php app/Http/Controllers/Purchasing/DirectPurchaseController.php app/Exports/PurchaseExport.php app/Exports/DirectPurchaseExport.php
  ```
- [x] Uji kompilasi seluruh Blade `purchasing/purchase/*` dan `purchasing/direct-purchase/*`.
- [x] Verifikasi alur:
  1. Pilih supplier dengan `payment_term_days = 14` dan `ppn = 11` pada tanggal `2026-11-01` -> `dueDate` otomatis `2026-11-15`, `ppn_rate` otomatis `11`, `nominal` tersimpan `subtotal + 11%`.
  2. Pilih Nopol -> field `Pemilik Kendaraan` otomatis menampilkan `fleet.company.name`.
  3. Tombol Show (`mdi-eye`) muncul pada seluruh baris di daftar Pembelian maupun Pembelian Langsung dan menampilkan rincian lengkap.
