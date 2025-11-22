<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_employee', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->text('name')->nullable();
            $table->string('positionCode', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->date('birthDate')->nullable();
            $table->date('joinDate')->nullable();
            $table->string('ktp', 20)->nullable();
            $table->string('npwp', 20)->nullable();
            $table->string('nik', 20)->nullable();
            $table->integer('provinceId')->nullable();
            $table->integer('cityId')->nullable();
            $table->integer('districtId')->nullable();
            $table->text('address')->nullable();
            $table->string('birthPlace', 255)->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->enum('citizenship', ['WNI', 'WNA'])->nullable();
            $table->string('accountNumber', 20)->nullable();
            $table->string('bankCode', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('positionCode')->references('code')->on('fms_position')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('provinceId')->references('id')->on('fms_province')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('cityId')->references('id')->on('fms_city')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('districtId')->references('id')->on('fms_district')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('bankCode')->references('code')->on('fms_bank_account')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_employee');
    }
};
