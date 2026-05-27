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
            $table->string('nota_number', 20)->nullable()->after('payment_status');
            $table->index('nota_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_payment', function (Blueprint $table) {
            $table->dropIndex(['nota_number']);
            $table->dropColumn('nota_number');
        });
    }
};
