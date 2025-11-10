<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_customer_detail_order', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 255)->unique()->nullable();
            $table->string('value', 255)->nullable();
            $table->string('orderCode', 255)->nullable();
            $table->string('customerDetailCode', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('orderCode')->references('code')->on('fms_order')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('customerDetailCode')->references('code')->on('fms_customer_detail')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_customer_detail_order');
    }
};
