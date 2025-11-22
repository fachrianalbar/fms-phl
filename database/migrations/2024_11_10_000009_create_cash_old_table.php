<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_old', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->text('description')->nullable();
            $table->integer('nominal')->nullable();
            $table->enum('type', ['Cash', 'Expense', 'Expense Office'])->nullable();
            $table->string('receiver', 30)->nullable();
            $table->enum('transferType', ['Cash', 'Transfer'])->nullable();
            $table->string('bankSender', 30)->nullable();
            $table->string('bankReceiver', 30)->nullable();
            $table->enum('cashType', ['Masuk', 'Keluar'])->nullable();
            $table->string('driverCode', 30)->nullable();
            $table->string('createdBy', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_old');
    }
};
