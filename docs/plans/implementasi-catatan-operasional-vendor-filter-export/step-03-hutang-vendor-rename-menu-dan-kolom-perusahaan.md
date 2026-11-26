# Step 03: Hutang Vendor — Rename Menu "Order Menunggu Faktur" & Ganti "Format" -> "Perusahaan"

- **Plan Utama:** [implementasi-catatan-operasional-vendor-filter-export.md](../implementasi-catatan-operasional-vendor-filter-export.md)
- **Prasyarat:** Step 02 selesai
- **Status:** `done`

---

## Tujuan Langkah

1. Mengganti nama menu pertama di **Hutang (Vendor)** (`VENDOR_ORDER_WAITING` / `vendor/order/waiting`) dari **"Order Menunggu Faktur"** / **"Order Menunggu Nota"** menjadi **"Faktur yg Belum Dibayar"** (sementara menu ke-2 `VENDOR_INV_UNPAID` tetap **"Faktur Vendor Belum Lunas"**).
2. Mengganti kolom, label, ringkasan modal, dan pesan validasi **"Format"** menjadi **"Perusahaan"** pada halaman `vendor/order/waiting` dan modal Generate Nota, dengan menampilkan nama perusahaan customer (`PT PUTRI HOKI LOGISTIK`, `PT WIJAYA TRANS MAKMUR SEJAHTERA`, `PRIBADI`) sambil tetap menjaga prefix nomor nota (`PHL`/`WTMS`/`P`) dan template PDF di backend.

---

## Daftar File yang Disentuh

- `[NEW]` `database/migrations/2026_11_25_000001_rename_vendor_waiting_menu_to_unpaid_invoice.php`
- `[MODIFY]` `database/seeders/MenuSeeder.php`
- `[MODIFY]` `app/Http/Controllers/Finance/VendorInvoiceController.php`
- `[MODIFY]` `app/Services/Finance/VendorPaymentService.php`
- `[MODIFY]` `resources/views/vendor/order/waiting.blade.php`
- `[MODIFY]` `resources/views/vendor/invoice/partials/modals.blade.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Migration & Seeder Rename Menu `VENDOR_ORDER_WAITING`:**
  - Buat migration baru yang mengupdate tabel `menu` untuk `code = 'VENDOR_ORDER_WAITING'`:
    - `nama` -> `'Faktur yg Belum Dibayar'`
    - `name` -> `'Unpaid Invoice'` (atau `'Invoice Not Yet Paid'`)
    - Sediakan method `down()` untuk mengembalikan ke `'Order Menunggu Faktur'` / `'Order Awaiting Invoice'`.
  - Perbarui baris `VENDOR_ORDER_WAITING` di `database/seeders/MenuSeeder.php` (baris 100) agar sinkron untuk fresh seed.

- [x] **2. Perbarui Judul & Label di `VendorInvoiceController.php` dan `waiting.blade.php`:**
  - Di `VendorInvoiceController::indexWaiting()` (baris 73): ubah fallback `'Order Menunggu Nota'` menjadi `'Faktur yg Belum Dibayar'`.
  - Di `resources/views/vendor/order/waiting.blade.php`:
    - Breadcrumb `firstSegment` -> `'Hutang (Vendor)'`, `secondSegment` -> `'Faktur yg Belum Dibayar'`.
    - Deskripsi header, KPI Card 1 (`Faktur yg Belum Dibayar`), header tabel, dan `infoEmpty` DataTable diselaraskan (serta hapus teks yang membatasi "armada eksternal" saja karena sudah mendukung antar PT Internal).

- [x] **3. Ganti Kolom & Label "Format" -> "Perusahaan":**
  - Di `VendorInvoiceController::datatableWaiting()`:
    - Tambahkan/ubah kolom `companyName` (atau kembalikan nama perusahaan pada `companyFormat` + tetap simpan `data-order-format` dan `data-customer-company-name` di checkbox):
      - Nilai yang ditampilkan di kolom tabel: `$row->customer->company->name ?? '-'` (dengan badge warna pembeda berdasarkan format `PHL`/`WTMS`/`P` agar tetap mudah dipindai).
    - Ikut sertakan `$row->customer->company->name` dalam pencarian global (`$searchableValues`).
  - Di `resources/views/vendor/order/waiting.blade.php`:
    - Header `<th>` kolom ke-10 (baris 164): ubah `Format` -> `Perusahaan`.
    - Placeholder pencarian DataTable (baris 500): ganti `format` -> `perusahaan`.
    - Render kolom di DataTable: tampilkan nama perusahaan (`PT PUTRI HOKI LOGISTIK`, `PT WIJAYA TRANS MAKMUR SEJAHTERA`, `PRIBADI`) alih-alih kode `PHL`/`WTMS`.
    - Validasi JS saat klik **Generate Nota** (baris 590–599): ubah pesan error menjadi `"Gagal: Order yang dipilih memiliki perusahaan yang berbeda (...). Semua order dalam satu nota harus memiliki perusahaan yang sama."`
    - Ringkasan modal (`#notaOrderFormat`): tampilkan `notaOrders[0].customerCompanyName || '-'`.
  - Di `resources/views/vendor/invoice/partials/modals.blade.php` (baris 794):
    - Ubah label tile `<i class="mdi mdi-tag-outline me-1"></i>Format` menjadi `<i class="mdi mdi-domain me-1"></i>Perusahaan`.
  - Di `VendorPaymentService::assignNota()` (baris 1028):
    - Sesuaikan pesan error validasi agar menyebut **perusahaan** (bukan *format perusahaan*).

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Jalankan migration & cek hasil di DB:
  ```bash
  php artisan migrate
  php artisan tinker --execute="print_r(DB::table('menu')->where('parentCode', 'VENDOR')->orderBy('sort')->get(['code','name','nama','url'])->toArray());"
  ```
- [x] Lint PHP & kompilasi Blade:
  ```bash
  php -l app/Http/Controllers/Finance/VendorInvoiceController.php && php -l app/Services/Finance/VendorPaymentService.php
  ```
- [ ] Cek visual di halaman `vendor/order/waiting`: sidebar menampilkan **Faktur yg Belum Dibayar**, tabel menampilkan kolom **Perusahaan** berisi nama perusahaan customer, dan modal Generate Nota menampilkan tile **Perusahaan**.
