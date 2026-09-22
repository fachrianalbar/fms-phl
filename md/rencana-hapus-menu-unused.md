# Rencana Hapus Menu Unused (10 Menu)

> ⚠️ **DOKUMEN INI SUDAH SELESAI DIEKSEKUSI.** Ringkasan hasil + rekomendasi restrukturisasi menu ERP ada di **`md/restrukturisasi-menu.md`**.
>
> Dokumen ini disimpan sebagai catatan analisis awal (dependency + klasifikasi risiko).

> Dokumen rencana **penghapusan bersih** untuk 10 menu yang tidak dipakai, hasil analisis dependency (`grep` lintas `app/`, `routes/`, `resources/`, `database/`).
>
> 📌 **STATUS: MENUNGGU KONFIRMASI SCOPE.** Ada 3 tingkat risiko — lihat §2. Bagian §5 berisi pertanyaan yang harus dijawab sebelum eksekusi.

---

## 1. Daftar 10 Menu yang Dihapus

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

`role_menu` rows yang ikut terhapus: FLEET_OWNER 2, ITEM_LOCATION 4, BANK_SENDER 1, BANK_RECEIVER 1, DUE_DATE 1, ORDER_MONITORING 1, ORDER_OFFICE 2, DOWN_PAYMENT 2, PURCHASE_VERIFICATION 7, PURCHASE_CONFIRMATION 7.

> 📝 **Koreksi:** Fleet **Company** (`FLEET_COMPANY`) **TIDAK** dihapus — diganti dengan Fleet **Owner** (`FLEET_OWNER`).

---

## 2. Klasifikasi Risiko

| Tier | Fitur | Boleh hapus total? | Alasan |
|---|---|---|---|
| 🟢 **A — AMAN** | Bank Sender, Bank Receiver, Due Date, Purchase Verification, Purchase Confirmation, Down Payment, Office Order | ✅ Ya (menu + route + controller + service + model + view + tabel) | Tidak dipakai fitur lain (hanya relasi kecil yang mudah dibersihkan) |
| 🟡 **B — HATI-HATI** | Fleet Owner, Item Location | ⚠️ Menu + halaman saja. **Model & tabel JANGAN dihapus** | Model/tabelnya dipakai fitur lain (lihat §3) |
| 🔴 **C — PERLU KEPUTUSAN** | Order Monitoring | ❓ Tergantung | Terhubung ke **public order tracking** + notifikasi email |

---

## 3. Detail per Fitur

### 🟢 A1. Bank Sender (`BANK_SENDER`)
**Hapus:**
- Menu `BANK_SENDER` + `role_menu` (1)
- Route `master/bank-sender` + datatable (`routes/master.php`)
- `app/Http/Controllers/Master/BankSenderController.php`
- `app/Services/Master/BankSenderService.php`
- `app/Models/Master/BankSender.php`
- `resources/views/master/bank-sender/` (index, create, edit)
- Sidebar icon: `'master/bank-sender' => 'mdi-bank-transfer'`
- Tabel DB: `bank_sender` *(opsional, lihat §5)*

**Refs lain:** tidak ada (Transfer Fund memakai `UserBank`, bukan `BankSender`).

---

### 🟢 A2. Bank Receiver (`BANK_RECEIVER`)
**Hapus:**
- Menu `BANK_RECEIVER` + `role_menu` (1)
- Route `master/bank-receiver` + datatable
- `app/Http/Controllers/Master/BankReceiverController.php`
- `app/Services/Master/BankReceiverService.php`
- `app/Models/Master/BankReceiver.php`
- `resources/views/master/bank-receiver/` (index, create, edit)
- Sidebar icon: `'master/bank-receiver' => 'mdi-bank-transfer-in'`
- Tabel DB: `bank_receiver` *(opsional)*

**Refs lain:** tidak ada.

---

### 🟢 A3. Due Date (`DUE_DATE`)
**Hapus:**
- Menu `DUE_DATE` + `role_menu` (1)
- Route `master/due-date` + datatable
- `app/Http/Controllers/Master/DueDateController.php`
- `app/Services/Master/DueDateService.php`
- `app/Models/Master/DueDate.php`
- `resources/views/master/due-date/` (index, edit)
- Sidebar icon: `'master/due-date' => 'mdi-calendar-clock-outline'`
- Tabel DB: `due_date` *(opsional)*

**Refs lain:** tidak ada. (Kolom `dueDate` di Purchase/Fleet/Customer/Invoice itu **field biasa**, bukan model `DueDate` — tidak terpengaruh.)

---

