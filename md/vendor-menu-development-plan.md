# Rencana Pengembangan: Menu Vendor (Pemisahan Vendor Payment)

> Dokumen perencanaan teknis — hasil analisis pola menu **Faktur (Invoice)** yang sudah ada, untuk dijadikan acuan membangun menu **Vendor** baru.
>
> ✅ **STATUS: SELESAI & TERVERIFIKASI** — semua fase (1–5) sudah diterapkan. Lihat bagian 9 untuk keputusan desain final & hasil pengujian. Dokumen bagian 2–7 dipertahankan sebagai catatan perencanaan.

---

## 1. Tujuan

Memecah halaman `Finance → Vendor Payment` (yang sekarang satu halaman untuk semua) menjadi **menu utama sendiri** bernama **Vendor** dengan 3 sub-menu:

```
SEKARANG                          TARGET
─────────────────────────         ─────────────────────────
FINANCE                           VENDOR (menu utama baru)
└── Vendor Payment   ◄── dipecah  ├── Invoice Belum Lunas
    (semua di 1 halaman)              ├── Invoice Lunas
                                      └── Daftar Pembayaran
```

**Kabar baik:** struktur data **tidak perlu diubah sama sekali**. Tabel `vendor_payment` (status per order) dan `vendor_payment_history` (riwayat per pembayaran) sudah cukup mendukung. Yang dibutuhkan hanya pemisahan **tampilan, route, dan menu**.

---

## 2. Struktur Menu Target

| Kode Menu | Parent | Nama (EN) | Nama (ID) | URL | Sort |
|---|---|---|---|---|---|
| `VENDOR` | 0 | Vendor | Vendor | `#` | (setelah Finance) |
| `VENDOR_INV_UNPAID` | `VENDOR` | Vendor Unpaid Invoice | Invoice Belum Lunas | `vendor/invoice/unpaid` | 1 |
| `VENDOR_INV_PAID` | `VENDOR` | Vendor Paid Invoice | Invoice Lunas | `vendor/invoice/paid` | 2 |
| `VENDOR_PAY_LIST` | `VENDOR` | Vendor Payment List | Daftar Pembayaran | `vendor/payment` | 3 |

---

## 3. Pola Acuan: Cara Kerja Menu Faktur

Menu Faktur terdiri dari komponen berikut (semua akan ditiru):

| Komponen | Lokasi di Faktur | Fungsi |
|---|---|---|
| **Data menu** | tabel `menu` (parent `FAKTUR` + 5 anak) + `role_menu` | Sidebar dirender dari DB, bukan hardcode |
| **Route** | `routes/invoice.php` — prefix `invoice`, nama `invoice.*` | `unpaid`, `paid`, `payment` |
| **Controller halaman** | `InvoiceController::indexUnpaid()` / `indexPaid()` | Menghitung statistik KPI lalu render view |
| **Controller datatable** | `datatableUnpaid()` / `datatablePaid()` | Sumber data tabel, difilter status |
| **Controller daftar pembayaran** | `InvoicePaymentController::index()` + `datatable()` | 1 baris = 1 transaksi pembayaran |
| **View** | `invoice/unpaid.blade.php`, `invoice/paid.blade.php`, `invoice/payment/index.blade.php` + `invoice/partials/` | Halaman dengan KPI cards + tabel |
| **Icon sidebar** | `sidebar.blade.php` → `$childIcons` | Icon submenu dipetakan per URL |
| **Redirect legacy** | block `faktur/*` di `routes/invoice.php` | URL lama tetap jalan, dialihkan |

---

## 4. Rincian Pekerjaan

### A. Database — Menu & Permission (migration)

Buat migration yang berisi:

1. **Insert 4 record menu** (tabel `menu`, sesuai tabel di bagian 2):
   - Parent `VENDOR`: isi kolom `icon` dengan feather icon (mis. `truck`) — **wajib diisi**, karena fallback icon di sidebar hanya meng-cover `SETTING` & `FAKTUR`
2. **Copy permission** dari menu lama `VENDOR-PAYMENT`:
   - Ambil semua `role_menu` yang punya `menuCode = 'VENDOR-PAYMENT'`
   - Duplikat untuk `VENDOR`, `VENDOR_INV_UNPAID`, `VENDOR_INV_PAID`, `VENDOR_PAY_LIST`
3. **Nonaktifkan menu lama** `VENDOR-PAYMENT` (soft delete) — dilakukan **paling akhir** setelah redirect siap

> Alternatif tanpa migration: input manual lewat menu `Master → Menu` + assign ulang di Role. Tapi migration lebih ter-version & bisa dijalankan di semua environment.

