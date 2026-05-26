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
        Schema::create('driver_salary', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique();
            $table->string('driverCode', 30)->nullable();
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->decimal('totalSalary', 15, 2)->default(0);
            $table->decimal('totalAdjustment', 15, 2)->default(0);
            $table->decimal('grandTotal', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('driver_salary_detail', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 30)->unique();
            $table->string('driverSalaryCode', 30)->nullable();
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['addition', 'deduction'])->default('addition');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_salary_detail');
        Schema::dropIfExists('driver_salary');
    }
};
