<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_route', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 50)->unique()->nullable();
            $table->text('name')->nullable();
            $table->string('customerCode', 30)->nullable();
            $table->string('originLocationCode', 30)->nullable();
            $table->string('destinationLocationCode', 30)->nullable();
            $table->string('fleetTypeCode', 30)->nullable();
            $table->string('routeTypeCode', 30)->nullable();
            $table->integer('price')->nullable();
            $table->integer('vendorPrice')->nullable();
            $table->integer('personalVendorPrice')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('customerCode')->references('code')->on('fms_customer')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('originLocationCode')->references('code')->on('fms_location')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('destinationLocationCode')->references('code')->on('fms_location')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('fleetTypeCode')->references('code')->on('fms_fleet_type')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('routeTypeCode')->references('code')->on('fms_route_type')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_route');
    }
};
