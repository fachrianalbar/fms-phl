<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_location', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->text('name')->nullable();
            $table->integer('provinceId')->nullable();
            $table->integer('cityId')->nullable();
            $table->integer('districtId')->nullable();
            $table->text('address')->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('provinceId')->references('id')->on('fms_province')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('cityId')->references('id')->on('fms_city')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('districtId')->references('id')->on('fms_district')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_location');
    }
};
