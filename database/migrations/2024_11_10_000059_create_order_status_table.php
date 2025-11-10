<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_order_status', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->tinyInteger('code')->unique()->nullable();
            $table->string('name', 255)->nullable();
            $table->string('nama', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_order_status');
    }
};
