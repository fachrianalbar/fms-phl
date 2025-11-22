<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_user', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->nullable();
            $table->string('chatId', 50);
            $table->string('username', 255)->nullable();
            $table->string('firstName', 255)->nullable();
            $table->string('lastName', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['code', 'chatId', 'username']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_user');
    }
};
