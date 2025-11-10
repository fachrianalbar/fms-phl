<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_order_payment_history', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->nullable();
            $table->string('orderCode', 30)->nullable();
            $table->enum('paymentType', ['Dp', 'Full'])->nullable();
            $table->integer('total')->nullable();
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->string('userBankCode', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_order_payment_history');
    }
};
