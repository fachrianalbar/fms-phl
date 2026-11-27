# Step 05: Inventori Perawatan — Auto-Fill Nama Pemilik Kendaraan saat Pilih Nopol

- **Plan Utama:** [supplier-dan-inventori-perbaikan.md](../supplier-dan-inventori-perbaikan.md)
- **Prasyarat:** [Step 04](./step-04-hutang-belum-lunas-rincian-pembayaran-per-mobil.md)
- **Status:** `done`

---

## Tujuan Langkah

Memenuhi kebutuhan pada menu **Inventori -> Perawatan (`warehouse/maintenance`)**: **"ketika pilih nopol otomatis nama pemilik kendaraan muncul"**.
Saat user memilih Nopol armada (`fleetCode`) di halaman Tambah maupun Edit Perawatan, sistem langsung menampilkan **Nama Pemilik Kendaraan** (`fleet.company.name`) secara otomatis, serta menampilkannya secara konsisten di daftar tabel Perawatan, modal Detail Perawatan, dan Export Excel/PDF.

---

## Daftar File yang Disentuh

- `[MODIFY]` `app/Services/Warehouse/MaintenanceService.php`
- `[MODIFY]` `app/Http/Controllers/Warehouse/MaintenanceController.php`
- `[MODIFY]` `resources/views/warehouse/maintenance/create.blade.php`
- `[MODIFY]` `resources/views/warehouse/maintenance/edit.blade.php`
- `[MODIFY]` `resources/views/warehouse/maintenance/index.blade.php`
- `[MODIFY]` `app/Exports/MaintenanceExport.php`
- `[MODIFY]` `resources/views/warehouse/maintenance/report/maintenance-pdf.blade.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Eager-load relasi `fleet.company` di Backend Perawatan:**
  - `MaintenanceService::datatable()` & `findAll()`: ubah `'fleet'` menjadi `'fleet.company'`.
  - `MaintenanceController::show()`: ubah `$data->load(['fleet.company', 'warehouse', 'details.item', 'purchases.supplier'])`.
  - `MaintenanceController::filteredQuery()` (untuk PDF & Excel): muat `'fleet.company'`.

- [x] **2. Auto-Fill Nama Pemilik Kendaraan di Form Create (`warehouse/maintenance/create.blade.php`) & Edit (`edit.blade.php`):**
  - Ubah baris pilihan Armada dari `col-md-12` menjadi dua kolom berdampingan (`col-md-6` untuk **Armada / Plat Nomor** dan `col-md-6` untuk **Nama Pemilik Kendaraan** readonly):
    ```blade
    <div class="col-md-6">
        <label class="form-label-custom" for="fleetCode">
            <i class="mdi mdi-truck-outline text-muted"></i>
            {{ __('menu_maintenance.fleet') }} / Plat Nomor <span class="required-star">*</span>
        </label>
        <select class="js-example-basic-single form-select" name="fleetCode" id="fleetCode" required>
            <option selected disabled value="">{{ __('general.choose') }} Armada...</option>
            @foreach ($fleet as $item)
                <option value="{{ $item->code }}"
                    data-owner="{{ $item->company->name ?? '-' }}"
                    data-owner-type="{{ $item->company->type ?? '' }}">
                    {{ $item->plateNumber }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label-custom" for="fleetOwnerDisplay">
            <i class="mdi mdi-account-tie-outline text-muted"></i>
            Nama Pemilik Kendaraan
        </label>
        <input class="form-control form-control-custom fw-semibold" type="text"
            id="fleetOwnerDisplay" readonly placeholder="Otomatis muncul saat pilih Nopol..." value="-">
    </div>
    ```
  - Tambahkan handler JS pada event `change` Select2 `#fleetCode` (dan panggil sekali saat `$(document).ready()` khususnya di halaman `edit` agar pemilik kendaraan armada yang sudah tersimpan langsung tampil):
    ```javascript
    function syncFleetOwner() {
        const selected = $('#fleetCode').find('option:selected');
        const owner = selected.val() ? (selected.attr('data-owner') || '-') : '';
        $('#fleetOwnerDisplay').val(owner || '-');
    }
    $('#fleetCode').on('change', syncFleetOwner);
    syncFleetOwner();
    ```
  - Pastikan styling input readonly mendukung light mode & dark mode (`html[data-bs-theme="dark"]`).

- [x] **3. Tampilkan Pemilik Kendaraan di Daftar Tabel, Modal Detail & Export Perawatan:**
  - `MaintenanceController::datatable()`: tambahkan kolom `fleetOwner` (`e($row->fleet?->company?->name ?? '-')`) beserta `filterColumn('fleetOwner', ...)` dan dukung pencarian global nama pemilik kendaraan sesuai `datatable-search-standard`.
  - `resources/views/warehouse/maintenance/index.blade.php`:
    - Tambahkan kolom **Pemilik Kendaraan** di sebelah kolom Plat Nomor pada tabel DataTables.
    - Pada fungsi JS `showDetail(id)`, tampilkan juga Nama Pemilik Kendaraan (`res.fleet?.company?.name || '-'`) di informasi header modal detail.
  - `MaintenanceExport.php` & `maintenance-pdf.blade.php`: sertakan kolom/informasi **Pemilik Kendaraan** agar laporan perawatan konsisten.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Cek syntax PHP & kompilasi Blade:
  ```bash
  php -l app/Services/Warehouse/MaintenanceService.php app/Http/Controllers/Warehouse/MaintenanceController.php app/Exports/MaintenanceExport.php
  ```
- [x] Verifikasi di form Tambah & Edit Perawatan:
  - Pilih Nopol Internal (mis. `BE 8172 AEU`) -> muncul `PT PUTRI HOKI LOGISTIK`.
  - Pilih Nopol External (mis. `BE 8786 ADU`) -> muncul `Wiwin Setiawan`.
- [x] Pastikan pencarian nama pemilik kendaraan di search box DataTables Perawatan berjalan tanpa error SQL.
