# Restrukturisasi Menu (Audit & Rencana)

> Dokumen audit struktur menu pada tabel `menu` + usulan restrukturisasi agar sesuai pola **ERP** umum.
>
> 📌 **STATUS: DRAFT — MENUNGGU FILTER USER.** Bagian **§7 (Checklist Filter)** dipakai untuk menandai menu mana yang dipakai / tidak dipakai sebelum eksekusi migrasi.
>
> ✅ **UPDATE:** Restrukturisasi sudah **DIEKSEKUSI** lewat `MenuSeeder` + `RoleMenuSeeder`. Lihat dokumen final: **`md/restrukturisasi-menu.md`**.
>
> Referensi: `database/tmp_dump_menu.php` (helper dump), `resources/views/layouts/sidebar.blade.php` (render sidebar), `app/Models/Menu.php`, `app/Services/Master/MenuService.php`.

---

## 1. Ringkasan Singkat

- Tabel `menu` berisi **91 baris** = **14 menu utama** + **77 sub-menu**.
- **14 menu utama** saat ini: `SETTING, FINANCE, OPERATIONAL, DIRECT_PAYMENT, VENDOR, FAKTUR, INVENTORY, BANK, PURCHASING, WAREHOUSE_PARENT, REPORT, DATA, MASTER, ADMINISTRATOR`.
- Masalah utama: **FINANCE kosong/mati**, modul finansial terpecah (FAKTUR/VENDOR/DIRECT_PAYMENT/BANK), **MASTER menampung 18 item flat**, tumpang tindih **DATA vs MASTER** dan **INVENTORY vs WAREHOUSE_PARENT**, kode menu **tidak konsisten** (ada auto-generate `MN...`), ikon campur-aduk, dan **1 menu tanpa route** (`MN_RPT_MAINT_ITEM`).

---

## 2. Skema Tabel `menu` Saat Ini

Sumber: `database/schema/mysql-schema.sql:869`.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | varchar(36) | UUID, PRIMARY KEY |
| `code` | varchar(30) | dipakai sebagai referensi `parentCode` **dan** `role_menu.menuCode` |
| `name` | text | label EN |
| `nama` | text | label ID |
| `parentCode` | varchar(20) | `'0'` = menu utama |
| `url` | varchar(50) | **terlalu pendek**; juga dipakai untuk deteksi active-state |
| `icon` | varchar(50) | nama Boxicons (CSS tidak tersedia) |
| `sort` | int | urutan |
| `created_at` / `updated_at` / `deleted_at` | timestamp | SoftDeletes |

Relasi terkait: `role_menu` (`roleCode` + `menuCode`) menentukan menu apa yang tampil per role. Role saat ini: `SPRADMIN`, `SPRUSER`, `TRL250604212202` (Maintenance), `DO`, `TRL250604212231` (Invoice), `TRL250604212242` (Uang Jalan).

---

## 3. Struktur Menu Saat Ini (Tree)

```
SETTING            (1)   └─ Change Password (url '#')
FINANCE            (0)   └─ ⚠️ KOSONG / MATI
OPERATIONAL        (7)   Order, Order Monitoring, Not Return DO, Bon UJT, Return DO, Office Order, Down Payment
DIRECT_PAYMENT     (3)   Unpaid Order, Paid Order, Payment List
VENDOR             (4)   Waiting Order, Unpaid Invoice, Paid Invoice, Payment List
FAKTUR             (5)   Create Invoice, Unpaid, Paid, Invoice Payment, Payment Transaction
INVENTORY          (9)   Item, Stock, Kartu Stock, Warehouse, Supplier, Item Unit, Item Location, Item Category, Sync Stock
BANK               (6)   Bank Account, User Bank, Config Bank, Transfer Fund, Expense, Bank Book
PURCHASING         (4)   Purchase, Purchase Verification, Purchase Confirmation, Purchase Payment
WAREHOUSE_PARENT   (1)   Maintenance
REPORT            (10)   Order Detail, P&L, Driver Salary, Driver Tonase, Fleet Tonase, All Order,
                         Maint. per Fleet, Maint. per Fleet Company, Supplier Purchase, Report Maintenance Item ⚠️
DATA               (5)   Fleet Owner, Drop Location, Pickup Location, Route, Tonase Bonus
MASTER            (18)   Fleet Brand, Fleet Type, Position, Employee, Fleet, Unit, Customer, Cost Component,
                         Location, Material, Bank Sender, Bank Receiver, Due Date, Fleet Company,
                         Transaction Type, Company, Menu, Component Log Change
ADMINISTRATOR      (4)   User, Role, Company Setting, Activity Log
```

