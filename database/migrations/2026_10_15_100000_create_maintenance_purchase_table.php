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
        if (Schema::hasTable('maintenance_purchase')) {
            return;
        }

        Schema::create('maintenance_purchase', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci'; // samakan dengan tabel lama (MySQL 8 default)

            $table->string('id', 36)->collation('utf8mb4_0900_ai_ci')->primary();
            $table->string('purchase_id', 36)->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->string('maintenance_id', 36)->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->timestamps();

            $table->unique(['maintenance_id', 'purchase_id'], 'maintenance_purchase_unique');

            $table->foreign('purchase_id')
                ->references('id')
                ->on('purchase')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('maintenance_id')
                ->references('id')
                ->on('maintenance')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_purchase');
    }
};
