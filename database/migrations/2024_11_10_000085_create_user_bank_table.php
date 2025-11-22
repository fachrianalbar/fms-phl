<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_bank', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->string('accountNumber', 100)->nullable();
            $table->string('accountName', 255)->nullable();
            $table->tinyInteger('type')->nullable()->comment('1 = person\n2 = company');
            $table->string('bankCode', 30)->nullable();
            $table->integer('balance')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('bankCode')->references('code')->on('bank_account')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bank');
    }
};