---

## 4. Audit Lengkap (91 baris)

Kolom **Route?** = apakah `url` menu punya route aktif (hasil silang dengan `php artisan route:list`).

### 4.1 Menu Utama (parent)

| Code | EN | ID | URL | Icon | Sort | Route? | Catatan |
|---|---|---|---|---|---|---|---|
| `SETTING` | Setting | Setting | `#` | — | 1 | ✅ | Hanya berisi Change Password |
| `FINANCE` | Finance | Finance | `#` | dollar-sign | 1 | ❌ | **Kosong/mati** |
| `OPERATIONAL` | Operational | Operational | `#` | file-text | 1 | ✅ | Ikon bentrok dgn FAKTUR |
| `DIRECT_PAYMENT` | Direct Payment | Pembayaran Langsung | `#` | credit-card | 2 | ✅ | Domain Finance |
| `VENDOR` | Vendor | Vendor | `#` | truck | 2 | ✅ | Domain Finance (AP) |
| `FAKTUR` | Invoice | Faktur | `#` | file-text | 2 | ✅ | Domain Finance (AR) |
| `INVENTORY` | Inventory | Inventory | `#` | package | 2 | ✅ | |
| `BANK` | Bank | Bank | `#` | book-open | 2 | ✅ | Domain Finance |
| `PURCHASING` | Purchasing | Purchasing | `#` | shopping-bag | 3 | ✅ | |
| `WAREHOUSE_PARENT` | Warehouse | Warehouse | `#` | codepen | 4 | ✅ | Hanya 1 anak |
| `REPORT` | Report | Report | `#` | clipboard | 5 | ✅ | |
| `DATA` | Data | Data | `#` | archive | 8 | ✅ | Tumpang tindih MASTER |
| `MASTER` | Master | Master | `#` | table | 9 | ✅ | 18 anak flat |
| `ADMINISTRATOR` | Administrator | Administrator | `#` | users | 10 | ✅ | |

### 4.2 Sub-menu

