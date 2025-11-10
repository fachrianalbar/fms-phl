<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_stock_transaction', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('code', 30)->nullable();
            $table->date('date')->nullable();
            $table->string('itemCode', 30)->nullable();
            $table->string('warehouseCode', 30)->nullable();
            $table->integer('qtyIn')->nullable();
            $table->integer('qtyOut')->nullable();
            $table->string('transactionCode', 30)->nullable();
            $table->string('transactionDetailCode', 30)->nullable();
            $table->string('transactionType', 50)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('itemCode')->references('code')->on('fms_item')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('warehouseCode')->references('code')->on('fms_warehouse')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_stock_transaction');
    }
};
