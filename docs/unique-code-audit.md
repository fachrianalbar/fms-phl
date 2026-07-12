# Unique Code Audit

## Tujuan

Project memakai banyak kode bisnis (`code`, `shipmentNumber`, `invoiceNumber`, dan nomor rekening/registrasi) pada model yang sebagian besar memakai `SoftDeletes`. Kode yang sudah pernah dipakai tidak boleh dipakai ulang, termasuk setelah soft delete. Unique index database tetap menjadi penjaga terakhir; aplikasi bertugas memilih kode pengganti dan memberi notifikasi, bukan mengembalikan error duplicate.

## Service Utama

`App\Services\UniqueCodeService` adalah source of truth baru untuk resolver kode.

- Mendukung model dan field dinamis.
- Otomatis memakai `withTrashed()` hanya untuk model yang memakai trait `SoftDeletes`.
- Mendukung `ignoreId` untuk update record yang sama.
- Mendukung scope callback, misalnya customer/bulan/tahun invoice.
- Mempertahankan digit padding dari angka terakhir.
- Fallback kode tanpa angka: `CUSTOM` menjadi `CUSTOM-001`.
- Membatasi pencarian kandidat dengan `maxIterations`.
- Menyediakan retry duplicate key via `runWithDuplicateRetry()`.

Contoh create:

```php
$result = app(UniqueCodeService::class)->resolve(
    model: Item::class,
    field: 'code',
    requestedCode: $request->code,
);

Item::create([
    ...$validated,
    'code' => $result->resolvedCode,
]);
```

Contoh update:

```php
$result = app(UniqueCodeService::class)->resolve(
    model: Order::class,
    field: 'shipmentNumber',
    requestedCode: $request->shipmentNumber,
    ignoreId: $order->id,
);
```

## Fitur Yang Diimplementasikan

| Fitur | Model | Tabel | Field | SoftDeletes | Unique index | Create/update | Perubahan |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Inventory Item | `App\Models\Inventory\Item` | `item` | `code` | Ya | `code` | `ItemController`, `ItemService` | Duplicate aktif/soft-deleted auto-resolve, validasi unique dihapus dari request, DB unique tetap. |
| Purchasing Purchase | `App\Models\Purchasing\Purchase` | `purchase` | `code` | Ya | Tidak terlihat di schema snapshot | `PurchaseController`, `PurchaseService` | Auto-resolve create, tidak rename code saat delete. Perlu migration index setelah data dicek. |
| Operational Order | `App\Models\Operational\Order` | `order` | `code`, `shipmentNumber` | Ya | `code`, `shipmentNumber` | `OrderController`, `OrderService` | Auto-resolve create/update, response JSON berisi `meta`, tidak rename code/shipment saat delete. |
| Finance Invoice | `App\Models\Finance\Invoice` | `invoice` | `invoiceNumber` | Ya | Tidak terlihat di schema snapshot untuk `invoiceNumber` | `InvoiceController`, `InvoiceService` | Suggest/manual update memakai soft-deleted data dan scope customer/bulan/tahun. |
| Maintenance | `App\Models\Warehouse\Maintenance` | `maintenance` | `code` | Ya | `code` | `GenerateCode::generateCodeAscDate`, `MaintenanceService`, model boot | Date-sequence helper sekarang soft-delete aware; delete tidak rename code. |
| Customer | `App\Models\Master\Customer` | `customer` | `code` | Ya | `code` | `CustomerController`, `CustomerService` | Duplicate create/update auto-resolve, PIC/detail relasi memakai kode hasil resolver, delete tidak rename code. |
| Supplier | `App\Models\Inventory\Supplier` | `supplier` | `code` | Ya | `code` | `SupplierController`, `SupplierService` | Duplicate create/update auto-resolve. |
| Bon UJT | `App\Models\Operational\BonUjt` | `bon_ujt` | `code` | Ya | `code` | `BonUjtController`, `BonUjtService` | Duplicate create auto-resolve dan detail memakai kode hasil resolver. |

## Pola Berbahaya Yang Ditemukan

- `GenerateCode::generateCodeAscDate()` memakai `count()` dan `exists()` tanpa `withTrashed()`. Ini berisiko memakai ulang kode dari record soft-deleted.
- `OrderService::shipmentFormat()` mengambil `orderByDesc('created_at')->first()` dan cek hanya record aktif. Ini berisiko melewati histori soft-deleted.
- `InvoiceService::invoiceNumberFormat()` dan `getSuggestedInvoiceNumber()` sebelumnya mencari nomor hanya dari record aktif.
- `PurchaseController`, `OrderController`, dan `ItemController` memakai `Rule::unique(...)->whereNull('deleted_at')`, sehingga duplicate soft-deleted menjadi validation error atau reusable.
- `OrderService`, `PurchaseService`, `MaintenanceService`/model, dan `CustomerService` mengubah kode sebelum soft delete. Ini bertentangan dengan aturan tidak boleh reuse kode soft-deleted.
- `CustomerService`, `SupplierService`, `BonUjtService`, dan `MaintenanceService` sebelumnya masih menyimpan `code` dari request langsung pada create/update.

