<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_payment', function (Blueprint $table) {

            if (!Schema::hasColumn('vendor_payment', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('initial_amount');
            }
            if (!Schema::hasColumn('vendor_payment', 'remaining_amount')) {
                $table->decimal('remaining_amount', 15, 2)->nullable()->after('paid_amount');
            }
            if (!Schema::hasColumn('vendor_payment', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending')->after('remaining_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_payment', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_payment', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
            if (Schema::hasColumn('vendor_payment', 'remaining_amount')) {
                $table->dropColumn('remaining_amount');
            }
            if (Schema::hasColumn('vendor_payment', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