### B. Route — file baru `routes/vendor.php`

Daftarkan di `routes/web.php` (dalam group `middleware(['auth'])`, di samping `finance.php`).

```php
Route::prefix('vendor')->name('vendor.')->group(function () {
    // Halaman
    Route::get('invoice/unpaid',  [VendorInvoiceController::class, 'indexUnpaid'])->name('invoice.unpaid');
    Route::get('invoice/paid',    [VendorInvoiceController::class, 'indexPaid'])->name('invoice.paid');
    Route::get('payment',         [VendorPaymentListController::class, 'index'])->name('payment.index');

    // Operasi (dipanggil dari halaman unpaid/paid)
    Route::post('invoice/payment',            ...'store');          // bayar (batch)
    Route::post('invoice/generate-nota',      ...'generateNota');
    Route::post('invoice/cancel-nota/{code}', ...'cancelNota');
    Route::delete('invoice/payment/{code}',   ...'destroy');        // batal pembayaran
    Route::get('invoice/pdf/{code}',          ...'pdf');
    Route::post('invoice/pdf-multi',          ...'pdfMulti');
});

// Datatable & ajax terpisah (ikut pola dt.* / ajax.*)
Route::get('datatable/vendor-invoice/unpaid',  ...)->name('dt.vendor-invoice.unpaid');
Route::get('datatable/vendor-invoice/paid',    ...)->name('dt.vendor-invoice.paid');
Route::get('datatable/vendor-payment-list',    ...)->name('dt.vendor-payment-list');
Route::get('ajax/vendor-invoice-detail/{code}', ...)->name('ajax.vendor-invoice-detail');

// Redirect legacy (URL lama → baru)
Route::get('finance/vendor-payment', fn () => redirect()->route('vendor.invoice.unpaid'));
```

Route lama di `routes/finance.php` dipindah/dihapus bertahap.

### C. Controller

**Opsi A (disarankan — rapi):**
- `VendorInvoiceController` (baru) — perpindahan logika dari `VendorPaymentController`:
  - `indexUnpaid()` — statistik + render view unpaid
  - `indexPaid()` — statistik + render view paid
  - `datatableUnpaid()` / `datatablePaid()` — tabel terfilter
  - Operasi dipindah apa adanya: `store` (bayar), `generateNota`, `cancelNota`, `destroy` (batal bayar), `pdf`, `pdfMulti`, `getDetail`
- `VendorPaymentListController` (baru) — `index()` + `datatable()` untuk Daftar Pembayaran

**Opsi B (hemat kerja):** tambahkan method `indexUnpaid/indexPaid/datatableUnpaid/datatablePaid` ke `VendorPaymentController` yang sudah ada (tapi filenya sudah ~700 baris).

> ⚠️ **Perhatian penting:** constructor controller sekarang mengambil judul halaman lewat
> `$menuSvc->getByName('Vendor Payment')`. **Jangan beri nama menu baru yang duplikat** dengan menu faktur (`'Unpaid Invoice'` & `'Paid Invoice'` sudah dipakai `FAKTUR_BELUM_LUNAS`/`FAKTUR_LUNAS` — `getByName()` mengembalikan record pertama yang cocok, bisa salah ambil).
> Solusi aman: pakai `$menuSvc->getByCode('VENDOR_INV_UNPAID')` (kode unik), atau beri nama unik seperti di tabel bagian 2.

### D. Service — `VendorPaymentService`

Tambahkan method query:

| Method | Filter | Untuk |
|---|---|---|
| `findAllUnpaid()` | Order fleet **external** + (belum ada vendor_payment ATAU `payment_status != 'paid'`) | Halaman Belum Lunas |
| `findAllPaid()` | Order fleet external + `payment_status = 'paid'` | Halaman Lunas |
| `findPayments()` | `vendor_payment_history` + relasi `vendorPayment.order.fleet.company`, `userBank.bank` | Daftar Pembayaran |
| `statsUnpaid()` | total order, pending vs partial, total tagihan, terbayar, sisa | KPI unpaid |
| `statsPaid()` | total order lunas, total nominal | KPI paid |
| `statsPayments()` | jumlah transaksi, total nominal, jumlah order/vendor | KPI daftar pembayaran |

Logika `store`, `cancelPayment`, `assignNota`, `cancelNota`, `generateNotaNumber` **tetap dipakai apa adanya** — tidak ada perubahan bisnis.

### E. View — folder baru `resources/views/vendor/`

