<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_purchase_detail', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->string('purchaseCode', 30)->nullable();
            $table->string('itemCode', 30)->nullable();
            $table->integer('qty')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->integer('receivedQty')->nullable();
            $table->integer('qtyUsed')->nullable();
            $table->text('description')->nullable();
            $table->integer('price')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_purchase_detail');
    }
};
