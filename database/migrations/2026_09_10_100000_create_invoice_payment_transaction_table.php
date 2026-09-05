<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel header transaksi pembayaran invoice.
     * 1 baris = 1 transaksi pembayaran (1 transfer uang masuk) yang
     * dapat menutup banyak invoice sekaligus beserta claim (pengurang tagihan).
     */
    public function up(): void
    {
        Schema::create('invoice_payment_transaction', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci'; // samakan dengan tabel lama (MySQL 8 default)

            $table->uuid('id')->primary();
            $table->string('code', 30)->nullable()->unique();
            $table->date('paymentDate')->nullable();
            $table->string('customerCode', 30)->nullable();
            $table->string('userBankCode', 30)->nullable();
            $table->bigInteger('amount')->default(0); // total uang riil diterima (kas masuk)
            $table->bigInteger('totalClaim')->default(0); // total claim (pengurang tagihan, tidak masuk kas)
            $table->text('description')->nullable();
            $table->string('paymentReceipt', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customerCode');
            $table->index('userBankCode');
            $table->index('paymentDate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payment_transaction');
    }
};