| Parent | Code | EN | URL | Sort | Route? |
|---|---|---|---|---|---|
| SETTING | `CHANGE_PASS` | Change Password | `#` | 1 | ❌ (aksi profil, bukan menu) |
| ADMINISTRATOR | `USER` | User | `administrator/user` | 1 | ✅ |
| ADMINISTRATOR | `ROLE` | Role | `administrator/role` | 2 | ✅ |
| ADMINISTRATOR | `COMPANY_SETTING` | Company Setting | `administrator/company-setting` | 3 | ✅ |
| ADMINISTRATOR | `ACTIVITY_LOG` | Activity Log | `administrator/activity-log` | 4 | ✅ |
| BANK | `BANK` | Bank Account | `bank/bank-account` | 1 | ✅ |
| BANK | `USER_BANK` | User Bank | `bank/user-bank` | 2 | ✅ |
| BANK | `CONFIG_BANK` | Config Bank | `bank/config-bank` | 3 | ✅ |
| BANK | `TRANSFER_FUND` | Transfer Fund | `bank/transfer-fund` | 4 | ✅ |
| BANK | `EXPENSE` | Expense | `bank/expense` | 5 | ✅ |
| BANK | `BANK_BOOK` | Bank Book | `bank/bank-book` | 6 | ✅ |
| DATA | `FLEET_OWNER` | Fleet Owner | `data/fleet-owner` | 1 | ✅ |
| DATA | `DROP_LOCATION` | Drop Location | `data/drop-location` | 2 | ✅ |
| DATA | `PICKUP_LOCATION` | Pickup Location | `data/pickup-location` | 3 | ✅ |
| DATA | `ROUTE` | Route | `data/route` | 4 | ✅ |
| DATA | `TONASE_BONUS` | Tonase Bonus | `data/tonase-bonus` | 5 | ✅ |
| DIRECT_PAYMENT | `DIRECT_PAYMENT_UNPAID` | Direct Payment Unpaid Order | `direct-payment/order/unpaid` | 1 | ✅ |
| DIRECT_PAYMENT | `DIRECT_PAYMENT_PAID` | Direct Payment Paid Order | `direct-payment/order/paid` | 2 | ✅ |
| DIRECT_PAYMENT | `DIRECT_PAYMENT_LIST` | Direct Payment Payment List | `direct-payment/payment` | 3 | ✅ |
| FAKTUR | `FAKTUR_PEMBUATAN` | Create Invoice | `invoice/create` | 1 | ✅ |
| FAKTUR | `FAKTUR_BELUM_LUNAS` | Unpaid Invoice | `invoice/unpaid` | 2 | ✅ |
| FAKTUR | `FAKTUR_LUNAS` | Paid Invoice | `invoice/paid` | 3 | ✅ |
| FAKTUR | `FAKTUR_PEMBAYARAN` | Invoice Payment | `invoice/payment` | 4 | ✅ |
| FAKTUR | `FAKTUR_TRANSAKSI_PEMBAYARAN` | Payment Transaction | `invoice/payment-transaction` | 5 | ✅ |
| INVENTORY | `ITEM` | Item | `inventory/items` | 1 | ✅ |
| INVENTORY | `STOCK` | Stock | `inventory/stock` | 2 | ✅ |
| INVENTORY | `STOCK_TRANSACTION` | Kartu Stock | `inventory/transaction-stock` | 3 | ✅ |
| INVENTORY | `WAREHOUSE` | Warehouse | `inventory/warehouse` | 4 | ✅ |
| INVENTORY | `SUPPLIER` | Supplier | `inventory/supplier` | 5 | ✅ |
| INVENTORY | `ITEM_UNIT` | Item Unit | `inventory/item-unit` | 6 | ✅ |
| INVENTORY | `ITEM_LOCATION` | Item Location | `inventory/item-location` | 7 | ✅ |
| INVENTORY | `ITEM_CATEGORY` | Item Category | `inventory/item-category` | 8 | ✅ |
| INVENTORY | `MN260521090715` | Sync Stock | `inventory/stock-sync` | 9 | ✅ (kode auto-generate) |
| MASTER | `FLEET_BRAND` | Fleet Brand | `master/fleet-brand` | 1 | ✅ |
| MASTER | `FLEET_TYPE` | Fleet Type | `master/fleet-type` | 2 | ✅ |
| MASTER | `POSITION` | Position | `master/position` | 3 | ✅ |
| MASTER | `EMPLOYEE` | Employee | `master/employee` | 4 | ✅ |
| MASTER | `FLEET` | Fleet | `master/fleets` | 5 | ✅ |
| MASTER | `UNIT` | Unit | `master/unit` | 6 | ✅ |
| MASTER | `CUSTOMER` | Customer | `master/customer` | 8 | ✅ |
| MASTER | `COST_COMPONENT` | Cost Component | `master/cost-component` | 9 | ✅ |
| MASTER | `LOCATION` | Location | `master/location` | 10 | ✅ |
| MASTER | `MATERIAL` | Material | `master/material` | 11 | ✅ |
| MASTER | `BANK_SENDER` | Bank Sender | `master/bank-sender` | 12 | ✅ |
| MASTER | `BANK_RECEIVER` | Bank Receiver | `master/bank-receiver` | 13 | ✅ |
| MASTER | `DUE_DATE` | Due Date | `master/due-date` | 14 | ✅ |
| MASTER | `FLEET_COMPANY` | Fleet Company | `master/fleet-company` | 15 | ✅ |
| MASTER | `TRANSACTION_TYPE` | Transaction Type | `master/transaction-type` | 16 | ✅ |
| MASTER | `COMPANY` | Company | `master/company` | 17 | ✅ |
| MASTER | `MN251127221320` | Menu | `master/menu` | 18 | ✅ (kode auto-generate) |
| MASTER | `MN251127221422` | Component Log Change | `master/cost-component-price-log` | 19 | ✅ (kode auto-generate) |
| OPERATIONAL | `ORDER` | Order | `operational/order` | 1 | ✅ |
| OPERATIONAL | `ORDER_MONITORING` | Order Monitoring | `operational/monitoring-order` | 2 | ✅ |
| OPERATIONAL | `NOT_RETURN_DO` | Not Return Do | `operational/not-return-do` | 3 | ✅ |
| OPERATIONAL | `BON_UJT` | Bon Ujt | `operational/bon-ujt` | 4 | ✅ |
| OPERATIONAL | `RETURN_DO` | Return Do | `operational/return-do` | 4 | ✅ |
| OPERATIONAL | `ORDER_OFFICE` | Order Office | `operational/office-order` | 5 | ✅ |
| OPERATIONAL | `DOWN_PAYMENT` | Down Payment | `operational/down-payment` | 6 | ✅ |
| PURCHASING | `PURCHASE` | Purchase | `purchasing/purchase` | 1 | ✅ |
| PURCHASING | `PURCHASE_VERIFICATION` | Purchase Verif | `purchasing/purchase-verification` | 2 | ✅ |
| PURCHASING | `PURCHASE_CONFIRMATION` | Purchase Confirm | `purchasing/purchase-confirmation` | 3 | ✅ |
| PURCHASING | `PURCHASE_PAYMENT` | Purchase Payment | `purchasing/purchase-payment` | 4 | ✅ |
| REPORT | `ORDER-DETAIL` | Order Detail | `report/order-detail` | 1 | ✅ |
| REPORT | `PROFIT_LOSS` | Profit & Loss | `report/profit-loss` | 1 | ✅ |
| REPORT | `DRIVER_SALARY` | Driver Salary | `report/driver-salary` | 2 | ✅ |
| REPORT | `DRIVER_TONASE` | Driver Tonase | `report/driver-tonase` | 3 | ✅ |
| REPORT | `FLEET_TONASE` | Fleet Tonase | `report/fleet-tonase` | 4 | ✅ |
| REPORT | `ALL_ORDER_LIST` | All Order List | `report/all-order-list` | 5 | ✅ |
| REPORT | `MN_RPT_MAINT_FLEET` | Maintenance Per Fleet | `report/maintenance-fleet` | 6 | ✅ (kode semi-generate) |
| REPORT | `MN_RPT_MAINT_COMPINT` | Maintenance Per Fleet Company | `report/maintenance-company-internal` | 7 | ✅ (kode semi-generate) |
| REPORT | `MN_RPT_SUPPLIER_PURCHASE` | Supplier Purchase Report | `report/supplier` | 8 | ✅ (kode semi-generate) |
| REPORT | `MN_RPT_MAINT_ITEM` | Report Maintenance Item | `report/maintenance-item` | 9 | ❌ **TANPA ROUTE** |
| VENDOR | `VENDOR_ORDER_WAITING` | Vendor Waiting Order | `vendor/order/waiting` | 1 | ✅ |
| VENDOR | `VENDOR_INV_UNPAID` | Vendor Unpaid Invoice | `vendor/invoice/unpaid` | 2 | ✅ |
| VENDOR | `VENDOR_INV_PAID` | Vendor Paid Invoice | `vendor/invoice/paid` | 3 | ✅ |
| VENDOR | `VENDOR_PAY_LIST` | Vendor Payment List | `vendor/payment` | 4 | ✅ |
| WAREHOUSE_PARENT | `MAINTENANCE` | Maintenance | `warehouse/maintenance` | 1 | ✅ |

