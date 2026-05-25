<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temporarily change type to string
        DB::statement("ALTER TABLE cost_component MODIFY COLUMN type VARCHAR(255) NULL");

        // Update existing data based on name containing 'gaji'
        DB::table('cost_component')->where('name', 'like', '%gaji%')->update(['type' => 'salary']);
        DB::table('cost_component')->where('name', 'not like', '%gaji%')->update(['type' => 'cost']);

        // Change to new enum
        DB::statement("ALTER TABLE cost_component MODIFY COLUMN type ENUM('salary', 'cost') DEFAULT 'cost'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE cost_component MODIFY COLUMN type VARCHAR(255) NULL");
        DB::table('cost_component')->update(['type' => null]);
        DB::statement("ALTER TABLE cost_component MODIFY COLUMN type ENUM('Mandatory','Non Mandatory','Allowance') NULL");
    }
};
