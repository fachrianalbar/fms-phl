<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cost_component MODIFY COLUMN type VARCHAR(255) NULL");

        DB::table('cost_component')
            ->whereRaw("LOWER(name) LIKE '%komisi%'")
            ->where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '!=', 'salary');
            })
            ->update(['type' => 'commission']);

        DB::statement("ALTER TABLE cost_component MODIFY COLUMN type ENUM('salary', 'cost', 'commission') DEFAULT 'cost'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cost_component MODIFY COLUMN type VARCHAR(255) NULL");

        DB::table('cost_component')
            ->where('type', 'commission')
            ->update(['type' => 'cost']);

        DB::statement("ALTER TABLE cost_component MODIFY COLUMN type ENUM('salary', 'cost') DEFAULT 'cost'");
    }
};
