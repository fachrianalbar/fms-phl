<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_role_menu', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->nullable();
            $table->string('roleCode', 30)->nullable();
            $table->string('menuCode', 30)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('roleCode')->references('code')->on('fms_role')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('menuCode')->references('code')->on('fms_menu')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_role_menu');
    }
};
