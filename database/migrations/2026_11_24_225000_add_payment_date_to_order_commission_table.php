<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_commission') && ! Schema::hasColumn('order_commission', 'payment_date')) {
            Schema::table('order_commission', function (Blueprint $table) {
                $table->date('payment_date')->nullable()->index()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_commission') && Schema::hasColumn('order_commission', 'payment_date')) {
            Schema::table('order_commission', function (Blueprint $table) {
                $table->dropColumn('payment_date');
            });
        }
    }
};
