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
        // Change stock table columns
        Schema::table('stock', function (Blueprint $table) {
            $table->decimal('stockIn', 10, 1)->change();
            $table->decimal('stockOut', 10, 1)->change();
        });

        // Change stock_transaction table columns
        Schema::table('stock_transaction', function (Blueprint $table) {
            $table->decimal('qtyIn', 10, 1)->change();
            $table->decimal('qtyOut', 10, 1)->change();
        });

        // Change purchase_detail table columns
        Schema::table('purchase_detail', function (Blueprint $table) {
            $table->decimal('qty', 10, 1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert stock table columns back to integer
        Schema::table('stock', function (Blueprint $table) {
            $table->integer('stockIn')->change();
            $table->integer('stockOut')->change();
        });

        // Revert stock_transaction table columns back to integer
        Schema::table('stock_transaction', function (Blueprint $table) {
            $table->integer('qtyIn')->change();
            $table->integer('qtyOut')->change();
        });

        // Revert purchase_detail table columns back to integer
        Schema::table('purchase_detail', function (Blueprint $table) {
            $table->integer('qty')->change();
        });
    }
};
