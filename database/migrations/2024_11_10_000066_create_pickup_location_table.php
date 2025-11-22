<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_location', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->string('name', 255)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->string('latitude', 50)->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->string('locationCode', 20)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_location');
    }
};
