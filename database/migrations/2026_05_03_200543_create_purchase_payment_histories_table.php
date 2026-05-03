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
        Schema::create('purchase_payment_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('purchaseCode')->nullable();
            $table->double('amount')->default(0);
            $table->date('paymentDate')->nullable();
            $table->string('userBankCode')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::table('purchase', function (Blueprint $table) {
            $table->double('paidAmount')->default(0)->after('nominal');
            $table->string('paymentStatus')->default('Unpaid')->after('paidAmount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase', function (Blueprint $table) {
            $table->dropColumn('paidAmount');
            $table->dropColumn('paymentStatus');
        });
        Schema::dropIfExists('purchase_payment_histories');
    }
};
