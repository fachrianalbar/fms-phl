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
        Schema::table('purchase', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase', 'subtotal')) {
                $table->decimal('subtotal', 18, 2)->default(0)->after('paymentCode');
            }
            if (! Schema::hasColumn('purchase', 'ppn_rate')) {
                $table->decimal('ppn_rate', 5, 2)->default(0)->after('subtotal');
            }
            if (! Schema::hasColumn('purchase', 'ppn_amount')) {
                $table->decimal('ppn_amount', 18, 2)->default(0)->after('ppn_rate');
            }
        });

        DB::statement('UPDATE purchase SET subtotal = COALESCE(nominal, 0), ppn_rate = 0, ppn_amount = 0 WHERE subtotal = 0 AND nominal IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase', function (Blueprint $table) {
            $columns = [];
            foreach (['ppn_amount', 'ppn_rate', 'subtotal'] as $col) {
                if (Schema::hasColumn('purchase', $col)) {
                    $columns[] = $col;
                }
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
