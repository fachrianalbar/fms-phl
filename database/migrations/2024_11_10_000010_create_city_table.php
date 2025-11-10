<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_city', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 30)->unique()->nullable();
            $table->string('name', 100);
            $table->integer('province_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
            $table->string('provinceCode', 20)->nullable();

            $table->foreign('province_id')->references('id')->on('fms_province');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_city');
    }
};
