<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SettingsTableSeeder::class);

        // Struktur menu ERP + akses role (Super Admin & role existing).
        // Urutan penting: MenuSeeder harus jalan sebelum RoleMenuSeeder.
        $this->call([
            MenuSeeder::class,
            RoleMenuSeeder::class,
        ]);
    }
}