### 🟢 A4. Purchase Verification (`PURCHASE_VERIFICATION`)
**Hapus:**
- Menu `PURCHASE_VERIFICATION` + `role_menu` (7)
- Route `purchasing/purchase-verification`, datatable `purchase-verification`, dan `purchase-verification-detail/{id}` (delete)
- `app/Http/Controllers/Purchasing/PurchaseVerificationController.php`
- `app/Services/Purchasing/PurchaseVerificationService.php`
- `resources/views/purchasing/purchase-verification/` (index, edit)
- Sidebar icon

**Refs lain:** tidak ada (PurchaseController/PurchaseService tidak memakainya). Tidak ada tabel khusus.

---

### 🟢 A5. Purchase Confirmation (`PURCHASE_CONFIRMATION`)
**Hapus:**
- Menu `PURCHASE_CONFIRMATION` + `role_menu` (7)
- Route `purchasing/purchase-confirmation`, datatable, dan ajax `purchase-detail`
- `app/Http/Controllers/Purchasing/PurchaseConfirmationController.php`
- `app/Services/Purchasing/PurchaseConfirmationService.php`
- `resources/views/purchasing/purchase-confirmation/` (index, edit)
- Sidebar icon

**Refs lain:** tidak ada. Tidak ada tabel khusus.

---

### 🟢 A6. Down Payment (`DOWN_PAYMENT`)
**Hapus:**
- Menu `DOWN_PAYMENT` + `role_menu` (2)
- Route: `operational/down-payment`, `operational/down-payment-detail`, `pdf-down-payment/{id}`, datatable `down-payment` & `down-payment-detail`, ajax `down-payment-data` (`routes/operational.php`)
- `app/Http/Controllers/Operational/DownPaymentController.php`
- `app/Http/Controllers/Operational/DownPaymentDetailController.php`
- `app/Services/Operational/DownPaymentService.php`
- `app/Services/Operational/DownPaymentDetailService.php`
- `app/Models/Operational/DownPayment.php`
- `app/Models/Operational/DownPaymentDetail.php`
- `app/Enums/DownPaymentType.php`
- `resources/views/operational/down-payment/` (index, create, edit, show, pdf/)
- Sidebar icon
- Tabel DB: `down_payment`, `down_payment_detail` *(opsional)*

**Refs lain:** `app/Models/Master/Employee.php` → hapus relasi `downPayment()`.

---

### 🟢 A7. Office Order (`ORDER_OFFICE`)
**Hapus:**
- Menu `ORDER_OFFICE` + `role_menu` (2)
- Route `operational/office-order` + datatable
- `app/Http/Controllers/Operational/OrderOfficeController.php`
- `resources/views/operational/office-order/index.blade.php`
- Sidebar icon: `'operational/office-order' => 'mdi-briefcase-outline'`

**Refs lain:** tidak ada. Tidak ada service/model/tabel khusus (pakai model `Route` & `TonaseBonus` yang tetap dipakai fitur lain).

---

### 🟡 B1. Fleet Owner (`FLEET_OWNER`) — HATI-HATI

> Catatan: menu "Fleet Owner" di UI sebenarnya = master **driver** (`fleet_driver`, controller `FleetDriverController`).

**Boleh hapus (menu + UI):**
- Menu `FLEET_OWNER` + `role_menu` (2)
- Route `data/fleet-owner` + datatable (`routes/data.php`)
- `app/Http/Controllers/Data/FleetDriverController.php`
- `resources/views/data/fleet-owner/` (index, create, edit)
- Sidebar icon: `'data/fleet-owner' => 'mdi-account-hard-hat'`

**JANGAN hapus (dipakai fitur lain):**
- `app/Models/Data/FleetDriver.php` + tabel `fleet_driver`
- `app/Services/Data/FleetDriverService.php`

**Dipakai di (bukti):**
- `app/Models/Operational/Order.php` → relasi `belongsTo(FleetDriver::class, 'fleetDriverCode', 'code')`
- `app/Http/Controllers/Operational/OrderController.php` → inject `FleetDriverService`
- `app/Http/Controllers/Report/AllOrderListController.php` → inject `FleetDriverService`
- `app/Http/Controllers/Operational/OrderOfficeController.php` → inject `FleetDriverService`

> ⚠️ Field `fleetDriverCode` pada Order **tetap dipakai**. Jadi model/service/tabel harus tetap ada.

---

### 🟡 B2. Item Location (`ITEM_LOCATION`) — HATI-HATI
**Boleh hapus (menu + UI):**
- Menu `ITEM_LOCATION` + `role_menu` (4)
- Route `inventory/item-location` + datatable
- `app/Http/Controllers/Inventory/ItemLocationController.php`
- `resources/views/inventory/item-location/` (index, create, edit)
- Sidebar icon: `'inventory/item-location' => 'mdi-map-marker-radius-outline'`

