<?php

namespace Database\Seeders;

use App\Models\Master\Company;
use App\Models\Master\FleetCompany;
use Illuminate\Database\Seeder;

class FleetCompanyCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dapatkan semua FleetCompany dengan tipe 'Internal'
        $fleetCompanies = FleetCompany::where('type', 'Internal')->get();

        foreach ($fleetCompanies as $fleetCompany) {
            // Cari Company dengan nama yang sama
            $company = Company::where('name', $fleetCompany->name)->first();

            if ($company) {
                // Jika ditemukan, perbarui kolom companyCode
                $fleetCompany->update(['companyCode' => $company->code]);
            }
        }
    }
}
