# Restrukturisasi Menu ERP — FMS PHL

> **Status: SELESAI (kode + database).**
> Struktur menu ERP 2-level sudah diterapkan lewat `MenuSeeder` + `RoleMenuSeeder`.
> Super Admin (`SPRADMIN`) = semua menu; role yang sudah ada tetap punya aksesnya
> (daftar akses eksplisit di `RoleMenuSeeder`). Semua menu tak terpakai
> (termasuk 12 menu soft-deleted) sudah **dihapus permanen** dari tabel `menu`.
> Nama grup (level-1) juga **diperbarui ke gaya ERP** (Master, Piutang (AR),
> Hutang (AP), Kas & Bank, Sistem, dst) — lihat §A.1.

---

## Ringkasan Singkat

| | Sebelum | Sesudah |
|---|---|---|
| Parent (level-1) | 13 (termasuk `FINANCE` kosong, `DATA` & `WAREHOUSE_PARENT` tumpang tindih) | **10** |
| Child (level-2) | 56 | **55** |
| Total menu aktif | 69 | **65** |
| Kode menu | Campur: `MN260521090715`, `MN251127221320`, `ORDER-DETAIL`, `MN_RPT_*` | **SCREAMING_SNAKE_CASE** konsisten |
| Menu tanpa route | `MN_RPT_MAINT_ITEM` (link mati) | dihapus |
| Kode duplikat | `BANK` dipakai parent **dan** child | dipecah (`BANK` + `BANK_ACCOUNT`) |

---

## A. Struktur Final (ERP-style, 2 level)

Urutan mengikuti alur bisnis: **Master → Operasional → Pembelian → Inventory → Piutang → Hutang → Pembayaran → Bank → Laporan → Administrator**.

```
🏠 Beranda (hardcode di sidebar, bukan baris menu)

1. MASTER            "Master" / "Master"
   ├─ CUSTOMER               master/customer
   ├─ SUPPLIER               inventory/supplier        (dipindah dari INVENTORY)
   ├─ FLEET_BRAND            master/fleet-brand
   ├─ FLEET_TYPE             master/fleet-type
   ├─ FLEET                  master/fleets
   ├─ FLEET_COMPANY          master/fleet-company
   ├─ EMPLOYEE               master/employee
   ├─ LOCATION               master/location
   ├─ ROUTE                  data/route                (dipindah dari DATA)
   ├─ MATERIAL               master/material
   ├─ UNIT                   master/unit
   ├─ COST_COMPONENT         master/cost-component
   ├─ COST_COMPONENT_LOG     master/cost-component-price-log
   └─ COMPANY                master/company

2. OPERATIONAL       "Operational" / "Operasional"
   ├─ ORDER                  operational/order
   ├─ NOT_RETURN_DO          operational/not-return-do
   └─ RETURN_DO              operational/return-do

3. PURCHASING        "Purchasing" / "Purchasing"
   ├─ PURCHASE               purchasing/purchase
   └─ PURCHASE_PAYMENT       purchasing/purchase-payment

4. INVENTORY         "Inventory" / "Inventory"
   ├─ ITEM                   inventory/items
   ├─ ITEM_CATEGORY          inventory/item-category
   ├─ ITEM_UNIT              inventory/item-unit
   ├─ WAREHOUSE              inventory/warehouse
   ├─ STOCK                  inventory/stock
   ├─ STOCK_TRANSACTION      inventory/transaction-stock
   ├─ STOCK_SYNC             inventory/stock-sync
   └─ MAINTENANCE            warehouse/maintenance       (dipindah dari WAREHOUSE_PARENT)

5. FAKTUR            "Accounts Receivable" / "Piutang (AR)"  (Piutang / AR)
   ├─ FAKTUR_PEMBUATAN              invoice/create
   ├─ FAKTUR_BELUM_LUNAS            invoice/unpaid
   ├─ FAKTUR_LUNAS                  invoice/paid
   ├─ FAKTUR_PEMBAYARAN             invoice/payment
   └─ FAKTUR_TRANSAKSI_PEMBAYARAN   invoice/payment-transaction

6. VENDOR            "Accounts Payable" / "Hutang (AP)"  (Hutang / AP)
   ├─ VENDOR_ORDER_WAITING   vendor/order/waiting
   ├─ VENDOR_INV_UNPAID      vendor/invoice/unpaid
   ├─ VENDOR_INV_PAID        vendor/invoice/paid
   └─ VENDOR_PAY_LIST        vendor/payment

7. DIRECT_PAYMENT    "Direct Payment" / "Direct Payment"
   ├─ DIRECT_PAYMENT_UNPAID  direct-payment/order/unpaid
   ├─ DIRECT_PAYMENT_PAID    direct-payment/order/paid
   └─ DIRECT_PAYMENT_LIST    direct-payment/payment

8. BANK              "Cash & Bank" / "Kas & Bank"  (Kas & Bank)
   ├─ BANK_ACCOUNT           bank/bank-account
   ├─ USER_BANK              bank/user-bank
   └─ BANK_BOOK              bank/bank-book

9. REPORT            "Report" / "Report"
   ├─ ORDER_DETAIL           report/order-detail
   ├─ ALL_ORDER_LIST         report/all-order-list
   ├─ PROFIT_LOSS            report/profit-loss
   ├─ DRIVER_SALARY          report/driver-salary
   ├─ DRIVER_TONASE          report/driver-tonase
   ├─ FLEET_TONASE           report/fleet-tonase
   ├─ MAINTENANCE_FLEET      report/maintenance-fleet
   ├─ MAINTENANCE_COMPANY    report/maintenance-company-internal
   └─ SUPPLIER_PURCHASE      report/supplier

10. ADMINISTRATOR    "System" / "Sistem"
   ├─ USER                   administrator/user
   ├─ ROLE                   administrator/role
   ├─ MASTER_MENU            master/menu                 (dipindah dari MASTER)
   └─ ACTIVITY_LOG           administrator/activity-log
```

