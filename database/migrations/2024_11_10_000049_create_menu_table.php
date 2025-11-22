<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_menu', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->nullable();
            $table->text('name')->nullable();
            $table->text('nama')->nullable();
            $table->string('parentCode', 20)->nullable();
            $table->string('url', 50)->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('sort')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_menu');
    }
};
