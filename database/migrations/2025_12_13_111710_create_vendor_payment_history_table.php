<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('vendor_payment_history')) {
            Schema::create('vendor_payment_history', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('vendor_payment_id');
                $table->decimal('amount', 15, 2);
                $table->date('payment_date');
                $table->string('user_bank_code')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('vendor_payment_id')
                    ->references('id')
                    ->on('vendor_payment')
                    ->onDelete('cascade');

                $table->index('vendor_payment_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payment_history');
    }
};
