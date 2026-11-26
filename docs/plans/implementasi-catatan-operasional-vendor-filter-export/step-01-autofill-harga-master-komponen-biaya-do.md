# Step 01: Auto-Fill Harga Master Komponen Biaya di DO Belum Kembali & DO Kembali

- **Plan Utama:** [implementasi-catatan-operasional-vendor-filter-export.md](../implementasi-catatan-operasional-vendor-filter-export.md)
- **Prasyarat:** Tidak ada
- **Status:** `in_progress` (implementasi kode & kompilasi Blade selesai; menunggu verifikasi manual di browser)

---

## Tujuan Langkah

Memastikan saat user memilih komponen biaya pada form edit order di **DO Belum Kembali** (`operational/not-return-do-edit/{code}/edit`) dan **DO Kembali** (`operational/return-do-edit/{code}/edit`), input nominal (`internalCostNominal[]`) otomatis terisi dari harga master (`cost_component.price`), terformat ribuan (`formatAngka`), tetap dapat diubah manual, dan langsung memperbarui total biaya komponen.

---

## Daftar File yang Disentuh

- `[MODIFY]` `resources/views/operational/not-return-do/components/cost-edit.blade.php`
- `[MODIFY]` `resources/views/operational/return-do/components/cost-edit.blade.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Tambahkan atribut `data-price` pada `<option>` komponen biaya (existing rows & newly added rows)** di kedua file:
  - Pada loop baris existing (`@foreach ($component as $comp)` sekitar baris 223–230):
    ```blade
    <option value="{{ $comp->code }}"
        data-type="{{ $comp->type }}"
        data-price="{{ $comp->price }}"
        data-is-salary="{{ ($comp->type === 'salary' || stripos($comp->name, 'gaji') !== false) ? '1' : '0' }}"
        {{ $item->componentType == $comp->code ? 'selected' : '' }}>
        {{ $comp->name }}
    </option>
    ```
  - Pada template JS `addInternalCostRow()` (`@foreach ($component as $item)` sekitar baris 409–415):
    ```blade
    <option value="{{ $item->code }}"
        data-type="{{ $item->type }}"
        data-price="{{ $item->price }}"
        data-is-salary="{{ ($item->type === 'salary' || stripos($item->name, 'gaji') !== false) ? '1' : '0' }}">
        {{ $item->name }}
    </option>
    ```

- [x] **2. Perbarui handler `$(document).on('change', '.internal-cost-component-select', ...)`** (sekitar baris 330–353 di kedua file) untuk mengisi nominal dari `data-price`:
  ```javascript
  const masterPrice = selectedOpt.attr('data-price');
  const nominalInput = row.find('input[name="internalCostNominal[]"]')[0];

  if (nominalInput && masterPrice !== undefined && masterPrice !== null && masterPrice !== '') {
      const numericPrice = Number(masterPrice);
      if (Number.isFinite(numericPrice)) {
          nominalInput.value = String(Math.round(numericPrice));
          if (typeof formatAngka === 'function') {
              formatAngka(nominalInput);
          }
          calculateInternalCostTotal();
      }
  }
  ```
  - Pastikan perilaku tetap aman: ketika halaman pertama kali dimuat (existing rows), event `change` tidak dipicu otomatis sehingga nominal historis order yang sudah tersimpan **tidak tertimpa**. Harga master hanya terisi ketika user mengganti pilihan komponen atau menambah baris baru lalu memilih komponen.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Kompilasi kedua file Blade tanpa error:
  ```bash
  php artisan tinker --execute="app('blade.compiler')->compileString(file_get_contents('resources/views/operational/not-return-do/components/cost-edit.blade.php')); app('blade.compiler')->compileString(file_get_contents('resources/views/operational/return-do/components/cost-edit.blade.php')); echo 'OK';"
  ```
- [ ] Verifikasi manual di browser:
  1. Buka *DO Belum Kembali* -> Edit Order -> klik **Tambah Data** di kartu Biaya Komponen -> pilih komponen yang memiliki harga di master -> nominal terisi otomatis & total ikut berubah.
  2. Ulangi langkah yang sama di *DO Kembali* -> Edit Order.
  3. Ubah nominal secara manual (override) -> simpan -> pastikan nominal override tersimpan.
