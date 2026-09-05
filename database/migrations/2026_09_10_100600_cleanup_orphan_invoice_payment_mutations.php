<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Bersihkan mutasi bank ORPHAN dari pembayaran invoice (data lama).
     *
     * Latar belakang:
     * Jalur pembayaran invoice lama mencatat mutasi bank 'In' dengan deskripsi
     * "Invoice Payment {noFaktur} with amount ..." tetapi TIDAK transaksional —
     * saat faktur / pembayaran dihapus, mutasi bank & saldonya tidak pernah
     * di-reverse, sehingga saldo Bank Book ikut mengandung uang tanpa dokumen.
     *
     * Yang dilakukan migration ini:
     * 1. Cari mutasi 'In' aktif ber-deskripsi "Invoice Payment ..." yang TIDAK
     *    punya pembayaran aktif (faktur terhapus / pembayaran terhapus).
     * 2. Soft-delete mutasi orphan tersebut (masih bisa dipulihkan manual).
     * 3. Kurangi LiveMutation.debit rekening terkait sebesar nominal orphan dan
     *    hitung ulang balance — saldo Bank Book kembali konsisten dengan ledger.
     *
     * PENTING:
     * - Generic: mencari orphan berdasarkan LOGIKA, bukan ID — aman dijalankan
     *   di server production yang datanya bisa berbeda dengan local.
     * - Idempotent: hanya memproses mutasi yang belum ter-soft-delete; jika
     *   dijalankan ulang tidak akan menghapus dua kali.
     * - Konservatif: mutasi dianggap orphan HANYA jika tidak ada satu pun
     *   pembayaran aktif dengan nominal sama pada faktur manapun yang cocok.
     *   Nomor faktur bisa DUPLIKAT di beberapa baris invoice (kasus nyata:
     *   INV/PHL/MLB/00018/06/2026 ada 4 baris), sehingga pencarian faktur
     *   memakai SEMUA baris yang cocok — bukan hanya baris pertama.
     * - Setiap mutasi yang dinonaktifkan dicatat di storage/logs/laravel.log
     *   sebagai jejak audit untuk pemulihan manual.
     */
    public function up(): void
    {
        $mutations = DB::table('mutation')
            ->whereNull('deleted_at')
            ->where('type', 'In')
            ->where('description', 'like', 'Invoice Payment %')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $reversedByBank = [];
        $count = 0;
        $totalReversed = 0;

        DB::transaction(function () use ($mutations, &$reversedByBank, &$count, &$totalReversed) {
            foreach ($mutations as $mutation) {
                // Ambil referensi faktur dari deskripsi jalur lama
                if (! preg_match('/^Invoice Payment (\S+) with amount/', $mutation->description, $m)) {
                    Log::warning('[cleanup-orphan-mutation] Format deskripsi tidak dikenali, dilewati: '.$mutation->code.' — '.$mutation->description);

                    continue;
                }

                $invoiceRef = $m[1];

                // Cari pembayaran aktif dengan nominal sama pada faktur manapun
                // yang cocok (nomor faktur ATAU kode) — nomor faktur bisa
                // duplikat antar beberapa baris invoice, jadi cek semuanya.
                $hasActivePayment = DB::table('invoice_payment')
                    ->whereNull('deleted_at')
                    ->where('amount', (int) $mutation->nominal)
                    ->whereIn('invoiceCode', function ($q) use ($invoiceRef) {
                        $q->select('code')->from('invoice')
                            ->whereNull('deleted_at')
                            ->where(function ($q2) use ($invoiceRef) {
                                $q2->where('invoiceNumber', $invoiceRef)->orWhere('code', $invoiceRef);
                            });
                    })
                    ->exists();

                if ($hasActivePayment) {
                    // Mutasi masih didukung pembayaran aktif — bukan orphan
                    continue;
                }

                // Soft-delete mutasi orphan
                DB::table('mutation')
                    ->where('id', $mutation->id)
                    ->update(['deleted_at' => Carbon::now()]);

                $reversedByBank[$mutation->userBankCode] = ($reversedByBank[$mutation->userBankCode] ?? 0) + (int) $mutation->nominal;
                $count++;
                $totalReversed += (int) $mutation->nominal;

                Log::info('[cleanup-orphan-mutation] Mutasi orphan dinonaktifkan: '.$mutation->code.' | bank '.$mutation->userBankCode.' | Rp '.number_format($mutation->nominal, 0, ',', '.').' | '.$mutation->description);
            }

            // Koreksi saldo (LiveMutation) per rekening: kurangi debit, hitung ulang balance
            foreach ($reversedByBank as $bankCode => $amount) {
                DB::table('live_mutation')
                    ->where('userBankCode', $bankCode)
                    ->whereNull('deleted_at')
                    ->update([
                        'debit' => DB::raw('GREATEST(debit - '.$amount.', 0)'),
                    ]);

                DB::table('live_mutation')
                    ->where('userBankCode', $bankCode)
                    ->whereNull('deleted_at')
                    ->update([
                        'balance' => DB::raw('debit - credit'),
                    ]);
            }
        });

        echo '[cleanup-orphan-mutation] Selesai: '.$count.' mutasi orphan dinonaktifkan, total Rp '.number_format($totalReversed, 0, ',', '.')."\n";
        if (! empty($reversedByBank)) {
            foreach ($reversedByBank as $bankCode => $amount) {
                echo '[cleanup-orphan-mutation]   '.$bankCode.' dikurangi Rp '.number_format($amount, 0, ',', '.')."\n";
            }
        }
    }

    public function down(): void
    {
        // Pemulihan tidak dilakukan otomatis.
        // Baris yang dinonaktifkan bersifat soft-delete (deleted_at terisi) dan
        // seluruh detailnya tercatat di storage/logs/laravel.log dengan prefix
        // [cleanup-orphan-mutation] — bisa dipulihkan manual bila diperlukan.
    }
};
