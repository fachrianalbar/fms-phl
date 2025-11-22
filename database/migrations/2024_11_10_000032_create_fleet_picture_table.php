<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_fleet_picture', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->string('fleetCode', 30)->nullable();
            $table->string('fleetPicture', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('fleetCode')->references('code')->on('fms_fleet')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_fleet_picture');
    }
};
