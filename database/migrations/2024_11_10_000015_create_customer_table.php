<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->text('name')->nullable();
            $table->text('officeAddress')->nullable();
            $table->text('billingAddress')->nullable();
            $table->string('phone', 20)->nullable();
            $table->integer('accountNumber')->nullable();
            $table->integer('ppn')->nullable();
            $table->integer('pph')->nullable();
            $table->string('invoiceFormat', 100)->nullable();
            $table->string('nickname', 100)->nullable();
            $table->string('picName', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('npwp', 50)->nullable();
            $table->string('telegramUsername', 255)->nullable();
            $table->string('companyCode', 255)->nullable();
            $table->tinyInteger('dueDateDuration')->nullable();
            $table->enum('type', ['Company', 'Individual'])->nullable();
            $table->tinyInteger('isDo')->nullable();
            $table->string('invoicePdf', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['email', 'phone', 'telegramUsername', 'npwp']);
            $table->index(['name', 'companyCode']);
            $table->foreign('companyCode')->references('code')->on('company')->onDelete('cascade')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
