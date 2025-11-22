<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->string('name', 100)->nullable();
            $table->string('brandName', 100)->nullable();
            $table->string('categoryCode', 30)->nullable();
            $table->string('itemLocationCode', 30)->nullable();
            $table->string('warehouseCode', 30)->nullable();
            $table->string('unitCode', 30)->nullable();
            $table->string('supplierCode', 30)->nullable();
            $table->decimal('price', 20, 2)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique('id');
            $table->foreign('categoryCode')->references('code')->on('item_category')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('warehouseCode')->references('code')->on('warehouse')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('unitCode')->references('code')->on('unit')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('supplierCode')->references('code')->on('supplier')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item');
    }
};
