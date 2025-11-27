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
        Schema::create('cost_component_price_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('costComponentCode');
            $table->string('costComponentName');
            $table->decimal('oldPrice', 15, 2)->nullable();
            $table->decimal('newPrice', 15, 2)->nullable();
            $table->string('changedBy')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('costComponentCode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_component_price_logs');
    }
};
