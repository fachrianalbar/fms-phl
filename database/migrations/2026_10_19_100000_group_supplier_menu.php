<?php

use App\Helpers\GenerateCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Satu grup menu Supplier.
     *
     * SUPPLIER_GROUP
     * ├── Data Supplier        inventory/supplier
     * ├── Pembelian            purchasing/purchase
     * ├── Hutang Belum Lunas   purchasing/purchase-payment
     * ├── Hutang Lunas         purchasing/purchase-paid
     * └── Laporan Supplier     report/supplier
     *
     * URL tidak diubah. Nama Inggris Purchase / Purchase Payment tetap,
     * karena dipakai MenuService::getByName().
     */
    private array $children = [
        'SUPPLIER' => [
            'nama' => 'Data Supplier',
            'sort' => 1,
        ],
        'PURCHASE' => [
            'nama' => 'Pembelian',
            'sort' => 2,
        ],
        'PURCHASE_PAYMENT' => [
            'nama' => 'Hutang Belum Lunas',
            'sort' => 3,
        ],
        'PURCHASE_PAID' => [
            'nama' => 'Hutang Lunas',
            'sort' => 4,
        ],
        'SUPPLIER_PURCHASE' => [
            'nama' => 'Laporan Supplier',
            'sort' => 5,
        ],
    ];

    public function up(): void
    {
        $now = Carbon::now();

        $parent = DB::table('menu')->where('code', 'SUPPLIER_GROUP')->first();

        if ($parent) {
            DB::table('menu')->where('code', 'SUPPLIER_GROUP')->update([
                'name' => 'Supplier',
                'nama' => 'Supplier',
                'parentCode' => '0',
                'url' => '#',
                'icon' => 'shopping-bag',
                'sort' => 3,
                'deleted_at' => null,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => 'SUPPLIER_GROUP',
                'name' => 'Supplier',
                'nama' => 'Supplier',
                'parentCode' => '0',
                'url' => '#',
                'icon' => 'shopping-bag',
                'sort' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($this->children as $code => $child) {
            DB::table('menu')->where('code', $code)->update([
                'nama' => $child['nama'],
                'parentCode' => 'SUPPLIER_GROUP',
                'sort' => $child['sort'],
                'updated_at' => $now,
            ]);
        }

        $roleCodes = DB::table('role_menu')
            ->whereIn('menuCode', array_keys($this->children))
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
                ->where('menuCode', 'SUPPLIER_GROUP')
                ->exists();

            if ($hasAccess) {
                continue;
            }

            DB::table('role_menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => GenerateCode::generateUniqueCode('TRL', 'role_menu'),
                'roleCode' => $roleCode,
                'menuCode' => 'SUPPLIER_GROUP',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $remaining = DB::table('menu')
            ->where('parentCode', 'PURCHASING')
            ->whereNull('deleted_at')
            ->count();

        if ($remaining === 0) {
            DB::table('role_menu')->where('menuCode', 'PURCHASING')->delete();
            DB::table('menu')->where('code', 'PURCHASING')->delete();
        }
    }

    public function down(): void
    {
        $now = Carbon::now();

        $purchasing = DB::table('menu')->where('code', 'PURCHASING')->first();

        if (! $purchasing) {
            DB::table('menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => 'PURCHASING',
                'name' => 'Purchasing',
                'nama' => 'Pembelian',
                'parentCode' => '0',
                'url' => '#',
                'icon' => 'shopping-bag',
                'sort' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $restore = [
            'SUPPLIER' => ['parentCode' => 'MASTER', 'nama' => 'Pemasok', 'sort' => 2],
            'PURCHASE' => ['parentCode' => 'PURCHASING', 'nama' => 'Pembelian', 'sort' => 1],
            'PURCHASE_PAYMENT' => ['parentCode' => 'PURCHASING', 'nama' => 'Pembayaran Pembelian', 'sort' => 2],
            'PURCHASE_PAID' => ['parentCode' => 'PURCHASING', 'nama' => 'Supplier Lunas', 'sort' => 3],
            'SUPPLIER_PURCHASE' => ['parentCode' => 'REPORT', 'nama' => 'Pembelian Per Supplier', 'sort' => 9],
        ];

        foreach ($restore as $code => $child) {
            DB::table('menu')->where('code', $code)->update([
                'nama' => $child['nama'],
                'parentCode' => $child['parentCode'],
                'sort' => $child['sort'],
                'updated_at' => $now,
            ]);
        }

        $roleCodes = DB::table('role_menu')
            ->whereIn('menuCode', ['PURCHASE', 'PURCHASE_PAYMENT', 'PURCHASE_PAID'])
            ->pluck('roleCode')
            ->unique();

        foreach ($roleCodes as $roleCode) {
            $hasAccess = DB::table('role_menu')
                ->where('roleCode', $roleCode)
                ->where('menuCode', 'PURCHASING')
                ->exists();

            if ($hasAccess) {
                continue;
            }

            DB::table('role_menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => GenerateCode::generateUniqueCode('TRL', 'role_menu'),
                'roleCode' => $roleCode,
                'menuCode' => 'PURCHASING',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('role_menu')->where('menuCode', 'SUPPLIER_GROUP')->delete();
        DB::table('menu')->where('code', 'SUPPLIER_GROUP')->delete();
    }
};
