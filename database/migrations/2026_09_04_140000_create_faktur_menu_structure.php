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
            'code' => 'FAKTUR_PEMBUATAN',
            'name' => 'Create Invoice',
            'nama' => 'Pembuatan Faktur',
            'url' => 'invoice/create',
            'sort' => 1,
        ],
        [
            'code' => 'FAKTUR_BELUM_LUNAS',
            'name' => 'Unpaid Invoice',
            'nama' => 'Faktur Belum Lunas',
            'url' => 'invoice/unpaid',
            'sort' => 2,
        ],
        [
            'code' => 'FAKTUR_LUNAS',
            'name' => 'Paid Invoice',
            'nama' => 'Faktur Lunas',
            'url' => 'invoice/paid',
            'sort' => 3,
        ],
        [
            'code' => 'FAKTUR_PEMBAYARAN',
            'name' => 'Invoice Payment',
            'nama' => 'Pembayaran Faktur',
            'url' => 'invoice/payment',
            'sort' => 4,
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        // 1. Insert / ensure Parent Menu 'FAKTUR'
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

        // 2. Insert Sub Menus
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

        // 3. Find roles with previous INVOICE or INVOICE_PAYMENT access
        $roleCodes = DB::table('role_menu')
            ->whereIn('menuCode', ['INVOICE', 'INVOICE_PAYMENT', 'FINANCE'])
            ->pluck('roleCode')
            ->unique()
            ->values()
            ->all();

        if (! in_array('SPRADMIN', $roleCodes)) {
            $roleCodes[] = 'SPRADMIN';
        }

        $allNewMenuCodes = array_merge(['FAKTUR'], collect($this->subMenus)->pluck('code')->all());

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

        // 4. Remove old INVOICE and INVOICE_PAYMENT menus from under FINANCE
        DB::table('role_menu')->whereIn('menuCode', ['INVOICE', 'INVOICE_PAYMENT'])->delete();
        DB::table('menu')->whereIn('code', ['INVOICE', 'INVOICE_PAYMENT'])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = Carbon::now();

        // 1. Re-add old INVOICE and INVOICE_PAYMENT to FINANCE
        DB::table('menu')->insert([
            [
                'id' => (string) Str::uuid(),
                'code' => 'INVOICE',
                'name' => 'Invoice',
                'nama' => 'Faktur',
                'parentCode' => 'FINANCE',
                'url' => 'finance/invoice',
                'icon' => null,
                'sort' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'code' => 'INVOICE_PAYMENT',
                'name' => 'Invoice Payment',
                'nama' => 'Pembayaran Faktur',
                'parentCode' => 'FINANCE',
                'url' => 'finance/invoice-payment',
                'icon' => null,
                'sort' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // 2. Restore role_menu for INVOICE & INVOICE_PAYMENT from FAKTUR roles
        $fakturRoles = DB::table('role_menu')->where('menuCode', 'FAKTUR')->pluck('roleCode')->all();
        foreach ($fakturRoles as $roleCode) {
            foreach (['INVOICE', 'INVOICE_PAYMENT'] as $oldCode) {
                DB::table('role_menu')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => GenerateCode::generateCode('TRL', true),
                    'roleCode' => $roleCode,
                    'menuCode' => $oldCode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 3. Remove new FAKTUR menus
        $allNewMenuCodes = array_merge(['FAKTUR'], collect($this->subMenus)->pluck('code')->all());
        DB::table('role_menu')->whereIn('menuCode', $allNewMenuCodes)->delete();
        DB::table('menu')->whereIn('code', $allNewMenuCodes)->delete();
    }
};
