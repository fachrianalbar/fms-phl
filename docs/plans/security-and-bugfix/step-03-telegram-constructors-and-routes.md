# Step 03 — Perbaikan Bug Telegram Webhook, Constructor Null-Safe, & Broken Routes

> **Plan Utama:** [../security-and-bugfix-plan.md](../security-and-bugfix-plan.md)
> **Prasyarat:** Step 02 selesai & konfirmasi Open Question #2 (hapus 3 custom broken routes & dead code terkait) dan #3 (hapus seluruh integrasi Telegram yang tidak dipakai)
> **Status:** `done`

---

## Tujuan Langkah
1. Memperbaiki bug HTTP 500 pada `TelegramController::handleWebhook` untuk payload non-teks dan menambahkan verifikasi secret token webhook.
2. Mencegah crash `Auth::user()->languange` pada `__construct()` controller ketika di-resolve di luar session user.
3. Menghilangkan seluruh **60 broken routes** (57 dari `Route::resource` yang belum memakai `->only()`/`->except()`, dan 3 custom route yang method-nya hilang).

---

## Daftar File yang Disentuh
- `[MODIFY]` `app/Http/Controllers/API/TelegramController.php`
- `[MODIFY]` Controller dengan akses `Auth::user()` di `__construct()`:
  - `app/Http/Controllers/Master/FleetController.php`
  - `app/Http/Controllers/Master/CustomerController.php`
  - `app/Http/Controllers/Master/LocationController.php`
  - `app/Http/Controllers/Operational/ReturnDoController.php`
  - `app/Http/Controllers/Operational/OrderController.php`
  - `app/Http/Controllers/Purchasing/PurchaseController.php`
  - `app/Http/Controllers/Purchasing/PurchasePaymentController.php`
  - `app/Http/Controllers/Finance/VendorPaymentListController.php`
- `[MODIFY]` File route dengan `Route::resource` berlebih / custom broken routes:
  - `routes/administrator.php`, `routes/bank.php`, `routes/data.php`, `routes/inventory.php`, `routes/master.php`, `routes/operational.php`, `routes/report.php`, `routes/warehouse.php`
- `[MODIFY]` *(Tergantung Open Question #2)* `OrderController.php` / `BonUjtController.php` / `UserController.php` atau Blade view terkait (`order/index.blade.php`, `bon-ujt/index.blade.php`, `administrator/user/balance.blade.php`)

---

## Action Checklist & Detail Perubahan

### 1. Hapus Integrasi Telegram yang Tidak Dipakai
- [x] Hapus `app/Http/Controllers/API/TelegramController.php`, `app/Models/Operational/TelegramUser.php`, dan `app/Jobs/SendTelegramNotification.php`.
- [x] Hapus route `POST /telegram/webhook` dari `routes/api.php`.
- [x] Hapus method `SendNotif::sendTelegram()` di `app/Helpers/SendNotif.php` dan key `'telegram'` di `config/services.php`.

### 2. Null-Safe `__construct()` pada Semua Controller
- [x] Cari semua `__construct` dan helper judul yang membaca `Auth::user()->languange` atau `$this->menuSvc->name` tanpa `?->` (`PurchaseController`, `PurchasePaymentController`, `LocationController`, `FleetController`, `CustomerController`, `FleetTypeController`, `FleetCompanyController`, `FleetBrandController`, `VendorPaymentListController`, `OrderController`, `ReturnDoController`, `RouteController`).
- [x] Ubah menjadi pola null-safe (`Auth::user()?->languange ?? 'id'` dan `$menu?->name ?? ...`).
- [x] Cek kesesuaian kolom `languange` vs `language` di model `User::$fillable` agar konsisten dengan skema DB aktual (`languange`).

### 3. Bersihkan Broken `Route::resource` & Hapus 3 Custom Broken Routes + Dead Code
- [x] Untuk setiap `Route::resource(...)` yang method-nya tidak lengkap (`menu`, `route-detail`, `return-do`, `not-return-do`, `transaction-stock`, `stock`, `activity-log`, `maintenance-detail`, `profit-loss`, `driver-salary`, `order-detail`, `bank-book`), tambahkan `->only([...])` atau `->except([...])` sesuai method yang benar-benar ada di controller-nya.
- [x] Hapus 3 custom broken routes beserta sisa dead code-nya:
  1. `operational/check-order-null-relation` (`OrderController@checkNullRelations`) — dihapus beserta handler `#check-null` & `#modal-null` di `resources/views/operational/order/index.blade.php` dan `resources/views/operational/bon-ujt/index.blade.php`.
  2. `datatable/bon-ujt-order-detail` (`BonUjtController@datatableOrderDetail`) — dihapus dari `routes/operational.php`.
  3. `administrator/store-balance` (`UserController@storeBalance`) & `administrator/user-balance/{id}` (`UserController@balance`) — dihapus beserta `UserController::balance()` dan `resources/views/administrator/user/balance.blade.php`.

---

## Kriteria Selesai & Verifikasi Langkah
- [x] Integrasi Telegram bot webhook/notifikasi yang tidak dipakai telah dihapus bersih.
- [x] Script pengecekan seluruh route menghasilkan **`Broken routes: 0`**.
- [x] Seluruh 68 controller route dapat di-instantiate tanpa error saat `Auth::user()` bernilai `null`.
- [x] Verifikasi lulus: route check (`Broken routes: 0`), `php artisan view:cache && php artisan view:clear`, dan `php artisan test` (`43 passed`).
