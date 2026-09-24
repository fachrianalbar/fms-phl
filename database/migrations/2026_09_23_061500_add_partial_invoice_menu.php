<?php

use App\Helpers\GenerateCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const MENU_CODE = 'FAKTUR_PEMBAYARAN_SEBAGIAN';

    public function up(): void
    {
        $now = Carbon::now();
        $menuExists = DB::table('menu')->where('code', self::MENU_CODE)->exists();

        if (! $menuExists) {
            DB::table('menu')
                ->where('parentCode', 'FAKTUR')
                ->where('sort', '>=', 3)
                ->increment('sort');

            DB::table('menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => self::MENU_CODE,
                'name' => 'Partially Paid Invoice',
                'nama' => 'Faktur Pembayaran Sebagian',
                'parentCode' => 'FAKTUR',
                'url' => 'invoice/partial',
                'icon' => null,
                'sort' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleCodes = DB::table('role_menu')
            ->where('menuCode', 'FAKTUR_BELUM_LUNAS')
            ->pluck('roleCode')
            ->push('SPRADMIN')
            ->unique()
            ->values();

        foreach ($roleCodes as $roleCode) {
            if (DB::table('role_menu')->where('roleCode', $roleCode)->where('menuCode', self::MENU_CODE)->exists()) {
                continue;
            }

            DB::table('role_menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => GenerateCode::generateCode('TRL', true),
                'roleCode' => $roleCode,
                'menuCode' => self::MENU_CODE,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $menuExists = DB::table('menu')->where('code', self::MENU_CODE)->exists();

        DB::table('role_menu')->where('menuCode', self::MENU_CODE)->delete();
        DB::table('menu')->where('code', self::MENU_CODE)->delete();

        if ($menuExists) {
            DB::table('menu')
                ->where('parentCode', 'FAKTUR')
                ->where('sort', '>', 3)
                ->decrement('sort');
        }
    }
};
