<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('commission_report')) {
            Schema::create('commission_report', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_0900_ai_ci';
                $table->string('id', 36)->primary();
                $table->string('code', 30)->unique();
                $table->string('recipientName', 255)->nullable();
                $table->string('customerCode', 50)->nullable()->index();
                $table->json('componentCodes')->nullable();
                $table->date('startDate')->nullable()->index();
                $table->date('endDate')->nullable()->index();
                $table->date('paymentDate')->nullable()->index();
                $table->decimal('totalCommission', 15, 2)->default(0);
                $table->decimal('totalAdjustment', 15, 2)->default(0);
                $table->decimal('grandTotal', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('commission_report_detail')) {
            Schema::create('commission_report_detail', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_0900_ai_ci';
                $table->string('id', 36)->primary();
                $table->string('code', 30)->unique();
                $table->string('commissionReportCode', 30)->nullable()->index();
                $table->date('date')->nullable();
                $table->text('description')->nullable();
                $table->enum('type', ['addition', 'deduction'])->default('addition');
                $table->decimal('nominal', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('order_commission')) {
            Schema::create('order_commission', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_0900_ai_ci';
                $table->string('id', 36)->primary();
                $table->string('order_id', 36)->nullable()->index();
                $table->string('cost_component_id', 36)->nullable()->index();
                $table->string('order_cost_id', 36)->nullable()->index();
                $table->string('commission_report_id', 36)->nullable()->index();
                $table->decimal('amount', 15, 2)->default(0);
                $table->enum('status', ['0', '1'])->default('0')->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_commission');
        Schema::dropIfExists('commission_report_detail');
        Schema::dropIfExists('commission_report');
    }
};
