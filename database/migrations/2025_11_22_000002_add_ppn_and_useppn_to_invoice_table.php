<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->integer('ppnAmount')->nullable()->after('invoiceAmount');
            $table->boolean('usePpn')->default(true)->after('ppnAmount');
        });
    }

    public function down(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropColumn('ppnAmount');
            $table->dropColumn('usePpn');
        });
    }
};
