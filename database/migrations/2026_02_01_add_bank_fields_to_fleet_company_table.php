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
        Schema::table('fleet_company', function (Blueprint $table) {
            $table->string('accountNumber')->nullable()->after('address');
            $table->string('bankName')->nullable()->after('accountNumber');
            $table->decimal('pph', 5, 2)->default(0)->after('bankName')->comment('PPH Percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleet_company', function (Blueprint $table) {
            $table->dropColumn(['accountNumber', 'bankName', 'pph']);
        });
    }
};
