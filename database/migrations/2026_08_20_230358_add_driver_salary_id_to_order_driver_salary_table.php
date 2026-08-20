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
        Schema::table('order_driver_salary', function (Blueprint $table) {
            $table->string('driver_salary_id', 36)->nullable()->collation('utf8mb4_unicode_ci')->after('driver_id');

            $table->foreign('driver_salary_id')
                ->references('id')
                ->on('driver_salary')
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
            $table->dropForeign(['driver_salary_id']);
            $table->dropColumn('driver_salary_id');
        });
    }
};
