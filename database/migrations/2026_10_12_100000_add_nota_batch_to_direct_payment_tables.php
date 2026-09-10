<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dukungan pembayaran multi-DO (nota) pada menu Pembayaran Langsung:
     * - order_payment.nota_number : nomor nota yang mengelompokkan beberapa DO
     *   milik satu customer (prefix DP, format DP/00001/2026).
     * - order_payment.user_bank_code : rekening perusahaan untuk mencetak nota.
     * - order_payment_history.batch_code : kode batch pembayaran per transaksi,
     *   agar pembatalan pembayaran menghapus mutasi bank yang tepat.
     * - order_payment_batch : header transaksi pembayaran multi-nota (mirror
     *   vendor_payment_batch).
     * - order_payment_nota_sequence : penomoran nota per tahun (mirror
     *   vendor_nota_sequence).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('order_payment', 'nota_number')) {
            Schema::table('order_payment', function (Blueprint $table) {
                $table->string('nota_number')->nullable()->index()->after('status');
            });
        }

        if (! Schema::hasColumn('order_payment', 'user_bank_code')) {
            Schema::table('order_payment', function (Blueprint $table) {
                $table->string('user_bank_code', 50)->nullable()->index()->after('nota_number');
            });
        }

        if (! Schema::hasColumn('order_payment_history', 'batch_code')) {
            Schema::table('order_payment_history', function (Blueprint $table) {
                $table->string('batch_code')->nullable()->index()->after('userBankCode');
            });
        }

        Schema::create('order_payment_batch', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->uuid('request_key')->unique();
            $table->char('payload_hash', 64);
            $table->string('status', 20)->default('active')->index();
            $table->date('payment_date');
            $table->string('user_bank_code', 50)->index();
            $table->unsignedBigInteger('amount');
            $table->unsignedInteger('nota_count');
            $table->unsignedInteger('order_count');
            $table->unsignedInteger('fully_paid_count')->default(0);
            $table->unsignedInteger('partial_count')->default(0);
            $table->string('description')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('payment_date');
        });

        Schema::create('order_payment_nota_sequence', function (Blueprint $table) {
            $table->string('prefix', 10)->primary();
            $table->unsignedInteger('year');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
        });

        // Seed urutan nota DP dari data yang sudah ada (normalnya masih kosong).
        $year = (int) Carbon::now()->format('Y');
        $lastSequence = 0;
        $notas = DB::table('order_payment')
            ->where('nota_number', 'like', 'DP/%/' . $year)
            ->pluck('nota_number');

        foreach ($notas as $nota) {
            $parts = explode('/', (string) $nota);
            if (count($parts) === 3 && $parts[0] === 'DP' && (int) $parts[2] === $year) {
                $lastSequence = max($lastSequence, (int) $parts[1]);
            }
        }

        if (! DB::table('order_payment_nota_sequence')->where('prefix', 'DP')->exists()) {
            DB::table('order_payment_nota_sequence')->insert([
                'prefix' => 'DP',
                'year' => $year,
                'last_sequence' => $lastSequence,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payment_batch');
        Schema::dropIfExists('order_payment_nota_sequence');

        if (Schema::hasColumn('order_payment_history', 'batch_code')) {
            Schema::table('order_payment_history', function (Blueprint $table) {
                $table->dropColumn('batch_code');
            });
        }

        if (Schema::hasColumn('order_payment', 'user_bank_code')) {
            Schema::table('order_payment', function (Blueprint $table) {
                $table->dropColumn('user_bank_code');
            });
        }

        if (Schema::hasColumn('order_payment', 'nota_number')) {
            Schema::table('order_payment', function (Blueprint $table) {
                $table->dropColumn('nota_number');
            });
        }
    }
};