---

## 5. Temuan / Masalah

| # | Masalah | Detail |
|---|---|---|
| 1 | **FINANCE kosong/mati** | Semua anaknya dipindah oleh migrasi `2026_09_04`, `2026_10_02`, `2026_10_10`. Kini menu `#` tanpa isi. |
| 2 | **Modul finansial terpecah** | `FAKTUR` (AR), `VENDOR` (AP), `DIRECT_PAYMENT`, `BANK` = satu domain **Finance**, tapi jadi 4 top-level. |
| 3 | **MASTER jadi tempat sampah** | 18 anak flat: master sejati + referensi + konfigurasi sistem (Menu, Company) bercampur. |
| 4 | **DATA vs MASTER tumpang tindih** | Fleet Owner, Drop/Pickup Location, Route, Tonase Bonus = master data tapi di modul `DATA`. |
| 5 | **INVENTORY vs WAREHOUSE_PARENT** | Warehouse/Unit/Location/Category ada di Inventory; `WAREHOUSE_PARENT` cuma 1 menu (Maintenance). |
| 6 | **Kode tidak konsisten** | Campur `snake_case` (`PROFIT_LOSS`) & `kebab` (`ORDER-DETAIL`) + auto-generate tak terbaca: `MN260521090715`, `MN251127221320`, `MN251127221422`, `MN_RPT_*`. |
| 7 | **`url` jadi identitas** | Active-state di `sidebar.blade.php` menebak dari `url`; rapuh. Kolom `url` juga varchar(50). |
| 8 | **Ikon dobel sumber** | `icon` di DB (Boxicons, tak dipakai) + map hardcode `$childIcons`/`$parentIconFallbacks` di sidebar. |
| 9 | **Change Password jadi menu** | url `#`, bukan sidebar di ERP standar (harusnya aksi profil). |
| 10 | **Tidak ada metadata modern** | Tidak ada `type`, `route_name`, `is_active`, `permission`, `badge`. |
| 11 | **Menu tanpa route** | `MN_RPT_MAINT_ITEM` (`report/maintenance-item`) — link mati. |
| 12 | **Dobel sumber nama** | Nama di kolom `name`/`nama` **dan** di `resources/lang/{en,id}/menu_*.php`. |

