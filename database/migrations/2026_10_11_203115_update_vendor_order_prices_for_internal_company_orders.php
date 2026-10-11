<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Update `vendorPrice` and `vendorPriceSingle` in the `order` table
     * to match the new pricing rule: when a Customer's Company owns the FleetCompany
     * (Internal type) that provides the vehicle, the vendor price should equal the
     * customer price (routeAmount).
     *
     * Hanya order dengan `orderDate` pada bulan Oktober 2026 yang diperbarui.
     */
    public function up(): void
    {
        // Hanya lanjutkan jika tabel 'order' dan 'fleet_company' ada
        if (! Schema::hasTable('order') || ! Schema::hasTable('fleet_company')) {
            return;
        }

        // Backfill companyCode pada fleet_company untuk tipe 'Internal' jika belum terisi
        if (Schema::hasColumn('fleet_company', 'companyCode')) {
            $internalFleetCompanies = DB::table('fleet_company')
                ->where('type', 'Internal')
                ->whereNull('companyCode')
                ->get();

            foreach ($internalFleetCompanies as $fc) {
                $matchingCompany = DB::table('company')->where('name', $fc->name)->first();
                if ($matchingCompany) {
                    DB::table('fleet_company')
                        ->where('id', $fc->id)
                        ->update(['companyCode' => $matchingCompany->code]);
                }
            }
        }

        // Hanya order pada bulan Oktober 2026
        $periodStart = '2026-10-01 00:00:00';
        $periodEnd = '2026-10-31 23:59:59';

        // Dapatkan semua order yang relevan untuk diperbarui
        $ordersToUpdate = DB::table('order as o')
            ->join('customer as c', 'o.customerCode', '=', 'c.code')
            ->join('fleet as f', 'o.fleetCode', '=', 'f.code')
            ->join('fleet_company as fc', 'f.fleetCompanyCode', '=', 'fc.code')
            ->join('company as customer_company', 'c.companyCode', '=', 'customer_company.code')
            ->where('fc.type', 'Internal')
            ->whereNotNull('fc.companyCode')
            ->whereColumn('customer_company.code', 'fc.companyCode')
            ->whereBetween('o.orderDate', [$periodStart, $periodEnd])
            ->select([
                'o.id',
                'o.routeAmount',
                'o.qty',
            ])
            ->get();

        echo "\n--- Ditemukan " . count($ordersToUpdate) . " order (periode " . $periodStart . " s/d " . $periodEnd . ") yang perlu diperbarui ---\n";

        $updatedCount = 0;
        foreach ($ordersToUpdate as $order) {
            $qty = (float) $order->qty;
            $routeAmount = (float) $order->routeAmount;

            // Hitung vendorPriceSingle, hindari pembagian dengan nol
            $vendorPriceSingle = ($qty > 0) ? ($routeAmount / $qty) : 0.0;

            DB::table('order')
                ->where('id', $order->id)
                ->update([
                    'vendorPrice' => $routeAmount,
                    'vendorPriceSingle' => $vendorPriceSingle,
                    'personalVendorPrice' => 0.0, // Atur ke nol karena ini sekarang sama dengan vendorPrice
                    'personalVendorPriceSingle' => 0.0, // Atur ke nol
                    'updated_at' => now(),
                ]);
            
            $updatedCount++;
            echo "Diperbarui Order ID: " . $order->id . "\n";
            echo "  routeAmount: " . $routeAmount . " | qty: " . $qty . "\n";
            echo "  vendorPrice -> " . $routeAmount . "\n";
            echo "  vendorPriceSingle -> " . $vendorPriceSingle . "\n";
        }

        echo "\n--- Selesai. Total order yang diperbarui: " . $updatedCount . " ---\n";
    }

    /**
     * Reverse the migrations.
     *
     * Not supported: This migration updates data, not schema.
     */
    public function down(): void
    {
        echo "Rollback tidak didukung untuk migrasi ini. Data harga order telah diperbarui.\n";
    }
};