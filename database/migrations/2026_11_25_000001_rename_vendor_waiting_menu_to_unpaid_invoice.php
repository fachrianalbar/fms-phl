<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MENU_CODE = 'VENDOR_ORDER_WAITING';

    public function up(): void
    {
        DB::table('menu')
            ->where('code', self::MENU_CODE)
            ->update([
                'name' => 'Unpaid Invoice',
                'nama' => 'Faktur yg Belum Dibayar',
                'updated_at' => Carbon::now(),
            ]);
    }

    public function down(): void
    {
        DB::table('menu')
            ->where('code', self::MENU_CODE)
            ->update([
                'name' => 'Order Awaiting Invoice',
                'nama' => 'Order Menunggu Faktur',
                'updated_at' => Carbon::now(),
            ]);
    }
};
