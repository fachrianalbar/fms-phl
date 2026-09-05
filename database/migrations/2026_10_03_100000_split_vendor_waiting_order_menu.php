<?php

use App\Helpers\GenerateCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menu "Order Menunggu Nota" dipisah menjadi sub-menu sendiri
     * (sebelumnya satu halaman dengan Invoice Belum Lunas).
     *
     * Struktur final menu VENDOR:
     *   1. VENDOR_ORDER_WAITING  - Order Menunggu Nota  (vendor/order/waiting)
     *   2. VENDOR_INV_UNPAID     - Invoice Belum Lunas  (vendor/invoice/unpaid)
     *   3. VENDOR_INV_PAID       - Invoice Lunas        (vendor/invoice/paid)
     *   4. VENDOR_PAY_LIST       - Daftar Pembayaran    (vendor/payment)
     */
    public function up(): void
    {
        $now = Carbon::now();

        // 1. Insert sub-menu Order Menunggu Nota (sort 1 — awal alur kerja)
        $exists = DB::table('menu')->where('code', 'VENDOR_ORDER_WAITING')->exists();
        if (! $exists) {
            DB::table('menu')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'code' => 'VENDOR_ORDER_WAITING',
                'name' => 'Vendor Waiting Order',
                'nama' => 'Order Menunggu Nota',
                'parentCode' => 'VENDOR',
                'url' => 'vendor/order/waiting',
                'icon' => null,
                'sort' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. Geser urutan sub-menu yang sudah ada
        DB::table('menu')->where('code', 'VENDOR_INV_UNPAID')->update(['sort' => 2, 'updated_at' => $now]);
        DB::table('menu')->where('code', 'VENDOR_INV_PAID')->update(['sort' => 3, 'updated_at' => $now]);
        DB::table('menu')->where('code', 'VENDOR_PAY_LIST')->update(['sort' => 4, 'updated_at' => $now]);

        // 3. Copy permission: role yang bisa mengakses menu vendor lain
        //    otomatis bisa mengakses Order Menunggu Nota.
        $roleCodes = DB::table('role_menu')
            ->where('menuCode', 'VENDOR_INV_UNPAID')
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
                ->where('menuCode', 'VENDOR_ORDER_WAITING')
                ->exists();

            if (! $hasAccess) {
                DB::table('role_menu')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'code' => GenerateCode::generateCode('TRL', true),
                    'roleCode' => $roleCode,
                    'menuCode' => 'VENDOR_ORDER_WAITING',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = Carbon::now();

        // 1. Hapus menu & permission Order Menunggu Nota
        DB::table('role_menu')->where('menuCode', 'VENDOR_ORDER_WAITING')->delete();
        DB::table('menu')->where('code', 'VENDOR_ORDER_WAITING')->delete();

        // 2. Kembalikan urutan sub-menu seperti semula
        DB::table('menu')->where('code', 'VENDOR_INV_UNPAID')->update(['sort' => 1, 'updated_at' => $now]);
        DB::table('menu')->where('code', 'VENDOR_INV_PAID')->update(['sort' => 2, 'updated_at' => $now]);
        DB::table('menu')->where('code', 'VENDOR_PAY_LIST')->update(['sort' => 3, 'updated_at' => $now]);
    }
};
