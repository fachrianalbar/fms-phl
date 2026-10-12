---
name: relasi-fleet-company-ke-company
description: "Panduan mengaitkan data FleetCompany (tipe Internal) ke model Company di sistem PHL. Mencakup migrasi, model, service, controller, view, dan validasi."
---

# Skill: Menghubungkan FleetCompany ke Company (Tipe Internal)

## Konteks & Asumsi

*   **`FleetCompany` (Perusahaan Armada):** Merupakan entitas mandiri dalam sistem ini untuk merepresentasikan pemilik atau operator kendaraan. Memiliki kolom `type` yang bisa berupa `Internal` atau `External`.
*   **`Company` (Perusahaan Induk/Utama):** Merupakan entitas untuk merepresentasikan perusahaan induk dari sistem ERP ini (misalnya, 'PT PUTRI HOKI LOGISTIK' atau 'PT WIJAYA TRANS MAKMUR SEJAHTERA'). Ini adalah entitas orang tua.
*   **Tujuan:** Entitas `FleetCompany` dengan `type` = `Internal` harus memiliki relasi ke satu `Company`. Entitas `External` tidak memerlukan relasi ini.

**Catatan Penting:** Nama entitas `FleetCompany` yang berisi kata "Company" dan relasinya ke model `Company` secara linguistik dapat menimbulkan kebingungan. Selalu gunakan istilah yang jelas untuk membedakan:
*   "**Perusahaan Armada**" atau "**Fleet Company**" untuk `FleetCompany`.
*   "**Perusahaan Induk**" atau "**Company**" untuk `Company`.

## Langkah Implementasi

### 1. Persiapan Database (`database/migrations/`)

*   **Tambahkan Kolom `companyCode`:**
    *   Buat migration baru untuk menambahkan kolom `companyCode` (UUID string) ke tabel `fleet_company`.
    *   Definisikan *foreign key* ke kolom `code` pada tabel `company`.
    *   Gunakan aturan `onUpdate('cascade')` dan `nullOnDelete()` untuk menjaga integritas referensial.

```php
// Contoh dalam migration
Schema::table('fleet_company', function (Blueprint $table) {
    $table->string('companyCode', 36)->nullable()->after('type');

    $table->foreign('companyCode')
        ->references('code')
        ->on('company')
        ->onUpdate('cascade')
        ->nullOnDelete();
});
```

*   **Migrasi Ulang Data (Opsional tapi Direkomendasikan):**
    *   Jika ada data lama, buat *seeder* untuk mengisi kembali kolom `companyCode`.
    *   *Seeder* harus mencocokkan `FleetCompany.name` dengan `Company.name`. Jika cocok dan tipe FleetCompany adalah `Internal`, perbarui `companyCode`-nya dengan kode `Company` yang sesuai.

### 2. Model (`app/Models/Master/`)

*   **Model `FleetCompany.php`:**
    *   Tambahkan `'companyCode'` ke dalam properti `$fillable`.
    *   Definisikan relasi *belongsTo* ke `Company`.

```php
// Di dalam class FleetCompany
protected $fillable = [
    // ... kolom lainnya
    'companyCode', // <-- Tambahkan ini
    // ...
];

public function company()
{
    // Relasi ke Perusahaan Induk
    return $this->belongsTo(Company::class, 'companyCode', 'code');
}
```

### 3. Service (`app/Services/Master/FleetCompanyService.php`)

*   Pastikan metode `store` dan `update` menerima dan menyimpan nilai `companyCode` dari request.
*   Perbarui metode `getById` untuk secara (*eager load*) memuat relasi `company`. Ini penting untuk efisiensi dan untuk menghindari masalah *N+1 query* di sisi view detail.

```php
// Di dalam FleetCompanyService
public function store($request, $title)
{
    $data = $this->service->create([
        'name' => $request->name,
        'type' => $request->type,
        'companyCode' => $request->companyCode, // <-- Pastikan ini ada
        // ... kolom lainnya
    ]);
    // ...
}

public function getById($id)
{
    // Eager load relasi company
    return $this->service->where('id', $id)->with('company')->first();
}
```

### 4. Controller (`app/Http/Controllers/Master/FleetCompanyController.php`)

*   **Metode `index()`:**
    *   Ambil daftar semua `Company` untuk mengisi dropdown di formulir.
    *   Kirimkan ke view sebagai variabel (misalnya, `$companyList`).

```php
public function index()
{
    $companies = $this->service->findAll();
    $companyList = Company::orderBy('name')->get(); // <-- Ambil daftar Company

    return view($this->view.'index')
        ->with('view', $this->view)
        ->with('companies', $companies)
        ->with('companyList', $companyList) // <-- Kirim ke view
        ->with('title', $this->title);
}
```

