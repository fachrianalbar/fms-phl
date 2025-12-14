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
        Schema::table('order_cost', function (Blueprint $table) {
            $table->boolean('is_route')->default(0)->after('type')->comment('1 = dari route_detail, 0 = custom/tambahan user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_cost', function (Blueprint $table) {
            $table->dropColumn('is_route');
        });
    }
};
