<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = Carbon::now();

        DB::table('menu')->where('code', 'VENDOR_INV_PARTIAL')->update([
            'sort' => 99,
            'updated_at' => $now,
        ]);

        DB::table('menu')->where('code', 'VENDOR_INV_UNPAID')->update([
            'sort' => 2,
            'updated_at' => $now,
        ]);

        DB::table('menu')->where('code', 'VENDOR_INV_PARTIAL')->update([
            'sort' => 3,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $now = Carbon::now();

        DB::table('menu')->where('code', 'VENDOR_INV_PARTIAL')->update([
            'sort' => 99,
            'updated_at' => $now,
        ]);

        DB::table('menu')->where('code', 'VENDOR_INV_UNPAID')->update([
            'sort' => 3,
            'updated_at' => $now,
        ]);

        DB::table('menu')->where('code', 'VENDOR_INV_PARTIAL')->update([
            'sort' => 2,
            'updated_at' => $now,
        ]);
    }
};
