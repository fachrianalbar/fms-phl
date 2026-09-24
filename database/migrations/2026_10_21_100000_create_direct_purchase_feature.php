<?php

use App\Helpers\GenerateCode;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        // 1. Tambah kolom is_direct dan fleetCode ke tabel purchase
        Schema::table('purchase', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase', 'is_direct')) {
                $table->boolean('is_direct')->default(false)->after('warehouseCode')->index();
            }
            if (! Schema::hasColumn('purchase', 'fleetCode')) {
                $table->string('fleetCode', 30)->nullable()->after('is_direct')->index();
            }
        });

        // 2. Geser sort menu lama agar Pembelian Langsung berada di posisi ke-3
        DB::table('menu')->where('code', 'PURCHASE_PAYMENT')->update(['sort' => 4, 'updated_at' => $now]);
        DB::table('menu')->where('code', 'PURCHASE_PAID')->update(['sort' => 5, 'updated_at' => $now]);
        DB::table('menu')->where('code', 'SUPPLIER_PURCHASE')->update(['sort' => 6, 'updated_at' => $now]);

        // 3. Daftarkan menu Pembelian Langsung (DIRECT_PURCHASE)
        $existingMenu = DB::table('menu')->where('code', 'DIRECT_PURCHASE')->first();
        if ($existingMenu) {
            DB::table('menu')->where('code', 'DIRECT_PURCHASE')->update([
                'name' => 'Direct Purchase',
                'nama' => 'Pembelian Langsung',
                'parentCode' => 'SUPPLIER_GROUP',
                'url' => 'purchasing/direct-purchase',
                'icon' => null,
                'sort' => 3,
                'deleted_at' => null,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => 'DIRECT_PURCHASE',
                'name' => 'Direct Purchase',
                'nama' => 'Pembelian Langsung',
                'parentCode' => 'SUPPLIER_GROUP',
                'url' => 'purchasing/direct-purchase',
                'icon' => null,
                'sort' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 4. Salin akses role_menu dari menu PURCHASE ke DIRECT_PURCHASE
        $roleCodes = DB::table('role_menu')
            ->where('menuCode', 'PURCHASE')
            ->pluck('roleCode')
            ->unique()
            ->values()
            ->all();

        if (! in_array('SPRADMIN', $roleCodes, true)) {
            $roleCodes[] = 'SPRADMIN';
        }

        foreach ($roleCodes as $roleCode) {
            $hasAccess = DB::table('role_menu')
                ->where('roleCode', $roleCode)
                ->where('menuCode', 'DIRECT_PURCHASE')
                ->exists();

            if (! $hasAccess) {
                DB::table('role_menu')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => GenerateCode::generateUniqueCode('TRL', 'role_menu'),
                    'roleCode' => $roleCode,
                    'menuCode' => 'DIRECT_PURCHASE',
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

        // 1. Hapus role_menu dan menu DIRECT_PURCHASE
        DB::table('role_menu')->where('menuCode', 'DIRECT_PURCHASE')->delete();
        DB::table('menu')->where('code', 'DIRECT_PURCHASE')->delete();

        // 2. Kembalikan sort menu lama
        DB::table('menu')->where('code', 'PURCHASE_PAYMENT')->update(['sort' => 3, 'updated_at' => $now]);
        DB::table('menu')->where('code', 'PURCHASE_PAID')->update(['sort' => 4, 'updated_at' => $now]);
        DB::table('menu')->where('code', 'SUPPLIER_PURCHASE')->update(['sort' => 5, 'updated_at' => $now]);

        // 3. Hapus kolom is_direct dan fleetCode dari tabel purchase
        Schema::table('purchase', function (Blueprint $table) {
            if (Schema::hasColumn('purchase', 'fleetCode')) {
                $table->dropColumn('fleetCode');
            }
            if (Schema::hasColumn('purchase', 'is_direct')) {
                $table->dropColumn('is_direct');
            }
        });
    }
};
