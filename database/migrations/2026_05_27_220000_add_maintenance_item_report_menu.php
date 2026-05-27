<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $maintenanceMenus = [
        [
            'code' => 'MN_RPT_MAINT_ITEM',
            'name' => 'Report Maintenance Item',
            'nama' => 'Laporan Detail Maintenance Item',
            'url' => 'report/maintenance-item',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $reportParentCode = $this->resolveReportParentCode();

        if (! $reportParentCode) {
            return;
        }

        $now = Carbon::now();
        $nextSort = ((int) DB::table('menu')->where('parentCode', $reportParentCode)->max('sort')) + 1;

        foreach ($this->maintenanceMenus as $menu) {
            $exists = DB::table('menu')->where('code', $menu['code'])->exists();

            if ($exists) {
                continue;
            }

            DB::table('menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => $menu['code'],
                'name' => $menu['name'],
                'nama' => $menu['nama'],
                'parentCode' => $reportParentCode,
                'url' => $menu['url'],
                'icon' => null,
                'sort' => $nextSort,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $nextSort++;
        }

        $menuCodes = collect($this->maintenanceMenus)->pluck('code')->all();

        $roleCodes = DB::table('role_menu')
            ->where(function ($query) use ($reportParentCode) {
                $query->where('menuCode', $reportParentCode)
                    ->orWhereIn('menuCode', function ($subQuery) use ($reportParentCode) {
                        $subQuery->select('code')
                            ->from('menu')
                            ->where('parentCode', $reportParentCode);
                    });
            })
            ->pluck('roleCode')
            ->unique();

        foreach ($roleCodes as $roleCode) {
            foreach ($menuCodes as $menuCode) {
                $exists = DB::table('role_menu')
                    ->where('roleCode', $roleCode)
                    ->where('menuCode', $menuCode)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('role_menu')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => 'RM' . Carbon::now()->format('ymdHis') . Str::upper(Str::random(6)),
                    'roleCode' => $roleCode,
                    'menuCode' => $menuCode,
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
        $menuCodes = collect($this->maintenanceMenus)->pluck('code')->all();

        DB::table('role_menu')->whereIn('menuCode', $menuCodes)->delete();
        DB::table('menu')->whereIn('code', $menuCodes)->delete();
    }

    private function resolveReportParentCode(): ?string
    {
        $reportParentFromChildren = DB::table('menu')
            ->where('url', 'like', 'report/%')
            ->whereNotNull('parentCode')
            ->where('parentCode', '!=', '0')
            ->value('parentCode');

        if ($reportParentFromChildren) {
            return $reportParentFromChildren;
        }

        return DB::table('menu')
            ->where(function ($query) {
                $query->where('url', 'report')
                    ->orWhere('name', 'Report')
                    ->orWhere('nama', 'Laporan');
            })
            ->where(function ($query) {
                $query->where('parentCode', '0')
                    ->orWhereNull('parentCode');
            })
            ->value('code');
    }
};
