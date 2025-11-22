<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_order', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->string('shipmentNumber', 255)->unique()->nullable();
            $table->date('orderDate')->nullable();
            $table->string('customerCode', 30)->nullable();
            $table->string('materialCode', 30)->nullable();
            $table->integer('materialQty')->nullable();
            $table->string('unitCode', 30)->nullable();
            $table->text('notes')->nullable();
            $table->string('salesOrder', 20)->nullable();
            $table->enum('orderTypeCode', ['Int', 'Ext', 'Dll'])->nullable();
            $table->string('sto', 100)->nullable();
            $table->string('fleetDriverCode', 20)->nullable();
            $table->string('driverCode', 30)->nullable();
            $table->string('routeCode', 20)->nullable();
            $table->string('fleetTypeCode', 20)->nullable();
            $table->string('fleetCode', 30)->nullable();
            $table->float('qty')->nullable();
            $table->tinyInteger('bonUjt')->default(0);
            $table->tinyInteger('status')->default(0)->comment('0 = order dibuat\n1 = dalam perjalanan\n2 = sampai tujuan\n3 = order finish\n4 = return do');
            $table->string('distance', 50)->nullable();
            $table->string('estimatedTime', 100)->nullable();
            $table->date('returnDate')->nullable();
            $table->text('returnDescription')->nullable();
            $table->tinyInteger('is_order_tax')->default(0);
            $table->integer('routeAmount')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('customerCode')->references('code')->on('fms_customer')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('materialCode')->references('code')->on('fms_material')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('unitCode')->references('code')->on('fms_unit')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('fleetDriverCode')->references('code')->on('fms_fleet_driver')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('driverCode')->references('code')->on('fms_employee')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('routeCode')->references('code')->on('fms_route')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('fleetTypeCode')->references('code')->on('fms_fleet_type')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('fleetCode')->references('code')->on('fms_fleet')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_order');
    }
};