*   **Metode `store()` & `update()`:**
    *   Tambahkan aturan validasi untuk `companyCode`.
    *   Gunakan `required_if:type,Internal` untuk membuatnya wajib hanya saat tipe adalah "Internal".
    *   Gunakan `nullable` untuk kasus tipe "External".
    *   Gunakan aturan `exists:company,code` untuk memastikan kode perusahaan valid.

```php
// Dalam Validator::make di store() dan update()
$validator = Validator::make($request->all(), [
    'name' => 'required|string|max:255',
    'type' => 'required|in:Internal,External',
    'companyCode' => 'required_if:type,Internal|nullable|string|max:36|exists:company,code', // <-- Aturan baru
    // ... aturan validasi lainnya
]);
```

*   **Metode `datatable()`:**
    *   Ketika membangun tombol aksi (misalnya, tombol "Edit"), sertakan atribut data `data-company-code` dari model `$row->companyCode`. Ini memungkinkan JavaScript di sisi klien untuk mengisi kembali nilai dropdown perusahaan saat memuat formulir edit.

### 5. View (Blade Template)

*   **Formulir Input (`index.blade.php` - Modal CRUD):**
    *   Tambahkan elemen `<select>` untuk `companyCode` di dalam modal formulir.
    *   Isi opsi dengan data dari variabel `$companyList`.
    *   Inisialisasi Select2 pada elemen `<select>` ini untuk pengalaman pengguna yang konsisten.
    *   Gunakan JavaScript untuk mengontrol visibilitas elemen `<select>`:
        *   Sembunyikan secara default jika tipe adalah "External".
        *   Tampilkan hanya ketika tipe dipilih sebagai "Internal".
        *   Isi nilainya otomatis saat membuka modal dalam mode "Edit" menggunakan data atribut dari tombol.

*   **Halaman Detail (`show.blade.php`):**
    *   Di bagian informasi inti, tampilkan nama `Company` yang terkait jika relasinya ada.
    *   Gunakan relasi yang sudah di-*eager load* (`$company->company->name`).
    *   Buat nama perusahaan induk sebagai tautan ke halaman detailnya (`route('company.show', $company->company->code)`) untuk navigasi yang intuitif.

## Validasi & Pengujian

Setelah mengimplementasikan semua langkah di atas:

1.  **Periksa Sintaks:** Jalankan `php -l` pada semua file PHP yang dimodifikasi untuk memastikan tidak ada kesalahan sintaks.
2.  **Kompilasi Blade:** Gunakan `php artisan tinker` dan `$compiler = app('blade.compiler'); $compiler->compileString(file_get_contents('path/to/view.blade.php'));` untuk memastikan file Blade tidak memiliki kesalahan.
3.  **Uji Relasi dengan Tinker:**
    *   Gunakan `php artisan tinker` untuk memverifikasi relasi bekerja. Contoh:

```php
$fc = App\Models\Master\FleetCompany::with('company')->where('type', 'Internal')->first();
echo $fc->company->name; // Harus mencetak nama Company yang sesuai
```

4.  **Pengujian Manual:** Buka halaman `master/fleet-company`, coba buat/edit entitas dengan tipe "Internal" dan "External", pastikan dropdown perusahaan muncul/hilang sesuai, dan data tersimpan dengan benar di database.

## Dampak & Hubungan dengan Modul Lain

### Berinteraksi dengan Modul `Order`, `Not Return DO`, dan `Return DO` (Harga)

Relasi ini memiliki dampak langsung pada perhitungan harga beli (vendor price) di seluruh modul operasional:
1. **Order** (`OrderService`, `OrderController`, `order/create`, `order/edit`)
2. **Not Return DO (DO Belum Kembali)** (`NotReturnDoService`, `NotReturnDoController`, `not-return-do/edit-order`)
3. **Return DO (DO Sudah Kembali)** (`ReturnDoController`, `return-do/edit-order`, yang memakai `NotReturnDoService::updateOrder`)
4. **AJAX Price Endpoint** (`OrderController::calculateOrderPrice` / `ajax.order-calculate-price`)

Oleh karena itu, memahami rantai berikut sangat penting:

1.  **Order** terhubung ke **Customer**.
2.  **Customer** dapat memiliki relasi ke sebuah **Company** (`c.companyCode`).
3.  **Order** juga terhubung langsung ke sebuah **Kendaraan/Fleet**.
4.  **Kendaraan/Fleet** memiliki relasi ke sebuah **FleetCompany** (`f.fleetCompanyCode`).
5.  **FleetCompany** (jika bertipe `Internal`) punya relasi ke sebuah **Company** (`fc.companyCode`).

### Riwayat & Perbandingan: Aturan Lama vs Aturan Baru

Agar selalu konsisten dan tidak kehilangan konteks historis sistem:

