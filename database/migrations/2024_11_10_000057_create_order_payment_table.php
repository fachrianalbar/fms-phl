<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payment', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->nullable();
            $table->string('orderCode', 30)->nullable();
            $table->integer('cost')->nullable();
            $table->integer('pph')->nullable();
            $table->integer('total')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 = Not Completed\n1 = Completed');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('orderCode')->references('code')->on('order')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payment');
    }
};
