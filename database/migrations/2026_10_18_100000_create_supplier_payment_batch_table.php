<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Batch pembayaran hutang supplier.
     *
     * Satu batch = satu transaksi pembayaran yang dapat melunasi / mencicil
     * beberapa pembelian (PO) sekaligus. Setiap alokasi per pembelian dicatat
     * di purchase_payment_histories (kolom batch_code).
     */
    public function up(): void
    {
        if (! Schema::hasTable('supplier_payment_batch')) {
            Schema::create('supplier_payment_batch', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code', 30)->nullable();
                $table->string('request_key', 64)->nullable();
                $table->string('payload_hash', 64)->nullable();
                $table->string('status', 20)->default('active');
                $table->date('payment_date')->nullable();
                $table->string('user_bank_code', 30)->nullable();
                $table->double('amount')->default(0);
                $table->integer('purchase_count')->default(0);
                $table->integer('fully_paid_count')->default(0);
                $table->integer('partial_count')->default(0);
                $table->text('description')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->unique('code', 'supplier_payment_batch_code_unique');
                $table->unique('request_key', 'supplier_payment_batch_request_key_unique');
            });
        }

        if (! Schema::hasColumn('purchase_payment_histories', 'batch_code')) {
            Schema::table('purchase_payment_histories', function (Blueprint $table) {
                $table->string('batch_code', 30)->nullable()->after('purchaseCode');
                $table->index('batch_code', 'purchase_payment_histories_batch_code_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_payment_histories', 'batch_code')) {
            Schema::table('purchase_payment_histories', function (Blueprint $table) {
                $table->dropIndex('purchase_payment_histories_batch_code_index');
                $table->dropColumn('batch_code');
            });
        }

        Schema::dropIfExists('supplier_payment_batch');
    }
};
