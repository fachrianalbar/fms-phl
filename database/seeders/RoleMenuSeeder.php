<?php

namespace Database\Seeders;

use App\Helpers\GenerateCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * RoleMenuSeeder — (re)bangun akses role ke menu setelah MenuSeeder.
 *
 * Tujuan:
 *  1. Super Admin (`SPRADMIN`) mendapat SEMUA menu.
 *  2. Role yang sudah ada TETAP punya akses seperti sebelumnya, memakai
 *     daftar akses EKSPLISIT di `$access` (hasil pemetaan dari data
 *     role_menu asli + rename/gabung kode). Data ditulis hardcoded agar
 *     seeder TIDAK bergantung pada state tabel `role_menu` (yang mungkin
 *     sudah dimodifikasi).
 *  3. Setiap child yang di-assign otomatis menyertakan parent-nya
 *     (sidebar hanya menampilkan child bila parent-nya juga ada di
 *     role_menu).
 *  4. Membersihkan baris role_menu yatim (roleCode yang tidak ada di tabel
 *     `role`, menuCode yang tidak ada di tabel `menu`).
 *
 * Role yang TIDAK terdaftar di `$access` (mis. role baru) memakai
 * `$legacyMap` sebagai fallback (memetakan dari role_menu existing).
 *
 * Idempotent: aman dijalankan berulang.
 */
