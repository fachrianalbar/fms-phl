<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_order_material', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->nullable();
            $table->string('orderCode', 30)->nullable();
            $table->string('materialCode', 30)->nullable();
            $table->string('unitCode', 30)->nullable();
            $table->integer('materialQty')->nullable();
            $table->integer('materialQty2')->nullable();
            $table->string('unitCode2', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('orderCode')->references('code')->on('fms_order')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('materialCode')->references('code')->on('fms_material')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('unitCode')->references('code')->on('fms_unit')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('unitCode2')->references('code')->on('fms_unit')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_order_material');
    }
};
