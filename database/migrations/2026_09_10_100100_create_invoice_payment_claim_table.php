<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel claim (biaya lain-lain) pengurang tagihan invoice.
     * Claim tercatat di dalam satu transaksi pembayaran (transactionCode)
     * dan boleh melekat pada invoice tertentu (invoiceCode).
     */
    public function up(): void
    {
        Schema::create('invoice_payment_claim', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci'; // samakan dengan tabel lama (MySQL 8 default)

            $table->uuid('id')->primary();
            $table->string('code', 30)->nullable()->unique();
            $table->string('transactionCode', 30)->nullable();
            $table->string('invoiceCode', 30)->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('amount')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('transactionCode');
            $table->index('invoiceCode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payment_claim');
    }
};