```
resources/views/vendor/
├── invoice/
│   ├── unpaid.blade.php        ← adaptasi dari index.blade.php sekarang
│   ├── paid.blade.php          ← baru (read-only + cetak + detail)
│   └── partials/
│       ├── modals.blade.php    ← modal bayar, generate nota, detail (pindahan)
│       └── table-style.blade.php
└── payment/
    └── index.blade.php         ← baru (daftar transaksi pembayaran)
```

**1. `invoice/unpaid.blade.php`** — adaptasi dari halaman sekarang:
- Header + 4 KPI cards (ikuti gaya `invoice/unpaid.blade.php`): *Order Belum Lunas* (badge Pending/Partial), *Total Tagihan Vendor*, *Sudah Terbayar*, *Sisa Harus Dibayar*
- Tabel: kolom sama seperti sekarang (aksi, no nota, tgl, nopol, vendor, driver, shipment, customer, asal-tujuan, tagihan, terbayar, sisa, status bayar, status order)
- Tombol **Generate Nota / Bayar Terpilih / Cetak Terpilih** — aturan aktif-mati & sinkronisasi checkbox per nota **tetap sama**
- Modal bayar, modal nota, modal detail — pindahan dari sekarang
- Aksi baris: **Batal Nota** (order punya nota, belum dibayar) & **Batal Pembayaran** (order partial)

**2. `invoice/paid.blade.php`** — baru:
- 2–4 KPI cards: *Order Lunas*, *Total Dibayar*
- Tabel read-only: kolom sama, tanpa checkbox bayar (opsional tetap ada checkbox untuk cetak)
- Aksi baris: **Detail** 👁️, **Cetak Nota**, **Batal Pembayaran** ❌ (order lunas → kembali ke belum lunas)
- Tombol "Lihat Invoice Belum Lunas" (link silang, seperti paid-nya faktur)

**3. `payment/index.blade.php`** — baru:
- KPI cards: *Jumlah Transaksi*, *Total Nominal*, *Order Terlibat*
- Tabel **1 baris = 1 pembayaran** (dari `vendor_payment_history`):

| Kolom | Sumber |
|---|---|
| Tanggal Bayar | `payment_date` |
| Kode Transaksi (batch) | `vendor_payment.code` |
| No Nota | `vendor_payment.nota_number` |
| Order / Vendor / Nopol | lewat relasi order → fleet.company |
| Nominal | `amount` |
| Bank Sumber Dana | `user_bank_code` → nama bank |
| Keterangan | `description` |

- Opsional (nice-to-have, ikuti `InvoicePaymentController`): filter rentang tanggal/bank/vendor, tombol **Export PDF & Excel**
- Aksi: detail (buka modal detail order terkait)

### F. Sidebar — icon submenu

Tambahkan di `$childIcons` (`resources/views/layouts/sidebar.blade.php`):

```php
// Vendor
'vendor/invoice/unpaid' => 'mdi-cash-remove',
'vendor/invoice/paid'   => 'mdi-cash-check',
'vendor/payment'        => 'mdi-credit-card-outline',
```

Hapus mapping lama `'finance/vendor-payment'` setelah menu lama dinonaktifkan.

### G. Redirect & Cleanup

1. URL lama `finance/vendor-payment` → redirect ke `vendor/invoice/unpaid` (pola sama seperti `faktur/*` → `invoice/*`)
2. Soft delete menu `VENDOR-PAYMENT` + hapus baris finance/vendor-payment di `$childIcons`
3. Pastikan tidak ada lagi view/route yang memakai nama route `finance.vendor-payment.*`

---

## 5. Pemetaan Fitur Lama → Lokasi Baru

| Fitur di halaman lama | Lokasi baru |
|---|---|
| Tabel semua order external | Terbagi: **Belum Lunas** (pending+partial) / **Lunas** |
| Generate Nota | **Invoice Belum Lunas** |
| Bayar Order Terpilih (lunas/DP) | **Invoice Belum Lunas** |
| Cetak Terpilih (PDF) | **Belum Lunas** & **Lunas** |
| Detail + riwayat pembayaran per order | Modal detail di **kedua halaman invoice** |
| Batal Nota | **Invoice Belum Lunas** (aksi baris) |
| Batal Pembayaran | **Belum Lunas** (partial) & **Lunas** (paid) |
| Riwayat pembayaran global | **Daftar Pembayaran** (baru) |

---

## 6. Skenario Uji (Testing Checklist)

