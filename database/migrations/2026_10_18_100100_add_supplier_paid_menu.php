<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Tambahkan menu "Supplier Lunas" (Hutang Supplier Lunas) di bawah
     * parent PURCHASING. Idempotent — aman bila MenuSeeder juga dijalankan.
     */
    public function up(): void
    {
        $now = now();

        $payload = [
            'name' => 'Supplier Paid',
            'nama' => 'Supplier Lunas',
            'parentCode' => 'PURCHASING',
            'url' => 'purchasing/purchase-paid',
            'icon' => null,
            'sort' => 3,
            'deleted_at' => null,
            'updated_at' => $now,
        ];

        $exists = DB::table('menu')->where('code', 'PURCHASE_PAID')->exists();

        if ($exists) {
            DB::table('menu')->where('code', 'PURCHASE_PAID')->update($payload);
        } else {
            DB::table('menu')->insert(array_merge($payload, [
                'id' => (string) Str::uuid(),
                'code' => 'PURCHASE_PAID',
                'created_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        DB::table('menu')->where('code', 'PURCHASE_PAID')->delete();
    }
};