**Perlu refactor (agar tidak error):**
- `app/Models/Inventory/Item.php` → hapus relasi `itemLocation()` + `itemLocationCode` dari `$fillable` *(kolom DB boleh tetap)*
- `app/Http/Controllers/Inventory/ItemController.php` → hapus inject `ItemLocationService` + passing `location`
- `resources/views/inventory/items/edit.blade.php` → hapus dropdown Item Location

**JANGAN hapus (dipakai Item):** `app/Services/Inventory/ItemLocationService.php` + `app/Models/Inventory/ItemLocation.php` + tabel `item_location` — kecuali Item di-refactor penuh.

---

### 🔴 C. Order Monitoring (`ORDER_MONITORING`) — PERLU KEPUTUSAN
**Hapus (bagian menu):**
- Menu `ORDER_MONITORING` + `role_menu` (1)
- Route `operational/monitoring-order` + datatable
- `app/Http/Controllers/Operational/OrderMonitoringController.php`
- `app/Services/Operational/OrderMonitoringService.php`
- `resources/views/operational/monitoring-order/` (index, show, notif/email)
- Sidebar icon: `'operational/monitoring-order' => 'mdi-truck-fast-outline'`

**Terkait (harus diputuskan — apakah ikut dihapus?):**
- `app/Http/Controllers/GuestOrderMonitoringController.php`
- `routes/guest.php` → `order-track`, `guest-order-shipment`, `guest-order-shipment-suggestion` **(halaman public tracking customer)**
- `routes/api.php` → `order-tracking`
- `app/Helpers/SendNotif.php` → method `EmailTruckOrderMonitoring()`
- `app/Jobs/SendEmailTruckNotification.php` (dispatch dari OrderMonitoringController)

**JANGAN hapus (dipakai fitur lain):**
- `app/Models/Operational/OrderTracking.php` → dipakai `API/TelegramController`, `routes/console.php`
- `app/Models/Operational/TelegramUser.php` → dipakai `API/TelegramController`

> ⚠️ Menghapus `order-track` = menghapus halaman **pelacakan order publik** untuk customer.

---

## 4. Rencana Eksekusi (setelah konfirmasi)

1. **Migration baru** (`php artisan make:migration`) berisi:
   - `DB::table('role_menu')->whereIn('menuCode', [...])->delete();`
   - `DB::table('menu')->whereIn('code', [...])->delete();`
   - *(opsional)* `Schema::dropIfExists('bank_sender')`, dst — lihat §5.
2. **Edit route files**: hapus entri route + datatable + ajax terkait di `routes/master.php`, `routes/inventory.php`, `routes/operational.php`, `routes/purchasing.php` (+ `routes/guest.php`, `routes/api.php` bila Order Monitoring ikut penuh).
3. **Hapus file**: controller, service, model, view, enum, job sesuai §3.
4. **Refactor dependency**: `Employee` (Down Payment), `Item` + `ItemController` + `items/edit` (Item Location), `FleetController` (biarkan pakai `FleetCompanyService`).
5. **Bersihkan `sidebar.blade.php`**: hapus entri `$childIcons` + `$parentIconFallbacks` terkait.
6. **Verifikasi**: `php artisan route:list`, `php artisan config:clear`, buka halaman-halaman terkait fitur yang tetap hidup (Order, Purchase, Fleet, Vendor, Report).

### Titik risiko
- `role_menu` — pastikan tidak ada role yang benar-benar butuh menu ini.
- `FleetController` — jangan hapus `FleetCompanyService`.
- `ItemController` — jangan hapus `ItemLocationService` sebelum refactor.
- `Order Monitoring` — public tracking & notifikasi.

---

## 5. Pertanyaan Konfirmasi (WAJIB dijawab sebelum eksekusi)

1. **Tabel DB orphan** — boleh saya `DROP` tabel `bank_sender`, `bank_receiver`, `due_date`, `down_payment`, `down_payment_detail`? Atau cukup hapus menu + kode, **tabel dibiarkan** (lebih aman, data historis tetap ada)?
2. **Order Monitoring** — apakah ikut menghapus **public order tracking** (`order-track` / guest) + notifikasi email? Atau **menu saja** yang dihapus (fitur tracking publik tetap jalan)?
3. **Fleet Owner & Item Location** — setuju kalau **hanya menu + halaman** yang dihapus, sedangkan **model/tabel/service tetap** (karena dipakai fitur lain)?

---

## 6. Rekomendasi Default (jika tidak ada preferensi lain)

- **Tabel**: **jangan drop** — cukup hapus menu + kode. Data & kolom tetap (bisa di-drop nanti terpisah).
- **Order Monitoring**: hapus **menu + controller + service + view monitoring**, **TAPI pertahankan** `order-track` (public) + notifikasi, kecuali Anda konfirmasi ikut dihapus.
- **Fleet Owner & Item Location**: menu + halaman dihapus, model/service/tabel tetap.
