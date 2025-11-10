<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_fleet', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('code', 30)->nullable();
            $table->string('plateNumber', 20)->nullable();
            $table->string('deviceName', 30)->nullable();
            $table->year('year')->nullable();
            $table->string('engineNumber', 100)->nullable();
            $table->string('frameNumber', 100)->nullable();
            $table->string('fleetBrandCode', 20)->nullable();
            $table->string('fleetTypeCode', 20)->nullable();
            $table->string('insurance', 100)->nullable();
            $table->string('vehicleRegistrationNumber', 100)->nullable();
            $table->string('barcode', 255)->nullable();
            $table->string('fleetCompanyCode', 30)->nullable();
            $table->date('vehicleRegistrationDueDate')->nullable();
            $table->string('driverCode', 30)->nullable();
            $table->string('barcodeNumber', 100)->nullable();
            $table->date('vehicleTax')->nullable();
            $table->date('vehicleKir')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->unique(['plateNumber', 'deviceName', 'code']);
            $table->foreign('fleetBrandCode')->references('code')->on('fms_fleet_brand')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('fleetTypeCode')->references('code')->on('fms_fleet_type')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('fleetCompanyCode')->references('code')->on('fms_fleet_company')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('driverCode')->references('code')->on('fms_employee')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_fleet');
    }
};
