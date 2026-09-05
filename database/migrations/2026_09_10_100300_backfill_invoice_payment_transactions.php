<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Backfill data lama: setiap pembayaran invoice yang sudah berjalan
     * (invoice_payment tanpa transactionCode) dibungkus menjadi satu
     * transaksi pembayaran tunggal agar seluruh riwayat tetap tampil
     * di daftar Transaksi Pembayaran.
     *
     * PENTING:
     * - Tidak membuat mutasi bank / live mutation baru (keuangan sudah tercatat).
     * - Tidak mengubah isi kolom lama selain mengisi transactionCode.
     * - Idempotent: hanya memproses baris yang transactionCode-nya masih NULL.
     */
    public function up(): void
    {
        $now = Carbon::now();

        $payments = DB::table('invoice_payment')
            ->whereNull('transactionCode')
            ->whereNull('deleted_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $index = 0;
        foreach ($payments as $payment) {
            if (empty($payment->invoiceCode)) {
                continue;
            }

            $invoice = DB::table('invoice')
                ->where('code', $payment->invoiceCode)
                ->whereNull('deleted_at')
                ->first();

            if (! $invoice) {
                continue;
            }

            $paymentDate = $payment->paymentDate
                ? Carbon::parse($payment->paymentDate)->toDateString()
                : ($payment->created_at ? Carbon::parse($payment->created_at)->toDateString() : $now->toDateString());

            $transactionCode = 'INVT'.$now->format('ymdHis').str_pad((string) $index, 4, '0', STR_PAD_LEFT);
            $index++;

            DB::table('invoice_payment_transaction')->insert([
                'id' => (string) Str::uuid(),
                'code' => $transactionCode,
                'paymentDate' => $paymentDate,
                'customerCode' => $invoice->customerCode,
                'userBankCode' => $payment->userBankCode,
                'amount' => (int) ($payment->amount ?? 0),
                'totalClaim' => 0,
                'description' => $payment->description ?: 'Pembayaran faktur (data lama)',
                'paymentReceipt' => $payment->paymentReceipt,
                'created_at' => $payment->created_at ?: $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            DB::table('invoice_payment')
                ->where('id', $payment->id)
                ->update(['transactionCode' => $transactionCode]);
        }
    }

    public function down(): void
    {
        // Lepaskan semua relasi transaksi pada pembayaran lama
        DB::table('invoice_payment')->whereNotNull('transactionCode')->update([
            'transactionCode' => null,
        ]);

        DB::table('invoice_payment_claim')->delete();
        DB::table('invoice_payment_transaction')->delete();
    }
};