- [x] Order external baru (tanpa nota) → muncul di **Belum Lunas**, bisa generate nota
- [x] Bayar DP → nota tetap di **Belum Lunas**, status **Partial**, KPI terbayar/sisa berubah
- [x] Pelunasan → nota pindah ke **Lunas**, status order ikut *Paid*
- [x] Batal pembayaran nota lunas → nota kembali ke **Belum Lunas**, saldo bank kembali (termasuk mutasi seluruh batch DP + pelunasan)
- [x] Batal nota (belum dibayar) → nota hilang, order kembali ke tabel *Order Menunggu Nota*
- [x] **Daftar Pembayaran** menampilkan semua transaksi (tanggal, batch, nota, nominal, bank)
- [x] Cetak PDF single & multi masih jalan (template Pribadi/WTMS/PHL)
- [x] Role yang dulu punya akses Vendor Payment → otomatis punya akses 4 menu baru
- [x] URL lama `finance/vendor-payment` → redirect ke halaman baru
- [x] Sidebar: menu Vendor muncul dengan icon, submenu aktif saat halaman dibuka
- [x] User tanpa permission → menu tidak muncul & akses ditolak
- [x] Validasi gagal digabung: beda vendor / beda format perusahaan → ditolak dengan pesan jelas

---

## 7. Urutan Pengerjaan (Fase)

| Fase | Isi | Estimasi |
|---|---|---|
| **1. Backend** | `VendorPaymentService` (query unpaid/paid/payments + stats), 2 controller baru, `routes/vendor.php` | ± 1 hari |
| **2. Frontend** | 3 view + partials + 3 datatable + JS (pindahan + penyesuaian nama route) | ± 1–2 hari |
| **3. Menu & Permission** | Migration menu + role_menu + icon sidebar | ± 0.5 hari |
| **4. Redirect & Cleanup** | Legacy redirect, hapus route/menu lama | ± 0.5 hari |
| **5. Testing & Polish** | Checklist di bagian 6 | ± 0.5–1 hari |

**Total estimasi: ± 3–5 hari kerja.**

---

## 8. Keputusan Desain (FINAL — sudah dikonfirmasi user & diterapkan)

1. **"Invoice" vendor = per NOTA (grup), bukan per order.** ✅
   - Halaman Belum Lunas menampilkan **2 tabel**: (a) *Order Menunggu Nota* (belum punya nota), (b) *Nota Belum Lunas* (satu baris = satu nota hasil agregasi status order di dalamnya).
   - Status nota = agregat: semua order `paid` → **paid**; ada `paid_amount > 0` → **partial**; sisanya **pending**.
   - Checkbox baris nota mewakili seluruh order dalam nota (bayar/cetak selalu per nota penuh).
2. **Pembayaran tetap dari halaman Invoice Belum Lunas.** ✅ — Tidak dibuat menu pembayaran terpisah; *Daftar Pembayaran* bersifat riwayat (read-only).
3. **Judul halaman diambil via `getByCode()`** (kode unik `VENDOR_INV_UNPAID`, dsb.) — bukan `getByName()`, untuk menghindari bentrok nama dengan menu faktur.
4. **Menu lama `VENDOR-PAYMENT` dihapus + URL lama di-redirect** ke `vendor/invoice/unpaid` demi transisi user.
5. **Cetak nota = per nota (1 PDF = 1 nota), tidak pernah digabung.** ✅
   - Penggabungan beberapa order ke dalam satu nota **hanya** terjadi saat *generate nota* (menu Order Menunggu Nota).
   - Halaman Invoice Belum Lunas tidak menyediakan cetak gabungan antar nota: tombol **Cetak Nota** aktif hanya saat **satu** nota dipilih, dan ada tombol cetak per baris nota (`vendor.invoice.pdf-nota`) yang mencetak satu nota utuh (seluruh order di dalam nomor nota tersebut).

### Keputusan turunan penting (bugfix menyertai migrasi)

- **`vendor_payment_history.batch_code`** (kolom baru): setiap transaksi pembayaran kini menyimpan kode batch pembayarannya sendiri. Dulu, `cancelPayment()` menghapus mutasi bank berdasarkan `vendor_payment.code` (batch **terakhir** yang menyentuh order) — padahal saldo dikembalikan **penuh** (semua history). Akibatnya batal pembayaran setelah DP + pelunasan hanya menghapus mutasi batch terakhir, mutasi DP tertinggal di buku bank. Sekarang mutasi dihapus per `history.batch_code` sehingga seluruh mutasi tiap transaksi ikut terhapus. **Sudah diuji E2E.**
- `vendor_payment.code` tetap menyimpan batch **terakhir** (informasi "kode transaksi terbaru").

---

## 9. Hasil Implementasi & Verifikasi

### Perubahan lanjutan (revisi): Order Menunggu Nota jadi sub-menu sendiri

