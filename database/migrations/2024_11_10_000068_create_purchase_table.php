<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_purchase', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('supplierCode', 30)->nullable();
            $table->string('warehouseCode', 30)->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 = purchase dibuat\n1 = purchase di approve');
            $table->date('receivedDate')->nullable();
            $table->date('paymentDate')->nullable();
            $table->date('dueDate')->nullable();
            $table->string('paymentCode', 30)->nullable();
            $table->integer('nominal')->nullable();
            $table->string('userBankCode', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('supplierCode')->references('code')->on('fms_supplier')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('warehouseCode')->references('code')->on('fms_warehouse')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('userBankCode')->references('code')->on('fms_user_bank')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_purchase');
    }
};