---

## 6. Usulan Struktur Target (ERP-style)

**Level 1 = 8 modul** (meniru Odoo/ERPNext). `url` dipertahankan agar route/controller tidak berubah — hanya `parentCode`, `code`, `sort`, `icon` yang dirapikan.

```
1. Dashboard              (/home)

2. Master Data            (group)
   ├─ Business Partner : Customer, Supplier, Fleet Owner
   ├─ Fleet & Asset    : Fleet, Fleet Type, Fleet Brand, Fleet Company
   ├─ Organization/HR  : Employee, Position, Company
   ├─ Product & Stock  : Item, Item Category, Item Unit, Item Location, Material
   ├─ Logistics        : Route, Location, Pickup Location, Drop Location, Tonase Bonus
   └─ Finance Ref.     : Cost Component, Component Price Log, Bank Sender,
                         Bank Receiver, Transaction Type, Due Date

3. Sales & Order          (group)   ← dari OPERATIONAL
   Order, Order Monitoring, Office Order, Down Payment,
   Not Return DO, Return DO, Bon UJT

4. Procurement            (group)   ← dari PURCHASING
   Purchase, Purchase Verification, Purchase Confirmation, Purchase Payment

5. Inventory & Warehouse  (group)   ← gabung INVENTORY + WAREHOUSE_PARENT
   Item Stock, Kartu Stock, Stock Sync, Warehouse, Maintenance

6. Finance                (group)   ← gabung FINANCE + FAKTUR + VENDOR + DIRECT_PAYMENT + BANK
   ├─ Receivable (AR)  : Create Invoice, Unpaid, Paid, Invoice Payment, Payment Transaction
   ├─ Payable (AP)     : Vendor Waiting Order, Vendor Unpaid/Paid Invoice, Vendor Payment List
   ├─ Cash & Bank      : Bank Account, User Bank, Config Bank, Transfer Fund, Expense, Bank Book
   └─ Direct Payment   : Unpaid Order, Paid Order, Payment List

7. Reports                (group)
   ├─ Sales & Order    : Order Detail, All Order List, Profit & Loss
   ├─ Fleet            : Driver Salary, Driver Tonase, Fleet Tonase,
   │                     Maintenance per Fleet / per Fleet Company
   └─ Procurement      : Supplier Purchase Report

8. Administration         (group)
   ├─ User Management  : User, Role
   └─ System Setting   : Company Setting, Menu, Activity Log
```

> ⚠️ **Catatan level:** struktur di atas punya 3 level (Modul → Grup → Menu). `sidebar.blade.php` saat ini hanya render 2 level. Pilih salah satu:
> - **(A) 3 level** → perlu ubah sidebar (dukung nested group).
> - **(B) 2 level** → grup (AR/AP/Cash) di-flatten jadi anak langsung di bawah modul Finance.

---

## 7. Checklist Filter (ISI DI SINI)

Isi kolom **Status** dengan salah satu: `KEEP` / `MERGE→...` / `RENAME` / `DELETE` / `TBD`.

### 7.1 Menu utama