### A.1 Rename nama grup (level-1)

Kode grup **tidak berubah** (agar `role_menu` & hak akses tetap aman); hanya
kolom `name`/`nama` (label yang tampil di sidebar) yang diperbarui:

| Kode | `name` (EN) | `nama` (ID) | Sebelumnya |
|---|---|---|---|
| `MASTER` | Master | Master | Master Data / Data Master |
| `OPERATIONAL` | Operational | Operasional | Operational / Operasional |
| `PURCHASING` | Purchasing | Purchasing | Purchasing / Pembelian |
| `INVENTORY` | Inventory | Inventory | Inventory / Inventaris |
| `FAKTUR` | Accounts Receivable | Piutang (AR) | Invoice / Faktur |
| `VENDOR` | Accounts Payable | Hutang (AP) | Vendor / Vendor |
| `DIRECT_PAYMENT` | Direct Payment | Direct Payment | Direct Payment / Pembayaran Langsung |
| `BANK` | Cash & Bank | Kas & Bank | Bank / Bank |
| `REPORT` | Report | Report | Report / Laporan |
| `ADMINISTRATOR` | System | Sistem | Administrator / Administrator |

> Label child (level-2) **tidak diubah** karena sebagian di-lookup lewat
> `MenuService::getByName()`.

### Aturan yang dipatuhi

| Aspek | Aturan |
|---|---|
| `url` | **Tidak diubah** — harus cocok dengan route/controller. |
| `name` | **Tidak diubah** untuk menu yang di-lookup `MenuService::getByName()` (Order, Route, Customer, Fleet, Fleet Company, Location, Not Return Do, Return Do, Purchase, Purchase Payment, Maintenance). |
| Parent | `parentCode = '0'`, `url = '#'`, punya `icon` (feather) valid. |
| Child | `url` nyata, icon dari map `$childIcons` di `sidebar.blade.php`. |
| Sort | Parent unik 1–10; child menaik di dalam parent-nya. |

---

## B. Perubahan yang Dilakukan

### B.1 Penggabungan / penghapusan parent

| Aksi | Parent | Keterangan |
|---|---|---|
| **Gabung** | `DATA` → `MASTER` | Child `ROUTE` dipindah ke `MASTER`. Parent `DATA` dihapus. |
| **Gabung** | `WAREHOUSE_PARENT` → `INVENTORY` | Child `MAINTENANCE` dipindah ke `INVENTORY`. Parent dihapus. |
| **Hapus** | `FINANCE` | Parent kosong (tanpa child). |

### B.2 Rename kode menu (agar konsisten)

| Kode lama | Kode baru |
|---|---|
| `MN260521090715` | `STOCK_SYNC` |
| `MN251127221320` | `MASTER_MENU` (dipindah ke ADMINISTRATOR) |
| `MN251127221422` | `COST_COMPONENT_LOG` |
| `ORDER-DETAIL` | `ORDER_DETAIL` |
| `MN_RPT_MAINT_FLEET` | `MAINTENANCE_FLEET` |
| `MN_RPT_MAINT_COMPINT` | `MAINTENANCE_COMPANY` |
| `MN_RPT_SUPPLIER_PURCHASE` | `SUPPLIER_PURCHASE` |
| `BANK` (child "Bank Account") | `BANK_ACCOUNT` (memecah tabrakan kode dengan parent `BANK`) |

