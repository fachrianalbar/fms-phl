# Step 01 — Auth, Proteksi `/logs`, & Aktivasi RBAC (`RoleAccess`)

> **Plan Utama:** [../security-and-bugfix-plan.md](../security-and-bugfix-plan.md)
> **Prasyarat:** Konfirmasi Open Question #1 — **Opsi A** (`/logs` & `/administrator/*` khusus `SPRADMIN`; `/bank/*` & `/report/profit-loss` menyesuaikan `RoleMenuSeeder`)
> **Status:** `done`

---

## Tujuan Langkah
Menutup celah autentikasi dan otorisasi tingkat tinggi:
1. Memastikan `/change-password` hanya dapat diakses user yang sudah login (menghilangkan error HTTP 500 saat diakses guest).
2. Membatasi akses `/logs` (`LogViewerController`) khusus untuk role administrator (`SPRADMIN`).
3. Mengisi logika middleware `RoleAccess` (`access`) yang sebelumnya kosong agar penegakan RBAC berjalan di sisi server.

---

## Daftar File yang Disentuh
- `[MODIFY]` `routes/auth.php`
- `[MODIFY]` `routes/web.php`
- `[MODIFY]` `app/Http/Middleware/RoleAccess.php`
- `[MODIFY]` *(Opsional, tergantung Open Question #1)* `routes/administrator.php`, `routes/bank.php`, `routes/report.php`, `routes/master.php`

---

## Action Checklist & Detail Perubahan

### 1. Pindahkan `change-password` ke dalam grup `auth`
- [x] Buka `routes/auth.php` dan hapus baris `Route::resource('change-password', ChangePasswordController::class);`.
  ```diff
  -use App\Http\Controllers\ChangePasswordController;
   use Illuminate\Support\Facades\Route;

  -Route::resource('change-password', ChangePasswordController::class);
   Route::get('/', [AuthController::class, 'login'])->middleware('guest')->name('login');
   Route::post('authenticate', [AuthController::class, 'authenticate'])->name('authenticate');
   Route::post('logout', [AuthController::class, 'logout'])->name('logout');
  ```
- [x] Buka `routes/web.php`, lalu daftarkan `change-password` di dalam `Route::middleware(['auth'])->group(...)` dengan `->only(['index', 'store'])` (karena controller hanya memiliki `index` dan `store`).

### 2. Batasi `/logs` khusus role admin
- [x] Pada `routes/web.php`, bungkus route `logs` dengan middleware `access:SPRADMIN`:
  ```php
  Route::middleware(['auth'])->group(function () {
      Route::resource('change-password', \App\Http\Controllers\ChangePasswordController::class)
          ->only(['index', 'store']);

      Route::middleware(['access:SPRADMIN'])->group(function () {
          Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
      });
      // ...
  });
  ```

### 3. Implementasikan `RoleAccess` Middleware
- [x] Buka `app/Http/Middleware/RoleAccess.php` dan implementasikan pengecekan user & role:
  ```php
  public function handle(Request $request, Closure $next, string ...$roles): Response
  {
      $user = $request->user();

      if (! $user) {
          abort(401);
      }

      if ($user->roleCode === 'SPRADMIN' || $user->role?->name === 'System Administrator') {
          return $next($request);
      }

      if (! empty($roles) && ! in_array($user->roleCode, $roles, true)) {
          abort(403, 'Anda tidak memiliki akses ke halaman ini.');
      }

      return $next($request);
  }
  ```
- [x] *(Sesuai keputusan Opsi A)* Pasang middleware `access` pada modul sensitif: `routes/administrator.php` (`access:SPRADMIN`), `routes/bank.php` (`access:SPRADMIN,SPRUSER` untuk `transfer-fund`, `expense`, `bank-book`, `config-bank`, dan `access:SPRADMIN,SPRUSER,DO,TRL250604212202,TRL250604212231,TRL250604212242` untuk `bank-account`, `user-bank`, `api.user-bank.company`), serta `routes/report.php` (`access:SPRADMIN,SPRUSER,TRL250604212202` untuk `profit-loss`).

---

## Kriteria Selesai & Verifikasi Langkah
- [x] `GET /change-password` tanpa login mengembalikan `302` redirect ke `/` (bukan `500`).
- [x] `GET /logs` sebagai user non-`SPRADMIN` mengembalikan `403`.
- [x] `GET /logs` sebagai `SPRADMIN` mengembalikan `200`.
- [x] Jalankan verifikasi cepat:
  ```bash
  php artisan route:list --path=change-password && php artisan route:list --path=logs && php artisan test
  ```
