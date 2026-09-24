<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->bigInteger('routeAmount')->default(0)->after('invoiceAmount');
            $table->bigInteger('onChargeAmount')->default(0)->after('routeAmount');
        });

        DB::table('invoice')
            ->select(['id', 'code', 'invoiceAmount'])
            ->orderBy('id')
            ->chunk(200, function ($invoices): void {
                foreach ($invoices as $invoice) {
                    $routeAmount = (float) DB::table('invoice_detail as detail')
                        ->join('order as orders', 'orders.code', '=', 'detail.orderCode')
                        ->where('detail.invoiceCode', $invoice->code)
                        ->whereNull('detail.deleted_at')
                        ->whereNull('orders.deleted_at')
                        ->sum('orders.routeAmount');

                    $onChargeAmount = (float) DB::table('invoice_detail as detail')
                        ->join('order_cost as costs', 'costs.orderCode', '=', 'detail.orderCode')
                        ->where('detail.invoiceCode', $invoice->code)
                        ->whereNull('detail.deleted_at')
                        ->whereRaw('LOWER(costs.type) = ?', ['on charge'])
                        ->sum('costs.nominal');

                    // Pertahankan DPP historis. Selisih yang tidak lagi ditemukan pada detail
                    // dimasukkan ke On Charge agar route + on charge tetap sama dengan invoiceAmount.
                    $storedSubtotal = (float) ($invoice->invoiceAmount ?? 0);
                    if (round($routeAmount + $onChargeAmount) !== round($storedSubtotal)) {
                        $onChargeAmount = max($storedSubtotal - $routeAmount, 0);
                    }

                    DB::table('invoice')->where('id', $invoice->id)->update([
                        'routeAmount' => (int) round($routeAmount),
                        'onChargeAmount' => (int) round($onChargeAmount),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropColumn(['routeAmount', 'onChargeAmount']);
        });
    }
};
