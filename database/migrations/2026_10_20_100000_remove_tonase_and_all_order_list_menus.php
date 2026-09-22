<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Menu report yang dihapus karena tidak dipakai.
     *
     *  - ALL_ORDER_LIST  report/all-order-list
     *  - DRIVER_TONASE   report/driver-tonase
     *  - FLEET_TONASE    report/fleet-tonase
     */
    private array $menuCodes = [
        'ALL_ORDER_LIST',
        'DRIVER_TONASE',
        'FLEET_TONASE',
    ];

    /**
     * Snapshot menu untuk restore pada down().
     */
    private array $menuSnapshot = [
        ['code' => 'ALL_ORDER_LIST', 'name' => 'All Order List', 'nama' => 'Semua Pesanan', 'parentCode' => 'REPORT', 'url' => 'report/all-order-list', 'icon' => null, 'sort' => 2],
        ['code' => 'DRIVER_TONASE', 'name' => 'Driver Tonnage', 'nama' => 'Tonase Sopir', 'parentCode' => 'REPORT', 'url' => 'report/driver-tonase', 'icon' => null, 'sort' => 5],
        ['code' => 'FLEET_TONASE', 'name' => 'Fleet Tonnage', 'nama' => 'Tonase Armada', 'parentCode' => 'REPORT', 'url' => 'report/fleet-tonase', 'icon' => null, 'sort' => 6],
    ];

    public function up(): void
    {
        // 1. Hapus akses role ke menu yang dihapus.
        DB::table('role_menu')->whereIn('menuCode', $this->menuCodes)->delete();

        // 2. Hapus baris menu.
        DB::table('menu')->whereIn('code', $this->menuCodes)->delete();
    }

    /**
     * Reverse: mengembalikan baris menu saja.
     * Permission role perlu di-grant ulang manual bila diperlukan.
     */
    public function down(): void
    {
        $now = Carbon::now();

        foreach ($this->menuSnapshot as $menu) {
            $exists = DB::table('menu')->where('code', $menu['code'])->exists();
            if (! $exists) {
                DB::table('menu')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => $menu['code'],
                    'name' => $menu['name'],
                    'nama' => $menu['nama'],
                    'parentCode' => $menu['parentCode'],
                    'url' => $menu['url'],
                    'icon' => $menu['icon'],
                    'sort' => $menu['sort'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
