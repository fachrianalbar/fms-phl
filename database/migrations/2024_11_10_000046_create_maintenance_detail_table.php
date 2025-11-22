<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_detail', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->integer('qty')->nullable();
            $table->string('itemCode', 30)->nullable();
            $table->string('maintenanceCode', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_detail');
    }
};