## Audit Field Identifier

Field unik utama dari schema snapshot:

- `code` dengan SoftDeletes dan unique index: `bank_account`, `bank_receiver`, `bank_sender`, `bon_ujt`, `bon_ujt_detail`, `city`, `company`, `company_setting`, `config_bank`, `cost_component`, `customer`, `down_payment`, `down_payment_detail`, `drop_location`, `due_date`, `employee`, `expense`, `fleet_brand`, `fleet_company`, `fleet_driver`, `fleet_type`, `invoice`, `invoice_detail`, `invoice_payment`, `item`, `item_category`, `item_location`, `location`, `maintenance`, `maintenance_detail`, `material`, `mutation`, `order`, `order_detail`, `order_type`, `pickup_location`, `position`, `province`, `role`, `route`, `route_detail`, `route_type`, `stock`, `supplier`, `tonase_bonus`, `transaction_type`, `transfer_fund`, `unit`, `user_bank`, `vendor_payment`, `warehouse`.
- `code` dengan SoftDeletes tetapi unique index tidak terlihat di snapshot: `district`, `menu`, `order_tax`, `order_tracking`, `purchase`, `role_menu`, `stock_transaction`.
- Identifier non-code: `order.shipmentNumber` punya unique index; `invoice.invoiceNumber` tidak terlihat punya unique index; `vendor_payment.nota_number` hanya index biasa; `user_bank.accountNumber` divalidasi unique aktif saja; `fleet_driver.vehicleRegistrationNumber` divalidasi unique aktif saja.

## Scope Dan Index

- `order.shipmentNumber` global sesuai unique index global.
- `item.code` global sesuai unique index global.
- `invoice.invoiceNumber` secara bisnis tampak scoped customer/bulan/tahun, tetapi schema snapshot tidak punya unique index untuk field ini. Resolver mengikuti scope bisnis; DB belum menjadi penjaga terakhir untuk field ini.
- `purchase.code` dipakai sebagai identifier dan divalidasi unik, tetapi schema snapshot tidak menunjukkan unique index. Resolver dipasang; DB guard perlu migration terpisah setelah deduplikasi data produksi.

## Import, Job, Observer, Seeder

- Tidak ditemukan import Excel/CSV yang membuat master code manual untuk fitur yang diubah.
- Banyak child-row technical code masih dibuat dengan `GenerateCode::generateCode(..., true)` berbasis timestamp mikrodetik. Risiko collision lebih kecil, tetapi belum semua dipindahkan ke service agar perubahan tetap scoped. Jika duplicate key terjadi di child-row technical code, target berikutnya adalah mengganti assignment itu ke `UniqueCodeService`.
- `Maintenance` sebelumnya punya model `deleting` observer yang rename code; sudah dihapus.
- Factory/seeder yang terlihat hanya `UserFactory` memakai Faker unique email dan `SettingsTableSeeder`; tidak ada generator code manual yang perlu auto-resolve saat ini.

## Notifikasi

Web redirect memakai flash:

```php
->with('code_replaced', [
    'requested' => $result->requestedCode,
    'resolved' => $result->resolvedCode,
]);
```

`resources/views/partials/alert.blade.php` menampilkan pesan informatif ketika kode diganti. Endpoint JSON order dan update nomor invoice mengirim `meta.code_changed`, `requested_code`, dan `resolved_code`.

## Concurrency

Resolver mengecek kandidat termasuk soft-deleted record. Penyimpanan create/update yang diubah dibungkus `UniqueCodeService::runWithDuplicateRetry()` dan `DB::transaction()`. Jika unique index melempar duplicate key akibat race condition, callback dicoba ulang dan resolver memilih kandidat berikutnya.

Catatan: retry hanya sempurna untuk field yang punya unique index database. `purchase.code` dan `invoice.invoiceNumber` perlu index yang sesuai agar DB benar-benar menutup race.

## Test

`tests/Feature/UniqueCodeServiceTest.php` menutup kasus:

- kode manual belum pernah dipakai;
- duplicate aktif dan soft-deleted;
- leading zero;
- update ignore-id;
- fallback kode tanpa angka;
- scoped lookup;
- retry duplicate key;
- direct duplicate tetap ditolak unique index.

## Rekomendasi Lanjutan

1. Tambahkan migration unique index untuk `purchase.code` setelah memastikan tidak ada duplicate historis.
2. Putuskan scope resmi `invoice.invoiceNumber`, lalu tambahkan index komposit yang cocok atau tabel sequence khusus.
3. Pindahkan generator timestamp child-row yang masih penting secara bisnis ke `UniqueCodeService` secara bertahap.
4. Audit validasi duplicate aktif saja pada `user_bank.accountNumber`, `config_bank.userBankCode`, dan `fleet_driver.vehicleRegistrationNumber`; beberapa field ini mungkin memang harus error, bukan auto-generate.
5. Untuk import bulk di masa depan, preload kode per prefix dan pakai cache lokal per file agar duplicate dalam file yang sama juga ter-resolve.
