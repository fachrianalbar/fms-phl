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
        if (! Schema::hasTable('fleet_plate_histories')) {
            Schema::create('fleet_plate_histories', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_0900_ai_ci';

                $table->uuid('id')->primary();
                $table->string('fleet_id', 50)->index();
                $table->string('fleet_code')->nullable()->index();
                $table->string('old_plate_number');
                $table->string('new_plate_number');
                $table->date('change_date')->nullable();
                $table->text('reason')->nullable();
                $table->string('changed_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('fleet_id')
                    ->references('id')
                    ->on('fleet')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_plate_histories');
    }
};
