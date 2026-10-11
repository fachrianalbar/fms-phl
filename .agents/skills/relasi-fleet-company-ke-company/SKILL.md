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

### Berinteraksi dengan Modul `Order` (Harga)

Relasi ini memiliki dampak langsung pada perhitungan harga di modul `Order`. Logika bisnis di dalam `OrderService`'s metode `buildOrderData()` menggunakan tipe `FleetCompany` yang terkait dengan `Kendaraan/Fleet` yang dipilih untuk sebuah order untuk menentukan cara menghitung harga beli (vendor price).

Oleh karena itu, memahami rantai berikut sangat penting:

1.  **Order** terhubung ke **Customer**.
2.  **Customer** dapat memiliki relasi ke sebuah **Company** (perusahaan induk pelanggan).
3.  **Order** juga terhubung langsung ke sebuah **Kendaraan/Fleet**.
4.  **Kendaraan/Fleet** memiliki relasi ke sebuah **FleetCompany**.
5.  **FleetCompany** (jika bertipe `Internal`) punya relasi ke sebuah **Company** (perusahaan induk fleet company).

**Poin Penting tentang Harga:**

*   Harga **Jual** (`price`, `routeAmount`) pada sebuah order dihitung dari master harga **rute** dan tidak secara langsung bergantung pada hubungan antara `Customer.Company` dan `FleetCompany.Company`. Orang tua mana pun yang tidak memengaruhi harga jual.
*   Harga **Beli** (`vendorPrice`, `personalVendorPrice`) sangat dipengaruhi oleh tipe `FleetCompany` dari kendaraan yang digunakan:
    *   Jika kendaraan berasal dari **FleetCompany eksternal**, biaya vendor dihitung dari tabel `route_price_external`. Kolom `vendorPrice` dan `vendorPriceSingle` akan terisi.
    *   Jika kendaraan berasal dari **FleetCompany internal**, biaya vendor dihitung dari kolom `personalVendorPrice` pada master rute. Kolom `personalVendorPrice` dan `personalVendorPriceSingle` akan terisi, sementara `vendorPrice` akan nol.

**Kesimpulan:** Ini memungkinkan untuk ada sebuah order dimana `Customer`-nya milik `Company A` (mis., 'PT WIJAYA TRANS MAKMUR SEJAHTERA'), tetapi kendaraannya berasal dari `FleetCompany` yang berbeda yang berada di bawah naungan `Company B` (mis., 'PT PUTRI HOKI LOGISTIK'). Dalam hal ini, harga jual order akan tetap sama (berdasarkan rute), tetapi biaya beli dan jenis kolom harga beli yang terisi akan berbeda, mencerminkan kepemilikan fleet company yang berbeda.