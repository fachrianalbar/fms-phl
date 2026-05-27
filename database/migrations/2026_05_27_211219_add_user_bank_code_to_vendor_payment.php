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
            $table->string('user_bank_code', 50)->nullable()->after('nota_number');
            $table->index('user_bank_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_payment', function (Blueprint $table) {
            $table->dropIndex(['user_bank_code']);
            $table->dropColumn('user_bank_code');
        });
    }
};