#### A. Aturan Lama (Data Historis Sebelum Oktober 2026 & Fallback Default):
Sebelum aturan baru diperkenalkan, sistem tidak memperhitungkan kesamaan entitas `Company`:
1. **Armada Internal (Semua):**
   * Apapun perusahaannya (baik customer dan armada bernaung di induk yang sama maupun tidak), seluruh biaya vendor internal masuk ke kolom **`personalVendorPrice`**.
   * Dihitung dari master rute: `personalVendorPrice = route.personalVendorPrice * qty`.
   * Kolom `vendorPrice` selalu `0.0`.
2. **Armada Eksternal:**
   * Biaya vendor masuk ke kolom **`vendorPrice`**.
   * Dihitung dari tabel tarif vendor: `vendorPrice = route_price_external.amount * qty`.
   * Kolom `personalVendorPrice` selalu `0.0`.

#### B. Aturan Baru (Berlaku untuk Order Mulai 1 Oktober 2026):
Memperkenalkan relasi `FleetCompany (Internal) -> Company` dan pencocokan dengan `Customer -> Company`:
1. **Jika Tanggal Order Mulai 1 Oktober 2026 (`orderDate >= '2026-10-01'`) DAN Perusahaan Customer SAMA dengan Perusahaan Armada (`isSameCompany = true`):**
   * Harga beli order mengikuti harga jual/tagihan rute customer: `vendorPrice = routeAmount`.
   * `vendorPriceSingle = (qty > 0) ? (routeAmount / qty) : route.price`.
   * `personalVendorPrice = 0.0`.
2. **Jika Tanggal Order Sebelum 1 Oktober 2026 (`orderDate < '2026-10-01'`), ATAU Perusahaan BERBEDA, ATAU Belum Berelasi:**
   * Tetap menggunakan **Aturan Lama** sebagai fallback:
     * Jika armada `Internal`: `personalVendorPrice = route.personalVendorPrice * qty` dan `vendorPrice = 0.0`.
     * Jika armada `External`: `vendorPrice = route_price_external.amount * qty` dan `personalVendorPrice = 0.0`.

**Aturan Final Penetapan Harga:**

*   Harga **Jual** (`price`, `routeAmount`) pada sebuah order selalu dihitung dari master harga **rute** (`price * qty`).
*   Harga **Beli** (`vendorPrice`, `personalVendorPrice`) dievaluasi dengan 3 kondisi:
    1.  **Kondisi 1 — Perusahaan Sama (`isSameCompany` = true):**
        *   Terjadi jika `Customer.companyCode` sama dengan `FleetCompany.companyCode` (tipe armada `Internal`).
        *   `vendorPrice = routeAmount` (mengikuti total harga jual customer).
        *   `vendorPriceSingle = (qty > 0) ? (routeAmount / qty) : route.price`.
        *   `personalVendorPrice = 0.0`.
        *   `personalVendorPriceSingle = 0.0`.
        *   Label tampilan di form: **Vendor Price**.
    2.  **Kondisi 2 — Armada Eksternal (`isExternal` = true):**
        *   Terjadi jika tipe `FleetCompany` armada adalah `External`.
        *   `vendorPriceSingle` diambil dari master `route_price_external`.
        *   `vendorPrice = vendorPriceSingle * qty`.
        *   `personalVendorPrice = 0.0`.
        *   `personalVendorPriceSingle = 0.0`.
        *   Label tampilan di form: **Vendor Price**.
    3.  **Kondisi 3 — Armada Internal Berbeda Perusahaan / Tanpa Relasi (`isSameCompany` = false):**
        *   Terjadi jika armada bertipe `Internal`, namun perusahaan customer berbeda dengan perusahaan pemilik kendaraan, atau perusahaan belum ditautkan.
        *   `vendorPrice = 0.0`.
        *   `vendorPriceSingle = 0.0`.
        *   `personalVendorPriceSingle` diambil dari `route.personalVendorPrice`.
        *   `personalVendorPrice = personalVendorPriceSingle * qty`.
        *   Label tampilan di form: **Personal Vendor Price**.

### 6. Migrasi Skema & Sinkronisasi Data Historis
- Migration skema: `2026_10_11_203112_add_company_code_to_fleet_company_table.php` menambahkan kolom `companyCode` pada tabel `fleet_company`.
- Migration data: `2026_10_11_203115_update_vendor_order_prices_for_internal_company_orders.php` yang berjalan setelah `203112`. Migration ini membatasi pembaruan harga order khusus untuk periode bulan Oktober 2026 (`whereBetween('o.orderDate', ['2026-10-01 00:00:00', '2026-10-31 23:59:59'])`).
- Pengecekan ketidaksesuaian harga master di `OrderService::checkRoutePriceMismatch()` telah diselaraskan dengan aturan baru agar tidak menimbulkan false positive mismatch.