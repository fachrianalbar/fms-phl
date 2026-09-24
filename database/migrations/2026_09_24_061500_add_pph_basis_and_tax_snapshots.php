<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            $table->string('pphBaseType', 20)->default('subtotal')->after('pph');
        });

        Schema::table('invoice', function (Blueprint $table) {
            $table->decimal('ppnRate', 8, 4)->default(0)->after('usePph');
            $table->decimal('pphRate', 8, 4)->default(0)->after('ppnRate');
            $table->string('pphBaseType', 20)->default('subtotal')->after('pphRate');
            $table->bigInteger('pphBaseAmount')->default(0)->after('pphBaseType');
        });

        DB::table('invoice')
            ->select(['id', 'customerCode', 'invoiceAmount', 'ppnAmount', 'pphAmount'])
            ->orderBy('id')
            ->chunk(200, function ($invoices): void {
                $customerRates = DB::table('customer')
                    ->whereIn('code', $invoices->pluck('customerCode')->filter()->unique())
                    ->get(['code', 'ppn', 'pph'])
                    ->keyBy('code');

                foreach ($invoices as $invoice) {
                    $subtotal = (float) ($invoice->invoiceAmount ?? 0);
                    $customer = $customerRates->get($invoice->customerCode);
                    $ppnRate = $subtotal > 0 && (float) ($invoice->ppnAmount ?? 0) !== 0.0
                        ? ((float) $invoice->ppnAmount / $subtotal) * 100
                        : (float) ($customer->ppn ?? 0);
                    $pphRate = $subtotal > 0 && (float) ($invoice->pphAmount ?? 0) !== 0.0
                        ? ((float) $invoice->pphAmount / $subtotal) * 100
                        : (float) ($customer->pph ?? 0);

                    DB::table('invoice')->where('id', $invoice->id)->update([
                        'ppnRate' => round($ppnRate, 4),
                        'pphRate' => round($pphRate, 4),
                        'pphBaseType' => 'subtotal',
                        'pphBaseAmount' => (int) round($subtotal),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropColumn(['ppnRate', 'pphRate', 'pphBaseType', 'pphBaseAmount']);
        });

        Schema::table('customer', function (Blueprint $table) {
            $table->dropColumn('pphBaseType');
        });
    }
};
