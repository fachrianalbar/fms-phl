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
        Schema::table('order_detail', function (Blueprint $table) {
            $table->string('order_id', 36)->nullable()->after('id');
            $table->string('file')->nullable()->after('order_id');
            $table->enum('type', ['surat_jalan', 'other'])->default('other')->after('file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_detail', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'file', 'type']);
        });
    }
};
