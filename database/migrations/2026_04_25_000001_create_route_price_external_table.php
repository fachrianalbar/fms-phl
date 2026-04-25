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
        // Handle partially-created table from a previous failed migration run.
        Schema::dropIfExists('route_price_external');

        Schema::create('route_price_external', function (Blueprint $table) {
            $table->string('id', 36)->collation('utf8mb4_0900_ai_ci')->primary();
            $table->string('route_id', 36)->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->string('fleet_company_id', 36)->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('route_id')
                ->references('id')
                ->on('route')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('fleet_company_id')
                ->references('id')
                ->on('fleet_company')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_price_external');
    }
};
