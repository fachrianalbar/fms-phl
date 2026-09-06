<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('vendor_payment_history', ['batch_code'])) {
            Schema::table('vendor_payment_history', function (Blueprint $table) {
                $table->index('batch_code', 'vendor_payment_history_batch_code_index');
            });
        }

        Schema::create('vendor_payment_batch', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->uuid('request_key')->unique();
            $table->char('payload_hash', 64);
            $table->string('status', 20)->default('active')->index();
            $table->date('payment_date');
            $table->string('user_bank_code', 50)->index();
            $table->unsignedBigInteger('amount');
            $table->unsignedInteger('nota_count');
            $table->unsignedInteger('order_count');
            $table->unsignedInteger('fully_paid_count')->default(0);
            $table->unsignedInteger('partial_count')->default(0);
            $table->string('description')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payment_batch');

        if (Schema::hasIndex('vendor_payment_history', 'vendor_payment_history_batch_code_index')) {
            Schema::table('vendor_payment_history', function (Blueprint $table) {
                $table->dropIndex('vendor_payment_history_batch_code_index');
            });
        }
    }
};