### B.3 Perpindahan child antar parent

| Child | Dari | Ke |
|---|---|---|
| `SUPPLIER` | `INVENTORY` | `MASTER` (Business Partner) |
| `ROUTE` | `DATA` | `MASTER` |
| `MAINTENANCE` | `WAREHOUSE_PARENT` | `INVENTORY` |
| `MASTER_MENU` (Menu) | `MASTER` | `ADMINISTRATOR` |

### B.4 Dihapus total

| Menu | Kode | Alasan |
|---|---|---|
| Report Maintenance Item | `MN_RPT_MAINT_ITEM` (`report/maintenance-item`) | **Tidak ada route** → link mati. |

---

## C. Seeder

Dua seeder baru (idempotent, aman dijalankan berulang):

### C.1 `database/seeders/MenuSeeder.php`
- Mendefinisikan struktur menu target (parent + child).
- **Hanya menyentuh tabel `menu`** (tidak menyentuh `role_menu`).
- **Hapus PERMANEN** semua menu di luar struktur target: `DB::table('menu')->whereNotIn('code', $targetCodes)->delete();`
  → menyapu menu lama, kode auto/rename, `MN_RPT_MAINT_ITEM`, **dan 12 menu soft-deleted** (lihat §E).
- `upsert` per `code` (`updateOrInsert` manual + reset `deleted_at = null`).
- Normalisasi kode duplikat (mis. `BANK` lama) → 1 baris per kode.

### C.2 `database/seeders/RoleMenuSeeder.php`
- **`SPRADMIN` → semua menu (65).**
- **Role existing** memakai daftar akses **EKSPLISIT** (`$access`) hasil pemetaan dari data `role_menu` asli + rename/gabung kode,
  sehingga seeder **tidak bergantung pada state tabel `role_menu`** dan akses role **tidak ada yang hilang**:
  `SPRUSER` (56), `TRL250604212202` (44), `DO` (38), `TRL250604212231` (35), `TRL250604212242` (38).
- Role yang tidak terdaftar di `$access` (mis. role baru) memakai `$legacyMap` sebagai fallback.
- Setiap child yang di-assign otomatis menyertakan **parent**-nya (sidebar hanya tampil bila parent ikut ada di `role_menu`).
- Menulis ulang `role_menu` bersih: hapus semua lalu insert (idempotent) + membersihkan baris yatim
  (roleCode/menuCode yang tidak ada di tabel `role`/`menu`).

### C.3 Cara menjalankan

```bash
cd phl

# Jalankan keduanya (urutan penting: Menu dulu, baru RoleMenu)
php artisan db:seed --class=MenuSeeder
php artisan db:seed --class=RoleMenuSeeder

# Atau lewat DatabaseSeeder (sudah terdaftar)
php artisan db:seed

# Bersihkan cache
php artisan view:clear && php artisan config:clear && php artisan route:clear
```

`DatabaseSeeder` sudah memanggil `MenuSeeder` lalu `RoleMenuSeeder`.

---

## D. Hasil Verifikasi

- Menu aktif = **65** (10 parent + 55 child), **0 kode duplikat**.
- Menu soft-deleted = **0** (12 menu lama dihapus permanen — lihat §E).
- `role_menu`: **276** baris, **0 menuCode yatim**, **0 roleCode yatim**.
- Setiap role: **semua child punya parent** di `role_menu` (tidak ada menu "yatim" yang gagal tampil).
- Jumlah menu per role (setelah seeder):

  | Role | Jumlah menu |
  |---|---|
  | `SPRADMIN` (System Administrator) | 65 (semua) |
  | `SPRUSER` (Super User) | 56 |
  | `TRL250604212202` (Maintenance) | 44 |
  | `DO` | 38 |
  | `TRL250604212231` (Invoice) | 35 |
  | `TRL250604212242` (Uang Jalan) | 38 |

  > Idempotent: menjalankan `MenuSeeder` + `RoleMenuSeeder` berulang kali menghasilkan
  > jumlah yang sama (total `role_menu` = 276 baris).

- Sidebar berhasil di-render untuk `SPRADMIN` (semua menu) & `DO` (subset sesuai akses).
- `php artisan view:cache` → sukses (semua blade compile), `route:list` → tanpa error.

