<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('vendor_payment')) {
            return;
        }

        $uniqueIndexes = DB::select("SHOW INDEX FROM `vendor_payment` WHERE `Column_name` = 'code' AND `Non_unique` = 0");

        foreach ($uniqueIndexes as $index) {
            DB::statement("ALTER TABLE `vendor_payment` DROP INDEX `{$index->Key_name}`");
        }

        $normalIndex = DB::select("SHOW INDEX FROM `vendor_payment` WHERE `Key_name` = 'vendor_payment_code_index'");
        if (empty($normalIndex)) {
            DB::statement('CREATE INDEX `vendor_payment_code_index` ON `vendor_payment` (`code`)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('vendor_payment')) {
            return;
        }

        $normalIndex = DB::select("SHOW INDEX FROM `vendor_payment` WHERE `Key_name` = 'vendor_payment_code_index'");
        if (! empty($normalIndex)) {
            DB::statement('DROP INDEX `vendor_payment_code_index` ON `vendor_payment`');
        }

        $uniqueIndexes = DB::select("SHOW INDEX FROM `vendor_payment` WHERE `Column_name` = 'code' AND `Non_unique` = 0");
        if (empty($uniqueIndexes)) {
            DB::statement('ALTER TABLE `vendor_payment` ADD UNIQUE `UNIQUE` (`code`)');
        }
    }
};
