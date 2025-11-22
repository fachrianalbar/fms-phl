<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->string('invoiceNumber', 100)->nullable();
            $table->string('poNumber', 100)->nullable();
            $table->string('receiptNumber', 100)->nullable();
            $table->date('invoiceDate')->nullable();
            $table->date('overdueDate')->nullable();
            $table->text('notes')->nullable();
            $table->string('customerCode', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('customerCode')->references('code')->on('customer')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
