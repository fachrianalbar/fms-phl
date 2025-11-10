<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_cost_component', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique();
            $table->text('name');
            $table->enum('type', ['Mandatory', 'Non Mandatory', 'Allowance'])->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_cost_component');
    }
};
