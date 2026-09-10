<?php

use App\Helpers\GenerateCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Struktur menu baru Pembayaran Langsung:
     *
     * PEMBAYARAN LANGSUNG (parent, url '#')
     * ├── Order Belum Lunas  → direct-payment/order/unpaid
     * ├── Order Lunas        → direct-payment/order/paid
     * └── Daftar Pembayaran  → direct-payment/payment
     *
     * Menu DIRECT_PAYMENT yang sudah ada diubah menjadi parent (url '#'),
     * lalu tiga submenu dibuat dengan akses role yang sama.
     */
    private array $subMenus = [
        [
            'code' => 'DIRECT_PAYMENT_UNPAID',
            'name' => 'Direct Payment Unpaid Order',
            'nama' => 'Order Belum Lunas',
            'url' => 'direct-payment/order/unpaid',
            'sort' => 1,
        ],
        [
            'code' => 'DIRECT_PAYMENT_PAID',
            'name' => 'Direct Payment Paid Order',
            'nama' => 'Order Lunas',
            'url' => 'direct-payment/order/paid',
            'sort' => 2,
        ],
        [
            'code' => 'DIRECT_PAYMENT_LIST',
            'name' => 'Direct Payment Payment List',
            'nama' => 'Daftar Pembayaran',
            'url' => 'direct-payment/payment',
            'sort' => 3,
        ],
    ];

    public function up(): void
    {
        $now = Carbon::now();

        // 1. Ubah menu DIRECT_PAYMENT menjadi parent menu (url '#')
        DB::table('menu')
            ->where('code', 'DIRECT_PAYMENT')
            ->update([
                'url' => '#',
                'updated_at' => $now,
            ]);

        // 2. Insert tiga submenu di bawah DIRECT_PAYMENT
        foreach ($this->subMenus as $subMenu) {
            $exists = DB::table('menu')->where('code', $subMenu['code'])->exists();
            if (! $exists) {
                DB::table('menu')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'code' => $subMenu['code'],
                    'name' => $subMenu['name'],
                    'nama' => $subMenu['nama'],
                    'parentCode' => 'DIRECT_PAYMENT',
                    'url' => $subMenu['url'],
                    'icon' => null,
                    'sort' => $subMenu['sort'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 3. Copy akses role dari menu DIRECT_PAYMENT ke ketiga submenu
        //    (SPRADMIN selalu diikutsertakan).
        $roleCodes = DB::table('role_menu')
            ->where('menuCode', 'DIRECT_PAYMENT')
            ->pluck('roleCode')
            ->unique()
            ->values()
            ->all();

        if (! in_array('SPRADMIN', $roleCodes)) {
            $roleCodes[] = 'SPRADMIN';
        }

        $subMenuCodes = collect($this->subMenus)->pluck('code')->all();

        foreach ($roleCodes as $roleCode) {
            foreach ($subMenuCodes as $menuCode) {
                $hasAccess = DB::table('role_menu')
                    ->where('roleCode', $roleCode)
                    ->where('menuCode', $menuCode)
                    ->exists();

                if (! $hasAccess) {
                    DB::table('role_menu')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'code' => GenerateCode::generateCode('TRL', true),
                        'roleCode' => $roleCode,
                        'menuCode' => $menuCode,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $now = Carbon::now();

        // 1. Hapus submenu beserta akses role-nya
        $subMenuCodes = collect($this->subMenus)->pluck('code')->all();
        DB::table('role_menu')->whereIn('menuCode', $subMenuCodes)->delete();
        DB::table('menu')->whereIn('code', $subMenuCodes)->delete();

        // 2. Kembalikan menu parent ke halaman tunggal
        DB::table('menu')
            ->where('code', 'DIRECT_PAYMENT')
            ->update([
                'url' => 'direct-payment',
                'updated_at' => $now,
            ]);
    }
};
