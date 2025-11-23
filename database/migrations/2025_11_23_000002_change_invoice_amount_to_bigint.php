<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to avoid dependency on doctrine/dbal
        $prefix = DB::getTablePrefix();

        // Convert to BIGINT to allow large monetary values
        DB::statement("ALTER TABLE `{$prefix}invoice` MODIFY COLUMN `invoiceAmount` BIGINT NULL;");
        DB::statement("ALTER TABLE `{$prefix}invoice` MODIFY COLUMN `ppnAmount` BIGINT NULL;");
    }

    public function down(): void
    {
        $prefix = DB::getTablePrefix();

        // Revert back to INT if needed
        DB::statement("ALTER TABLE `{$prefix}invoice` MODIFY COLUMN `invoiceAmount` INT NULL;");
        DB::statement("ALTER TABLE `{$prefix}invoice` MODIFY COLUMN `ppnAmount` INT NULL;");
    }
};
