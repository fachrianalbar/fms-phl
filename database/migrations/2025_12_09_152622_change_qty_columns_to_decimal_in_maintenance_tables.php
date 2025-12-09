<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenance_detail', function (Blueprint $table) {
            $table->decimal('qty', 10, 1)->change();
        });

        Schema::table('maintenance_fifo', function (Blueprint $table) {
            $table->decimal('qty', 10, 1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_detail', function (Blueprint $table) {
            $table->integer('qty')->change();
        });

        Schema::table('maintenance_fifo', function (Blueprint $table) {
            $table->integer('qty')->change();
        });
    }
};
