<?php

use App\Helpers\GenerateCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $subMenus = [
        [
            'code' => 'FAKTUR_TRANSAKSI_PEMBAYARAN',
            'name' => 'Payment Transaction',
            'nama' => 'Transaksi Pembayaran',
            'url' => 'invoice/payment-transaction',
            'sort' => 5,
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        // 1. Pastikan parent menu 'FAKTUR' tersedia
        $parentExists = DB::table('menu')->where('code', 'FAKTUR')->exists();
        if (! $parentExists) {
            DB::table('menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => 'FAKTUR',
                'name' => 'Invoice',
                'nama' => 'Faktur',
                'parentCode' => '0',
                'url' => '#',
                'icon' => 'file-text',
                'sort' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. Insert sub menu baru
        foreach ($this->subMenus as $subMenu) {
            $exists = DB::table('menu')->where('code', $subMenu['code'])->exists();
            if (! $exists) {
                DB::table('menu')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => $subMenu['code'],
                    'name' => $subMenu['name'],
                    'nama' => $subMenu['nama'],
                    'parentCode' => 'FAKTUR',
                    'url' => $subMenu['url'],
                    'icon' => null,
                    'sort' => $subMenu['sort'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 3. Berikan akses ke role yang sudah memiliki akses menu Pembayaran Faktur
        $roleCodes = DB::table('role_menu')
            ->whereIn('menuCode', ['FAKTUR_PEMBAYARAN', 'FAKTUR', 'INVOICE', 'INVOICE_PAYMENT', 'FINANCE'])
            ->pluck('roleCode')
            ->unique()
            ->values()
            ->all();

        if (! in_array('SPRADMIN', $roleCodes)) {
            $roleCodes[] = 'SPRADMIN';
        }

        $allNewMenuCodes = collect($this->subMenus)->pluck('code')->all();

        foreach ($roleCodes as $roleCode) {
            foreach ($allNewMenuCodes as $menuCode) {
                $hasAccess = DB::table('role_menu')
                    ->where('roleCode', $roleCode)
                    ->where('menuCode', $menuCode)
                    ->exists();

                if (! $hasAccess) {
                    DB::table('role_menu')->insert([
                        'id' => (string) Str::uuid(),
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $allNewMenuCodes = collect($this->subMenus)->pluck('code')->all();

        DB::table('role_menu')->whereIn('menuCode', $allNewMenuCodes)->delete();
        DB::table('menu')->whereIn('code', $allNewMenuCodes)->delete();
    }
};
