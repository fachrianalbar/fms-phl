<?php

use App\Helpers\GenerateCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const MENU_CODE = 'OMSET_REPORT';

    public function up(): void
    {
        $now = Carbon::now();

        if (! DB::table('menu')->where('code', self::MENU_CODE)->exists()) {
            DB::table('menu')
                ->where('parentCode', 'REPORT')
                ->where('sort', '>=', 5)
                ->increment('sort');

            DB::table('menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => self::MENU_CODE,
                'name' => 'Turnover Report',
                'nama' => 'Laporan Omset',
                'parentCode' => 'REPORT',
                'url' => 'report/omset',
                'icon' => null,
                'sort' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleCodes = DB::table('role_menu')
            ->where('menuCode', 'DRIVER_SALARY')
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
                ->where('parentCode', 'REPORT')
                ->where('sort', '>', 5)
                ->decrement('sort');
        }
    }
};
