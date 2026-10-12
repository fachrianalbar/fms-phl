<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('order', 'plateNumber')) {
            Schema::table('order', function (Blueprint $table) {
                $table->string('plateNumber')->nullable()->after('fleetCode')->index();
            });
        }

        if (Schema::hasTable('order') && Schema::hasTable('fleet')) {
            DB::statement("
                UPDATE `order` o
                INNER JOIN `fleet` f ON f.code = o.fleetCode
                SET o.plateNumber = f.plateNumber
                WHERE o.plateNumber IS NULL AND o.fleetCode IS NOT NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order', 'plateNumber')) {
            Schema::table('order', function (Blueprint $table) {
                $table->dropColumn('plateNumber');
            });
        }
    }
};