Setelah implementasi awal (waiting table + nota tabel di satu halaman), tabel *Order Menunggu Nota* dipisah ke sub-menu tersendiri agar urutan kerja lebih jelas:

```
VENDOR
1. VENDOR_ORDER_WAITING  - Order Menunggu Nota  (vendor/order/waiting)  ← generate nota di sini
2. VENDOR_INV_UNPAID     - Invoice Belum Lunas  (vendor/invoice/unpaid)  ← bayar di sini
3. VENDOR_INV_PAID       - Invoice Lunas        (vendor/invoice/paid)
4. VENDOR_PAY_LIST       - Daftar Pembayaran    (vendor/payment)
```

Perubahan yang menyertai:

- Migration `2026_10_03_100000_split_vendor_waiting_order_menu.php` — insert menu `VENDOR_ORDER_WAITING` (sort 1), geser sort 3 menu lain, copy permission dari role yang punya `VENDOR_INV_UNPAID`
- Route baru `vendor.order.waiting` (`vendor/order/waiting`) di `routes/vendor.php`
- `VendorInvoiceController::indexWaiting()` + view baru `resources/views/vendor/order/waiting.blade.php` (tabel waiting + tombol Generate Nota + modal nota)
- `generateNota()` (sukses & gagal validasi) redirect ke `vendor.order.waiting`
- `statsWaiting()` baru (waitingCount, totalBilling, vendorCount); `statsUnpaid()` kini nota-only (tambah `orderCount`, hapus komponen waiting)
- Halaman `unpaid.blade.php` kehilangan tabel waiting & tombol Generate Nota; kartu KPI "Order Menunggu Nota" diganti "Total Order"; ada link bolak-balik antar halaman
- Ikon sidebar `vendor/order/waiting` = `mdi-tray-full-outline`

### Struktur akhir

| Komponen | Lokasi |
|---|---|
| Migration menu + kolom `batch_code` | `database/migrations/2026_10_02_100000_create_vendor_menu_structure.php` (sudah di-migrate) |
| Migration pemisahan menu waiting | `database/migrations/2026_10_03_100000_split_vendor_waiting_order_menu.php` (sudah di-migrate) |
| Service | `app/Services/Finance/VendorPaymentService.php` |
| Controller invoice | `app/Http/Controllers/Finance/VendorInvoiceController.php` |
| Controller daftar pembayaran | `app/Http/Controllers/Finance/VendorPaymentListController.php` |
| Routes | `routes/vendor.php` (baru) + redirect legacy di `routes/finance.php` |
| Views | `resources/views/vendor/order/waiting.blade.php`, `vendor/invoice/{unpaid,paid}.blade.php`, `vendor/invoice/partials/`, `vendor/payment/index.blade.php` |
| Dihapus | `VendorPaymentController`, `finance/vendor-payment/index.blade.php` (folder `pdf/` dipertahankan — masih dipakai) |

### Verifikasi yang sudah lolos

- `route:list` lengkap (`vendor.order.waiting`, `vendor.invoice.*`, `vendor.payment.index`, `dt.*`, `ajax.*`, redirect legacy 302)
- DB: menu `VENDOR` + 4 anak (sort 1–4) ada, permission ter-copy (2 role), menu lama terhapus, kolom `batch_code` ada
- 4 halaman render HTTP 200 (login user admin/SPRADMIN); 4 endpoint datatable + ajax detail OK dengan data nyata (94 waiting / 3 unpaid / 72 paid / 132 transaksi)
- **Uji E2E alur tulis (dalam DB transaction, di-rollback): 35/35 PASS**
  - generateNota → 2 vendor_payment pending + nomor nota `PREFIX/00010/2026`
  - DP 30% → partial, history ber-`batch_code`, LiveMutation & mutasi tercatat
  - Pelunasan → paid, 2 batch mutasi terpisah, status order = 6
  - cancelNota ditolak saat sudah dibayar (pesan jelas)
  - cancelPayment → pending kembali, **semua mutasi kedua batch terhapus**, saldo LiveMutation kembali 100%
  - cancelNota setelahnya → sukses, order kembali ke waiting list
- **Uji E2E pemisahan menu waiting: 13/13 PASS**
  - `generateNota()` redirect ke `vendor/order/waiting` + flash sukses
  - Campuran format perusahaan ditolak (flash fail, tanpa record)
  - Campuran fleet company ditolak (flash fail, tanpa record)
  - Halaman waiting menampilkan notifikasi nomor nota; unpaid tetap render normal
- Sintaks: 4 blade compile OK; JS inline waiting & unpaid lolos `node --check`
