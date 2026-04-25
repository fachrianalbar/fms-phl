<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ensure the settings table has an is_maintenance flag set to true
        DB::table('settings')->updateOrInsert(
            ['object' => 'is_maintenance'],
            ['value' => 'true']
        );
    }
}
