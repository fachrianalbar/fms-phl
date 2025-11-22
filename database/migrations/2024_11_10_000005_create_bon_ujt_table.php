<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bon_ujt', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique()->nullable();
            $table->string('bon', 50)->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->date('submitDate')->nullable();
            $table->string('handover', 255)->nullable();
            $table->integer('note')->nullable();
            $table->string('createdBy', 20)->nullable();
            $table->string('fleetTypeCode', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('fleetTypeCode')->references('code')->on('fleet_type')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_ujt');
    }
};