| Code | Status | Target modul | Catatan |
|---|---|---|---|
| `SETTING` | TBD | Administration | Pindahkan Change Password ke profil |
| `FINANCE` | TBD | Finance | Kosong → jadikan induk Finance / hapus |
| `OPERATIONAL` | TBD | Sales & Order | |
| `DIRECT_PAYMENT` | TBD | Finance → Direct Payment | |
| `VENDOR` | TBD | Finance → Payable | |
| `FAKTUR` | TBD | Finance → Receivable | |
| `INVENTORY` | TBD | Inventory & Warehouse | |
| `BANK` | TBD | Finance → Cash & Bank | |
| `PURCHASING` | TBD | Procurement | |
| `WAREHOUSE_PARENT` | TBD | Inventory & Warehouse | |
| `REPORT` | TBD | Reports | |
| `DATA` | TBD | Master Data | |
| `MASTER` | TBD | Master Data | |
| `ADMINISTRATOR` | TBD | Administration | |

### 7.2 Sub-menu

| Code | Status | Catatan |
|---|---|---|
| `CHANGE_PASS` | TBD | Aksi profil, bukan menu |
| `USER` | TBD | |
| `ROLE` | TBD | |
| `COMPANY_SETTING` | TBD | |
| `ACTIVITY_LOG` | TBD | |
| `BANK` (Bank Account) | TBD | |
| `USER_BANK` | TBD | |
| `CONFIG_BANK` | TBD | |
| `TRANSFER_FUND` | TBD | |
| `EXPENSE` | TBD | |
| `BANK_BOOK` | TBD | |
| `FLEET_OWNER` | TBD | |
| `DROP_LOCATION` | TBD | |
| `PICKUP_LOCATION` | TBD | |
| `ROUTE` | TBD | |
| `TONASE_BONUS` | TBD | |
| `DIRECT_PAYMENT_UNPAID` | TBD | |
| `DIRECT_PAYMENT_PAID` | TBD | |
| `DIRECT_PAYMENT_LIST` | TBD | |
| `FAKTUR_PEMBUATAN` | TBD | |
| `FAKTUR_BELUM_LUNAS` | TBD | |
| `FAKTUR_LUNAS` | TBD | |
| `FAKTUR_PEMBAYARAN` | TBD | |
| `FAKTUR_TRANSAKSI_PEMBAYARAN` | TBD | |
| `ITEM` | TBD | |
| `STOCK` | TBD | |
| `STOCK_TRANSACTION` | TBD | |
| `WAREHOUSE` | TBD | |
| `SUPPLIER` | TBD | |
| `ITEM_UNIT` | TBD | |
| `ITEM_LOCATION` | TBD | |
| `ITEM_CATEGORY` | TBD | |
| `MN260521090715` (Sync Stock) | TBD | Rename kode |
| `FLEET_BRAND` | TBD | |
| `FLEET_TYPE` | TBD | |
| `POSITION` | TBD | |
| `EMPLOYEE` | TBD | |
| `FLEET` | TBD | |
| `UNIT` | TBD | |
| `CUSTOMER` | TBD | |
| `COST_COMPONENT` | TBD | |
| `LOCATION` | TBD | |
| `MATERIAL` | TBD | |
| `BANK_SENDER` | TBD | |
| `BANK_RECEIVER` | TBD | |
| `DUE_DATE` | TBD | |
| `FLEET_COMPANY` | TBD | |
| `TRANSACTION_TYPE` | TBD | |
| `COMPANY` | TBD | |
| `MN251127221320` (Menu) | TBD | Rename kode |
| `MN251127221422` (Component Log) | TBD | Rename kode |
| `ORDER` | TBD | |
| `ORDER_MONITORING` | TBD | |
| `NOT_RETURN_DO` | TBD | |
| `BON_UJT` | TBD | |
| `RETURN_DO` | TBD | |
| `ORDER_OFFICE` | TBD | |
| `DOWN_PAYMENT` | TBD | |
| `PURCHASE` | TBD | |
| `PURCHASE_VERIFICATION` | TBD | |
| `PURCHASE_CONFIRMATION` | TBD | |
| `PURCHASE_PAYMENT` | TBD | |
| `ORDER-DETAIL` | TBD | Kode pakai `-`, seragamkan |
| `PROFIT_LOSS` | TBD | |
| `DRIVER_SALARY` | TBD | |
| `DRIVER_TONASE` | TBD | |
| `FLEET_TONASE` | TBD | |
| `ALL_ORDER_LIST` | TBD | |
| `MN_RPT_MAINT_FLEET` | TBD | Rename kode |
| `MN_RPT_MAINT_COMPINT` | TBD | Rename kode |
| `MN_RPT_SUPPLIER_PURCHASE` | TBD | Rename kode |
| `MN_RPT_MAINT_ITEM` | **DELETE?** | ⚠️ Tanpa route (`report/maintenance-item`) |
| `VENDOR_ORDER_WAITING` | TBD | |
| `VENDOR_INV_UNPAID` | TBD | |
| `VENDOR_INV_PAID` | TBD | |
| `VENDOR_PAY_LIST` | TBD | |
| `MAINTENANCE` | TBD | |

