<?php

use App\Helpers\GenerateCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $subMenus = [
        [
            'code' => 'VENDOR_INV_UNPAID',
            'name' => 'Vendor Unpaid Invoice',
            'nama' => 'Invoice Belum Lunas',
            'url' => 'vendor/invoice/unpaid',
            'sort' => 1,
        ],
        [
            'code' => 'VENDOR_INV_PAID',
            'name' => 'Vendor Paid Invoice',
            'nama' => 'Invoice Lunas',
            'url' => 'vendor/invoice/paid',
            'sort' => 2,
        ],
        [
            'code' => 'VENDOR_PAY_LIST',
            'name' => 'Vendor Payment List',
            'nama' => 'Daftar Pembayaran',
            'url' => 'vendor/payment',
            'sort' => 3,
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        // 0. Kolom batch_code pada vendor_payment_history
        //    Menyimpan kode batch pembayaran per riwayat, agar pembatalan
        //    pembayaran (yang bisa terdiri dari beberapa batch DP/cicilan)
        //    menghapus mutasi bank yang tepat.
        if (! Schema::hasColumn('vendor_payment_history', 'batch_code')) {
            Schema::table('vendor_payment_history', function (Blueprint $table) {
                $table->string('batch_code')->nullable()->after('vendor_payment_id');
            });
        }

        // 1. Insert / ensure Parent Menu 'VENDOR'
        $parentExists = DB::table('menu')->where('code', 'VENDOR')->exists();
        if (! $parentExists) {
            DB::table('menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => 'VENDOR',
                'name' => 'Vendor',
                'nama' => 'Vendor',
                'parentCode' => '0',
                'url' => '#',
                'icon' => 'truck',
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
                    'parentCode' => 'VENDOR',
                    'url' => $subMenu['url'],
                    'icon' => null,
                    'sort' => $subMenu['sort'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 3. Copy permission dari menu lama VENDOR-PAYMENT (jika ada)
        $roleCodes = DB::table('role_menu')
            ->where('menuCode', 'VENDOR-PAYMENT')
            ->pluck('roleCode')
            ->unique()
            ->values()
            ->all();

        if (! in_array('SPRADMIN', $roleCodes)) {
            $roleCodes[] = 'SPRADMIN';
        }

        $allNewMenuCodes = array_merge(['VENDOR'], collect($this->subMenus)->pluck('code')->all());

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

        // 4. Hapus menu lama VENDOR-PAYMENT dari bawah FINANCE
        //    (URL lama tetap hidup via redirect di routes)
        DB::table('role_menu')->where('menuCode', 'VENDOR-PAYMENT')->delete();
        DB::table('menu')->where('code', 'VENDOR-PAYMENT')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = Carbon::now();

        // 1. Kembalikan menu lama VENDOR-PAYMENT di bawah FINANCE
        DB::table('menu')->insert([
            'id' => (string) Str::uuid(),
            'code' => 'VENDOR-PAYMENT',
            'name' => 'Vendor Payment',
            'nama' => 'Pembayaran Vendor',
            'parentCode' => 'FINANCE',
            'url' => 'finance/vendor-payment',
            'icon' => null,
            'sort' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 2. Restore permission dari role yang punya akses VENDOR
        $vendorRoles = DB::table('role_menu')->where('menuCode', 'VENDOR')->pluck('roleCode')->all();
        foreach ($vendorRoles as $roleCode) {
            DB::table('role_menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => GenerateCode::generateCode('TRL', true),
                'roleCode' => $roleCode,
                'menuCode' => 'VENDOR-PAYMENT',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 3. Hapus menu VENDOR baru
        $allNewMenuCodes = array_merge(['VENDOR'], collect($this->subMenus)->pluck('code')->all());
        DB::table('role_menu')->whereIn('menuCode', $allNewMenuCodes)->delete();
        DB::table('menu')->whereIn('code', $allNewMenuCodes)->delete();

        // 4. Drop kolom batch_code
        if (Schema::hasColumn('vendor_payment_history', 'batch_code')) {
            Schema::table('vendor_payment_history', function (Blueprint $table) {
                $table->dropColumn('batch_code');
            });
        }
    }
};