class RoleMenuSeeder extends Seeder
{
    /**
     * Akses menu EKSPLISIT per role (child codes).
     *
     * `SPRADMIN` tidak perlu dicantumkan — otomatis mendapat semua menu.
     * Parent dari tiap child ditambahkan otomatis saat penulisan.
     *
     * @var array<string, array<int, string>>
     */
    private array $access = [
        // Super User — hampir semua modul kecuali Administrator.
        'SPRUSER' => [
            'BANK_ACCOUNT', 'BANK_BOOK', 'COMPANY',
            'COST_COMPONENT', 'CUSTOMER', 'DIRECT_PAYMENT_LIST',
            'DIRECT_PAYMENT_PAID', 'DIRECT_PAYMENT_UNPAID', 'DRIVER_SALARY',
            'EMPLOYEE', 'FAKTUR_BELUM_LUNAS', 'FAKTUR_LUNAS',
            'FAKTUR_PEMBAYARAN', 'FAKTUR_PEMBUATAN', 'FAKTUR_TRANSAKSI_PEMBAYARAN',
            'FLEET', 'FLEET_BRAND', 'FLEET_COMPANY', 'FLEET_TYPE',
            'ITEM', 'ITEM_CATEGORY', 'ITEM_UNIT', 'LOCATION',
            'MAINTENANCE', 'MAINTENANCE_COMPANY', 'MAINTENANCE_FLEET', 'MATERIAL',
            'NOT_RETURN_DO', 'ORDER', 'ORDER_DETAIL', 'PROFIT_LOSS',
            'PURCHASE', 'PURCHASE_PAYMENT', 'RETURN_DO', 'ROUTE',
            'STOCK', 'STOCK_TRANSACTION', 'SUPPLIER', 'SUPPLIER_PURCHASE',
            'UNIT', 'USER_BANK', 'VENDOR_INV_PAID', 'VENDOR_INV_UNPAID',
            'VENDOR_ORDER_WAITING', 'VENDOR_PAY_LIST', 'WAREHOUSE',
        ],

        // DO — operasional + master + inventory + faktur + purchasing.
        'DO' => [
            'BANK_ACCOUNT', 'COST_COMPONENT', 'CUSTOMER', 'DRIVER_SALARY',
            'EMPLOYEE', 'FAKTUR_BELUM_LUNAS', 'FAKTUR_LUNAS', 'FAKTUR_PEMBAYARAN',
            'FAKTUR_PEMBUATAN', 'FAKTUR_TRANSAKSI_PEMBAYARAN', 'FLEET', 'FLEET_BRAND',
            'FLEET_COMPANY', 'FLEET_TYPE', 'ITEM', 'ITEM_CATEGORY',
            'ITEM_UNIT', 'LOCATION', 'MAINTENANCE', 'MATERIAL',
            'NOT_RETURN_DO', 'ORDER', 'PURCHASE', 'PURCHASE_PAYMENT',
            'RETURN_DO', 'ROUTE', 'STOCK', 'STOCK_TRANSACTION',
            'SUPPLIER', 'USER_BANK', 'WAREHOUSE',
        ],

        // Maintenance.
        'TRL250604212202' => [
            'BANK_ACCOUNT', 'COST_COMPONENT', 'CUSTOMER',
            'DRIVER_SALARY', 'EMPLOYEE', 'FAKTUR_BELUM_LUNAS',
            'FAKTUR_LUNAS', 'FAKTUR_PEMBAYARAN', 'FAKTUR_PEMBUATAN',
            'FAKTUR_TRANSAKSI_PEMBAYARAN', 'FLEET', 'FLEET_BRAND', 'FLEET_COMPANY',
            'FLEET_TYPE', 'ITEM', 'ITEM_CATEGORY',
            'ITEM_UNIT', 'LOCATION', 'MAINTENANCE', 'MAINTENANCE_COMPANY',
            'MAINTENANCE_FLEET', 'NOT_RETURN_DO', 'ORDER', 'PROFIT_LOSS',
            'PURCHASE', 'PURCHASE_PAYMENT', 'RETURN_DO', 'ROUTE',
            'STOCK', 'STOCK_TRANSACTION', 'SUPPLIER', 'SUPPLIER_PURCHASE',
            'USER_BANK', 'WAREHOUSE',
        ],

        // Invoice.
        'TRL250604212231' => [
            'BANK_ACCOUNT', 'COST_COMPONENT', 'CUSTOMER', 'EMPLOYEE',
            'FAKTUR_BELUM_LUNAS', 'FAKTUR_LUNAS', 'FAKTUR_PEMBAYARAN',
            'FAKTUR_PEMBUATAN', 'FAKTUR_TRANSAKSI_PEMBAYARAN', 'FLEET',
            'FLEET_BRAND', 'FLEET_COMPANY', 'FLEET_TYPE', 'ITEM',
            'ITEM_CATEGORY', 'ITEM_UNIT', 'LOCATION', 'MAINTENANCE',
            'NOT_RETURN_DO', 'ORDER', 'PURCHASE', 'PURCHASE_PAYMENT',
            'RETURN_DO', 'ROUTE', 'STOCK', 'STOCK_TRANSACTION',
            'SUPPLIER', 'USER_BANK', 'WAREHOUSE',
        ],

        // Uang Jalan.
        'TRL250604212242' => [
            'BANK_ACCOUNT', 'COST_COMPONENT', 'CUSTOMER', 'EMPLOYEE',
            'FAKTUR_BELUM_LUNAS', 'FAKTUR_LUNAS', 'FAKTUR_PEMBAYARAN',
            'FAKTUR_PEMBUATAN', 'FAKTUR_TRANSAKSI_PEMBAYARAN', 'FLEET',
            'FLEET_BRAND', 'FLEET_COMPANY', 'FLEET_TYPE', 'ITEM',
            'ITEM_CATEGORY', 'ITEM_UNIT', 'LOCATION', 'MATERIAL',
            'NOT_RETURN_DO', 'ORDER', 'ORDER_DETAIL', 'PURCHASE',
            'PURCHASE_PAYMENT', 'RETURN_DO', 'ROUTE', 'STOCK',
            'STOCK_TRANSACTION', 'SUPPLIER', 'SUPPLIER_PURCHASE', 'USER_BANK',
            'WAREHOUSE',
        ],
    ];

