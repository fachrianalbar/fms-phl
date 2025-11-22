<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_maintenance', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('fleetCode', 30)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('fleetCode')->references('code')->on('fms_fleet')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_maintenance');
    }
};
