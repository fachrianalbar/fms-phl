<?php

use App\Helpers\GenerateCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menu "Order Menunggu Nota" (Pembayaran Langsung) dipisah menjadi sub-menu sendiri
     * (sebelumnya order standalone belum dibuat nota digabung satu halaman dengan Nota Belum Lunas).
     *
     * Struktur final menu PEMBAYARAN LANGSUNG (DIRECT_PAYMENT):
     *   1. DIRECT_PAYMENT_WAITING - Order Menunggu Nota  (direct-payment/order/waiting)  ← generate nota di sini
     *   2. DIRECT_PAYMENT_UNPAID  - Nota Belum Lunas    (direct-payment/order/unpaid)   ← bayar nota di sini
     *   3. DIRECT_PAYMENT_PAID    - Order Lunas          (direct-payment/order/paid)
     *   4. DIRECT_PAYMENT_LIST    - Daftar Pembayaran    (direct-payment/payment)
     */
    public function up(): void
    {
        $now = Carbon::now();

        // 1. Insert sub-menu Order Menunggu Nota (sort 1)
        $exists = DB::table('menu')->where('code', 'DIRECT_PAYMENT_WAITING')->exists();
        if (! $exists) {
            DB::table('menu')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'code' => 'DIRECT_PAYMENT_WAITING',
                'name' => 'Direct Payment Waiting Order',
                'nama' => 'Order Menunggu Nota',
                'parentCode' => 'DIRECT_PAYMENT',
                'url' => 'direct-payment/order/waiting',
                'icon' => null,
                'sort' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. Update penamaan & sort sub-menu yang sudah ada
        DB::table('menu')->where('code', 'DIRECT_PAYMENT_UNPAID')->update([
            'name' => 'Direct Payment Unpaid Nota',
            'nama' => 'Nota Belum Lunas',
            'sort' => 2,
            'updated_at' => $now,
        ]);
        DB::table('menu')->where('code', 'DIRECT_PAYMENT_PAID')->update(['sort' => 3, 'updated_at' => $now]);
        DB::table('menu')->where('code', 'DIRECT_PAYMENT_LIST')->update(['sort' => 4, 'updated_at' => $now]);

        // 3. Copy permission: role yang bisa mengakses menu direct payment lain
        //    otomatis bisa mengakses Order Menunggu Nota.
        $roleCodes = DB::table('role_menu')
            ->where('menuCode', 'DIRECT_PAYMENT_UNPAID')
            ->pluck('roleCode')
            ->unique()
            ->values()
            ->all();

        if (! in_array('SPRADMIN', $roleCodes)) {
            $roleCodes[] = 'SPRADMIN';
        }

        foreach ($roleCodes as $roleCode) {
            $hasAccess = DB::table('role_menu')
                ->where('roleCode', $roleCode)
                ->where('menuCode', 'DIRECT_PAYMENT_WAITING')
                ->exists();

            if (! $hasAccess) {
                DB::table('role_menu')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'code' => GenerateCode::generateCode('TRL', true),
                    'roleCode' => $roleCode,
                    'menuCode' => 'DIRECT_PAYMENT_WAITING',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $now = Carbon::now();

        // 1. Hapus menu & permission Order Menunggu Nota
        DB::table('role_menu')->where('menuCode', 'DIRECT_PAYMENT_WAITING')->delete();
        DB::table('menu')->where('code', 'DIRECT_PAYMENT_WAITING')->delete();

        // 2. Kembalikan nama & urutan sub-menu seperti semula
        DB::table('menu')->where('code', 'DIRECT_PAYMENT_UNPAID')->update([
            'name' => 'Direct Payment Unpaid Order',
            'nama' => 'Order Belum Lunas',
            'sort' => 1,
            'updated_at' => $now,
        ]);
        DB::table('menu')->where('code', 'DIRECT_PAYMENT_PAID')->update(['sort' => 2, 'updated_at' => $now]);
        DB::table('menu')->where('code', 'DIRECT_PAYMENT_LIST')->update(['sort' => 3, 'updated_at' => $now]);
    }
};
