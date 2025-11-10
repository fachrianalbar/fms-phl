<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 30)->unique()->nullable();
            $table->string('name', 255);
            $table->string('username', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->string('roleCode', 20)->nullable();
            $table->string('languange', 10)->default('id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_users');
    }
};
