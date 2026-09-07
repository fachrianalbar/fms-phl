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
        if (Schema::hasTable('order_payment')) {
            Schema::table('order_payment', function (Blueprint $table) {
                if (! Schema::hasColumn('order_payment', 'claim')) {
                    $table->decimal('claim', 15, 2)->default(0)->nullable()->after('additional_cost');
                }
                if (! Schema::hasColumn('order_payment', 'claim_description')) {
                    $table->string('claim_description', 255)->nullable()->after('claim');
                }
            });
        }

        if (Schema::hasTable('order_payment_history')) {
            Schema::table('order_payment_history', function (Blueprint $table) {
                if (! Schema::hasColumn('order_payment_history', 'claim')) {
                    $table->decimal('claim', 15, 2)->default(0)->nullable()->after('paymentType');
                }
                if (! Schema::hasColumn('order_payment_history', 'claim_description')) {
                    $table->string('claim_description', 255)->nullable()->after('claim');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_payment')) {
            Schema::table('order_payment', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('order_payment', 'claim')) {
                    $columns[] = 'claim';
                }
                if (Schema::hasColumn('order_payment', 'claim_description')) {
                    $columns[] = 'claim_description';
                }
                if (! empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('order_payment_history')) {
            Schema::table('order_payment_history', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('order_payment_history', 'claim')) {
                    $columns[] = 'claim';
                }
                if (Schema::hasColumn('order_payment_history', 'claim_description')) {
                    $columns[] = 'claim_description';
                }
                if (! empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
