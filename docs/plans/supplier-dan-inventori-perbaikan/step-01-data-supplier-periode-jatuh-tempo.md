# Step 01: Data Supplier — Tambahkan Periode Jatuh Tempo Pembayaran

- **Plan Utama:** [supplier-dan-inventori-perbaikan.md](../supplier-dan-inventori-perbaikan.md)
- **Prasyarat:** Tidak ada
- **Status:** `done`

---

## Tujuan Langkah

Menambahkan pengaturan **Periode Jatuh Tempo Pembayaran** (dalam satuan hari, mis. `0`, `7`, `14`, `30`, `45`, `60` hari) pada master **Data Supplier** (`inventory/supplier`), lengkap pada database, model, validasi, tabel DataTables (termasuk pencarian global), modal tambah/edit supplier, serta export Excel & PDF. Nilai ini akan menjadi acuan pengisian otomatis tanggal jatuh tempo di form Pembelian & Pembelian Langsung pada Step 02.

---

## Daftar File yang Disentuh

- `[NEW]` `database/migrations/2026_11_26_130000_add_payment_term_days_to_supplier_table.php`
- `[MODIFY]` `app/Models/Inventory/Supplier.php`
- `[MODIFY]` `app/Http/Controllers/Inventory/SupplierController.php`
- `[MODIFY]` `resources/views/inventory/supplier/index.blade.php`
- `[MODIFY]` `app/Exports/SupplierExport.php`
- `[MODIFY]` `resources/views/inventory/supplier/report/supplier-pdf.blade.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Buat migration penambahan kolom `payment_term_days` pada tabel `supplier`:**
  - Tambah kolom `payment_term_days` bertipe `unsignedSmallInteger()->default(0)->after('pph')` (nilai `0` berarti jatuh tempo pada hari yang sama / tunai jika tidak diatur).
  - Sediakan `down()` yang menghapus kolom bila ada (`Schema::hasColumn`).

- [x] **2. Perbarui model `App\Models\Inventory\Supplier`:**
  - Tambahkan `'payment_term_days'` ke `$fillable` dan cast `'payment_term_days' => 'integer'`.

- [x] **3. Perbarui `SupplierController` (`store`, `update`, `datatable`):**
  - Tambahkan validasi `'payment_term_days' => ['nullable', 'integer', 'min:0', 'max:3650']` pada `store()` dan `update()`, dengan normalisasi default `0` bila kosong.
  - Pada `datatable()`:
    - Tambahkan kolom tampilan `paymentTermHtml` atau `editColumn('payment_term_days')` yang merender mis. `30 Hari` (atau `0 Hari` / `-` jika `0`).
    - Sertakan pencarian angka hari pada global search `filter(...)` (`CAST(payment_term_days AS CHAR) LIKE ?`) sesuai standar `datatable-search-standard`.
    - Tambahkan atribut `data-payment-term-days="{{ (int) ($row->payment_term_days ?? 0) }}"` pada tombol `.js-edit-supplier`.

- [x] **4. Perbarui tampilan & modal CRUD `resources/views/inventory/supplier/index.blade.php`:**
  - Tambahkan kolom `Jatuh Tempo` (`payment_term_days`) pada `<thead>` dan konfigurasi `columns` DataTables (`searchable: true`, `orderable: true`).
  - Pada modal `#supplierCrudModal`, tambahkan input:
    ```html
    <div class="col-md-4">
        <label class="form-label" for="supplierPaymentTermInput">Periode Jatuh Tempo (Hari)</label>
        <div class="input-group">
            <input class="form-control" name="payment_term_days" id="supplierPaymentTermInput"
                type="number" min="0" max="3650" step="1" value="0" placeholder="cth: 30">
            <span class="input-group-text">Hari</span>
        </div>
    </div>
    ```
    (Sesuaikan grid PPN, PPH, dan Periode Jatuh Tempo menjadi 3 kolom `col-md-4` yang rapi di light & dark mode).
  - Pastikan script reset modal Tambah & populasi modal Edit mengisi `#supplierPaymentTermInput` dari `$(this).data('payment-term-days')`.

- [x] **5. Perbarui Export Excel & PDF Data Supplier:**
  - `app/Exports/SupplierExport.php`: tambahkan heading `'Jatuh Tempo (Hari)'` dan mapping `(int) ($supplier->payment_term_days ?? 0) . ' Hari'`.
  - `resources/views/inventory/supplier/report/supplier-pdf.blade.php`: tambahkan kolom `Jatuh Tempo` agar selaras dengan tampilan web.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Jalankan migrasi & cek syntax:
  ```bash
  php artisan migrate
  php -l app/Models/Inventory/Supplier.php app/Http/Controllers/Inventory/SupplierController.php app/Exports/SupplierExport.php
  ```
- [x] Uji kompilasi Blade `inventory/supplier/index.blade.php` dan `supplier-pdf.blade.php`.
- [x] Pastikan simpan/ubah supplier dengan `payment_term_days = 30` tersimpan di DB, tampil di DataTable, bisa dicari lewat kotak search global, dan muncul di Excel/PDF.
