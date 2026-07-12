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
        Schema::table('order_payment', function (Blueprint $table) {
            $table->integer('additional_cost')->nullable()->after('cost');
            $table->integer('ppn')->nullable()->after('pph');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_payment', function (Blueprint $table) {
            $table->dropColumn(['additional_cost', 'ppn']);
        });
    }
};