    /**
     * Pemetaan kode menu lama → kode menu baru (fallback untuk role yang
     * tidak terdaftar di `$access`, mis. role baru).
     *
     * Nilai berupa array karena satu kode lama bisa memetakan ke beberapa
     * kode baru (mis. `BANK` lama ambigu: parent + child).
     *
     * @var array<string, array<int, string>>
     */
    private array $legacyMap = [
        // parent digabung
        'DATA' => ['MASTER'],
        'WAREHOUSE_PARENT' => ['INVENTORY'],
        // kode 'BANK' lama dipakai bersama oleh parent Bank & child Bank Account
        'BANK' => ['BANK', 'BANK_ACCOUNT'],
        // rename kode auto / campur
        'MN260521090715' => ['STOCK_SYNC'],
        'MN251127221320' => ['MASTER_MENU'],
        'MN251127221422' => ['COST_COMPONENT_LOG'],
        'ORDER-DETAIL' => ['ORDER_DETAIL'],
        'MN_RPT_MAINT_FLEET' => ['MAINTENANCE_FLEET'],
        'MN_RPT_MAINT_COMPINT' => ['MAINTENANCE_COMPANY'],
        'MN_RPT_SUPPLIER_PURCHASE' => ['SUPPLIER_PURCHASE'],
    ];

    public function run(): void
    {
        $now = Carbon::now();

        // ── 1. Kumpulkan menu aktif + peta parent ─────────────────────────
        $menus = DB::table('menu')->whereNull('deleted_at')->get();
        $validCodes = $menus->pluck('code')->all();
        $validSet = array_flip($validCodes);

        $parentOf = [];
        foreach ($menus as $menu) {
            if ($menu->parentCode !== '0' && $menu->parentCode !== null) {
                $parentOf[$menu->code] = $menu->parentCode;
            }
        }

        // ── 2. Role yang benar-benar ada ──────────────────────────────────
        $realRoles = DB::table('role')->whereNull('deleted_at')->pluck('code')->all();

        // ── 3. Tentukan menu per role ─────────────────────────────────────
        $desired = [];
        foreach ($realRoles as $roleCode) {
            $desired[$roleCode] = [];

            // Super Admin: SEMUA menu.
            if ($roleCode === 'SPRADMIN') {
                foreach ($validCodes as $code) {
                    $desired[$roleCode][$code] = true;
                }

                continue;
            }

            // Role terdaftar: pakai daftar akses eksplisit.
            if (isset($this->access[$roleCode])) {
                foreach ($this->access[$roleCode] as $code) {
                    if (isset($validSet[$code])) {
                        $desired[$roleCode][$code] = true;
                    }
                }

                continue;
            }

            // Fallback (role baru): turunkan dari role_menu existing + legacyMap.
            foreach (DB::table('role_menu')->where('roleCode', $roleCode)->get() as $row) {
                foreach ($this->legacyMap[$row->menuCode] ?? [$row->menuCode] as $code) {
                    if (isset($validSet[$code])) {
                        $desired[$roleCode][$code] = true;
                    }
                }
            }
        }

        // ── 3b. Menu turunan: role yang punya akses sumber otomatis ikut ──
        //      mendapat menu baru (mis. Supplier Lunas mengikuti Pembayaran).
        $followers = [
            'PURCHASE_PAID' => 'PURCHASE_PAYMENT',
        ];

        foreach ($desired as $roleCode => $codes) {
            foreach ($followers as $newCode => $sourceCode) {
                if (isset($codes[$sourceCode]) && isset($validSet[$newCode])) {
                    $desired[$roleCode][$newCode] = true;
                }
            }
        }

        // ── 4. Pastikan parent dari setiap child ikut ter-assign ──────────
        foreach ($desired as $roleCode => $codes) {
            foreach (array_keys($codes) as $code) {
                if (isset($parentOf[$code])) {
                    $desired[$roleCode][$parentOf[$code]] = true;
                }
            }
        }

        // ── 5. Tulis ulang role_menu (bersih & idempotent) ────────────────
        DB::table('role_menu')->delete();

        $inserted = 0;
        foreach ($desired as $roleCode => $codes) {
            foreach (array_keys($codes) as $menuCode) {
                DB::table('role_menu')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => GenerateCode::generateUniqueCode('TRL', 'role_menu'),
                    'roleCode' => $roleCode,
                    'menuCode' => $menuCode,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);
                $inserted++;
            }
        }

        $this->command?->info('RoleMenuSeeder: '.count($realRoles).' role, '.$inserted.' baris akses menu.');
        foreach ($desired as $roleCode => $codes) {
            $this->command?->line(sprintf('  - %-20s %d menu', $roleCode, count($codes)));
        }
    }
}
