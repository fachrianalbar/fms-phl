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
            $table->string('driverCode', 30)->nullable()->collation('utf8mb4_0900_ai_ci')->after('orderCode');
        });

        Schema::table('order_driver_salary', function (Blueprint $table) {
            $table->string('order_cost_id', 36)->nullable()->collation('utf8mb4_0900_ai_ci')->after('cost_component_id');

            $table->foreign('order_cost_id')
                ->references('id')
                ->on('order_cost')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_driver_salary', function (Blueprint $table) {
            $table->dropForeign(['order_cost_id']);
            $table->dropColumn('order_cost_id');
        });

        Schema::table('order_cost', function (Blueprint $table) {
            $table->dropColumn('driverCode');
        });
    }
};
