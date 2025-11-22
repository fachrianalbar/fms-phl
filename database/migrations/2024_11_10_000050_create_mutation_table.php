<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutations', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->string('userBankCode', 30)->nullable();
            $table->dateTime('date')->nullable();
            $table->text('description')->nullable();
            $table->integer('nominal')->nullable();
            $table->string('type', 10)->nullable();
            $table->string('transactionTypeCode', 30)->nullable();
            $table->string('transactionCode', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('userBankCode')->references('code')->on('user_bank')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('transactionTypeCode')->references('code')->on('transaction_type')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutations');
    }
};
