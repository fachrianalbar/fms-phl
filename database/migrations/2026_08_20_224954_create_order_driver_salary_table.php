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
        Schema::dropIfExists('order_driver_salary');

        Schema::create('order_driver_salary', function (Blueprint $table) {
            $table->string('id', 36)->collation('utf8mb4_0900_ai_ci')->primary();
            $table->string('order_id', 36)->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->string('driver_id', 36)->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->string('cost_component_id', 36)->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['0', '1'])->default('0');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')
                ->references('id')
                ->on('order')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('driver_id')
                ->references('id')
                ->on('employee')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('cost_component_id')
                ->references('id')
                ->on('cost_component')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_driver_salary');
    }
};

