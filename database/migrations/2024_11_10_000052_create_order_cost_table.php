<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_order_cost', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 20)->nullable();
            $table->string('orderCode', 20)->nullable();
            $table->string('componentType', 255)->nullable();
            $table->integer('nominal')->nullable();
            $table->string('type', 255)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('orderCode')->references('code')->on('fms_order')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_order_cost');
    }
};
