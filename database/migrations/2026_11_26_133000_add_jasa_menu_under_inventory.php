<?php

use App\Helpers\GenerateCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = Carbon::now();

        $hasSortTwoOther = DB::table('menu')
            ->where('parentCode', 'INVENTORY')
            ->where('code', '!=', 'SERVICE_ITEM')
            ->where('sort', 2)
            ->exists();

        if ($hasSortTwoOther) {
            DB::table('menu')
                ->where('parentCode', 'INVENTORY')
                ->where('code', '!=', 'SERVICE_ITEM')
                ->where('sort', '>=', 2)
                ->increment('sort', 1, ['updated_at' => $now]);
        }

        $serviceMenuPayload = [
            'name' => 'Service',
            'nama' => 'Jasa',
            'parentCode' => 'INVENTORY',
            'url' => 'inventory/jasa',
            'icon' => null,
            'sort' => 2,
            'deleted_at' => null,
            'updated_at' => $now,
        ];

        $exists = DB::table('menu')->where('code', 'SERVICE_ITEM')->exists();

        if ($exists) {
            DB::table('menu')->where('code', 'SERVICE_ITEM')->update($serviceMenuPayload);
        } else {
            DB::table('menu')->insert(array_merge($serviceMenuPayload, [
                'id' => (string) Str::uuid(),
                'code' => 'SERVICE_ITEM',
                'created_at' => $now,
            ]));
        }

        $roleCodes = DB::table('role_menu')
            ->where('menuCode', 'ITEM')
            ->whereNull('deleted_at')
            ->pluck('roleCode')
            ->all();

        $hasSprAdmin = DB::table('role')
            ->where('code', 'SPRADMIN')
            ->whereNull('deleted_at')
            ->exists();

        if ($hasSprAdmin) {
            $roleCodes[] = 'SPRADMIN';
        }

        $roleCodes = array_values(array_unique(array_filter($roleCodes)));

        foreach ($roleCodes as $roleCode) {
            foreach (['INVENTORY', 'SERVICE_ITEM'] as $menuCode) {
                $existingRoleMenu = DB::table('role_menu')
                    ->where('roleCode', $roleCode)
                    ->where('menuCode', $menuCode)
                    ->first();

                if ($existingRoleMenu) {
                    if ($existingRoleMenu->deleted_at !== null) {
                        DB::table('role_menu')
                            ->where('id', $existingRoleMenu->id)
                            ->update([
                                'deleted_at' => null,
                                'updated_at' => $now,
                            ]);
                    }

                    continue;
                }

                DB::table('role_menu')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => GenerateCode::generateUniqueCode('TRL', 'role_menu'),
                    'roleCode' => $roleCode,
                    'menuCode' => $menuCode,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);
            }
        }
    }

    public function down(): void
    {
        $now = Carbon::now();

        DB::table('role_menu')->where('menuCode', 'SERVICE_ITEM')->delete();
        DB::table('menu')->where('code', 'SERVICE_ITEM')->delete();

        $hasSortTwo = DB::table('menu')
            ->where('parentCode', 'INVENTORY')
            ->where('sort', 2)
            ->exists();

        if (! $hasSortTwo) {
            DB::table('menu')
                ->where('parentCode', 'INVENTORY')
                ->where('sort', '>=', 3)
                ->decrement('sort', 1, ['updated_at' => $now]);
        }
    }
};