---

## E. Menu Tersembunyi (soft-deleted) — DIHAPUS PERMANEN

Menu berikut **sudah lama di-soft-delete** (disembunyikan dari sidebar sejak 2024–2025).
Sebagai bagian dari pembersihan, baris-baris ini kini **dihapus permanen** dari tabel `menu`
(oleh langkah *purge* di `MenuSeeder`). Route & controller lama (bila ada) dibiarkan
selama tidak direferensikan menu — jadi tidak ada menu yatim di database.

| Kode | Nama | URL | Parent lama | Soft-deleted |
|---|---|---|---|---|
| `SETTING` | Setting | `#` | (parent) | 2024-09-04 |
| `CHANGE_PASS` | Change Password | `#` | SETTING | 2024-08-21 |
| `POSITION` | Position | `master/position` | MASTER | 2024-09-24 |
| `DROP_LOCATION` | Drop Location | `data/drop-location` | DATA | 2024-10-19 |
| `PICKUP_LOCATION` | Pickup Location | `data/pickup-location` | DATA | 2024-10-19 |
| `BON_UJT` | Bon Ujt | `operational/bon-ujt` | OPERATIONAL | 2025-02-13 |
| `CONFIG_BANK` | Config Bank | `bank/config-bank` | BANK | 2025-05-12 |
| `EXPENSE` | Expense | `bank/expense` | BANK | 2025-05-12 |
| `TRANSACTION_TYPE` | Transaction Type | `master/transaction-type` | MASTER | 2025-05-19 |
| `TONASE_BONUS` | Tonase Bonus | `data/tonase-bonus` | DATA | 2025-05-19 |
| `COMPANY_SETTING` | Company Setting | `administrator/company-setting` | ADMINISTRATOR | 2025-05-19 |
| `TRANSFER_FUND` | Transfer Fund | `bank/transfer-fund` | BANK | 2025-07-03 |

> Verifikasi: `menu` soft-deleted = **0**. `$childIcons` di `sidebar.blade.php` sudah dibersihkan
> dari URL menu di atas. Jika suatu saat diaktifkan kembali, tambahkan kembali entri menu + ikonnya.

---

## F. Log Penghapusan 10 Menu (pekerjaan sebelumnya)

### F.1 Daftar yang dihapus

| # | Menu | Code | Parent | URL |
|---|---|---|---|---|
| 1 | Fleet Owner | `FLEET_OWNER` | DATA | `data/fleet-owner` |
| 2 | Item Location | `ITEM_LOCATION` | INVENTORY | `inventory/item-location` |
| 3 | Bank Sender | `BANK_SENDER` | MASTER | `master/bank-sender` |
| 4 | Bank Receiver | `BANK_RECEIVER` | MASTER | `master/bank-receiver` |
| 5 | Due Date | `DUE_DATE` | MASTER | `master/due-date` |
| 6 | Order Monitoring | `ORDER_MONITORING` | OPERATIONAL | `operational/monitoring-order` |
| 7 | Office Order | `ORDER_OFFICE` | OPERATIONAL | `operational/office-order` |
| 8 | Down Payment | `DOWN_PAYMENT` | OPERATIONAL | `operational/down-payment` |
| 9 | Purchase Verification | `PURCHASE_VERIFICATION` | PURCHASING | `purchasing/purchase-verification` |
| 10 | Purchase Confirmation | `PURCHASE_CONFIRMATION` | PURCHASING | `purchasing/purchase-confirmation` |

> ⚠️ **Fleet Company (`FLEET_COMPANY`) TIDAK dihapus** (dibatalkan oleh user). Fleet Owner yang dihapus.

### F.2 Migration

`database/migrations/2026_10_17_100000_remove_unused_menus_and_features.php`
- `up()`: hapus `role_menu` + `menu` untuk 10 code, lalu `Schema::dropIfExists` tabel: `bank_sender`, `bank_receiver`, `due_date`, `down_payment`, `down_payment_detail`, `fleet_driver`, `item_location`.
- Sudah dijalankan (`php artisan migrate`).

### F.3 Catatan sisa yang dibiarkan

- `OrderTracking` & `TelegramUser` — tetap (dipakai `API/TelegramController`).
- `Fleet Company` + `FleetCompanyService` + route `master/fleet-company` — tetap.
- Kolom `dueDate`/`bankSender`/`bankReceiver` di tabel lain (purchase, transfer_fund, dll.) — field biasa, bukan model, tetap.
