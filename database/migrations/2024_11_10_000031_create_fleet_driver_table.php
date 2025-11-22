<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_driver', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique();
            $table->string('fleetTypeCode', 20)->nullable();
            $table->string('fleetCode', 20)->nullable();
            $table->string('vehicleRegistrationNumber', 50)->nullable();
            $table->date('vehicleRegistrationNumberExpDate')->nullable();
            $table->string('kir', 50)->nullable();
            $table->date('kirExpDate')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('fleetTypeCode')->references('code')->on('fleet_type')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('fleetCode')->references('code')->on('fleet')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_driver');
    }
};