---

## 8. Usulan Perbaikan Skema Tabel `menu`

| Kolom baru | Tipe | Fungsi |
|---|---|---|
| `route_name` | varchar(100) | identitas stabil untuk active-state (ganti ketergantungan `url`) |
| `type` | enum(`group`,`link`,`divider`) | bedakan header grup vs link |
| `permission` | varchar(100) nullable | kunci izin granular (opsional) |
| `is_active` | boolean default 1 | sembunyikan tanpa hapus |
| `level` | tinyint | cache kedalaman (opsional) |
| `badge` / `target` | varchar nullable | opsional |

Penyesuaian lain:
- `url` → naikkan ke varchar(150–191).
- `icon` → pakai **satu** set ikon (mis. Tabler/MDI) & buang hardcode di sidebar.
- Pertimbangkan hapus `name`/`nama` → pakai **translation key** (`lang/menu_*.php` sudah ada).
- `role_menu` bisa jadi pivot granular (`can_view/can_create/can_edit/can_delete`).

---

## 9. Konvensi Kode & Nama (Target)

Pola `<MODUL>_<FITUR>` (SCREAMING_SNAKE_CASE):

```
MASTER_CUSTOMER, MASTER_FLEET
SALES_ORDER, SALES_ORDER_MONITORING
PURC_PURCHASE, PURC_PURCHASE_VERIFICATION
INV_ITEM, INV_STOCK, INV_STOCK_SYNC
FIN_AR_INVOICE, FIN_AP_VENDOR_PAYMENT, FIN_CASH_BANK_ACCOUNT, FIN_DP_UNPAID
RPT_SALES_ORDER_DETAIL, RPT_FLEET_DRIVER_SALARY
ADM_USER, ADM_ROLE
```

Ganti semua kode auto-generate (`MN260521090715`, `MN251127221320`, `MN251127221422`, `MN_RPT_*`) dan kode campur (`ORDER-DETAIL`).

---

## 10. Rencana Implementasi (Aman & Bertahap)

1. **Satu data-migration baru** (pola repo: `DB::table('menu')->insert/update` + copy `role_menu`) — jangan edit migrasi lama.
2. **Remap `role_menu`**: saat memindahkan/mengganti `code`, salin permission lama → kode baru (contoh: `create_vendor_menu_structure.php`), lalu hapus mapping lama. **Ini titik risiko utama.**
3. **Pertahankan `url`** agar route/controller tidak berubah.
4. **Perbarui `sidebar.blade.php`**: hapus map ikon hardcode, baca dari DB; tambah dukungan 3-level bila dipilih.
5. **Hapus `FINANCE`** (atau jadikan induk Finance), **hapus Change Password** dari sidebar.
6. **Uji**: `php artisan tinker --execute` (dump `menu` + `role_menu`) atau `database/tmp_dump_menu.php`.

### Titik risiko
- `role_menu` (role kehilangan akses bila remap salah).
- Hardcode ikon di `sidebar.blade.php`.
- Kode auto-generate yang mungkin direferensikan di tempat lain.

---

## 11. Langkah Berikutnya

1. **User mengisi §7** (Status: KEEP/MERGE/RENAME/DELETE).
2. Dari hasil filter, susun **tabel mapping** `kode_lama → kode_baru → parentCode_baru → sort`.
3. Baru generate file migrasi restrukturisasi + patch sidebar.
