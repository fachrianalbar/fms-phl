<?php

use App\Helpers\GenerateCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        // 0a. Kolom persentase PPN/PPH pada tagihan order.
        //     Mode nominal tetap tersimpan di kolom ppn/pph yang sudah ada,
        //     sedangkan ppn_percent/pph_percent terisi hanya saat pajak dihitung
        //     dari persentase (termasuk persentase default customer).
        if (! Schema::hasColumn('order_payment', 'ppn_percent')) {
            Schema::table('order_payment', function (Blueprint $table) {
                $table->decimal('ppn_percent', 5, 2)->nullable()->after('ppn');
                $table->decimal('pph_percent', 5, 2)->nullable()->after('pph');
            });
        }

        // 0b. Snapshot pajak per transaksi pada riwayat pembayaran,
        //     agar setiap baris riwayat mencatat PPN/PPH yang dipakai saat itu.
        if (! Schema::hasColumn('order_payment_history', 'ppn')) {
            Schema::table('order_payment_history', function (Blueprint $table) {
                $table->integer('ppn')->nullable()->after('paymentType');
                $table->integer('pph')->nullable()->after('ppn');
                $table->decimal('ppn_percent', 5, 2)->nullable()->after('pph');
                $table->decimal('pph_percent', 5, 2)->nullable()->after('ppn_percent');
            });
        }

        // 1. Menu baru: Pembayaran Langsung (menu utama tersendiri,
        //    sebelumnya berada di bawah Finance → Order Payment)
        $exists = DB::table('menu')->where('code', 'DIRECT_PAYMENT')->exists();
        if (! $exists) {
            DB::table('menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => 'DIRECT_PAYMENT',
                'name' => 'Direct Payment',
                'nama' => 'Pembayaran Langsung',
                'parentCode' => '0',
                'url' => 'direct-payment',
                'icon' => 'credit-card',
                'sort' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. Pindahkan akses role dari menu lama Finance → Order Payment
        //    ke menu baru Pembayaran Langsung
        $roleCodes = DB::table('role_menu')
            ->where('menuCode', 'ORDER_PAYMENT')
            ->pluck('roleCode')
            ->unique()
            ->values()
            ->all();

        if (! in_array('SPRADMIN', $roleCodes)) {
            $roleCodes[] = 'SPRADMIN';
        }

        foreach ($roleCodes as $roleCode) {
            $hasAccess = DB::table('role_menu')
                ->where('roleCode', $roleCode)
                ->where('menuCode', 'DIRECT_PAYMENT')
                ->exists();

            if (! $hasAccess) {
                DB::table('role_menu')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => GenerateCode::generateCode('TRL', true),
                    'roleCode' => $roleCode,
                    'menuCode' => 'DIRECT_PAYMENT',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 3. Hapus menu lama Finance → Order Payment
        //    (fungsinya berpindah ke menu Pembayaran Langsung,
        //    URL lama dialihkan lewat redirect di routes/finance.php)
        DB::table('role_menu')->where('menuCode', 'ORDER_PAYMENT')->delete();
        DB::table('menu')->where('code', 'ORDER_PAYMENT')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = Carbon::now();

        // 1. Kembalikan menu lama Finance → Order Payment
        $oldExists = DB::table('menu')->where('code', 'ORDER_PAYMENT')->exists();
        if (! $oldExists) {
            DB::table('menu')->insert([
                'id' => (string) Str::uuid(),
                'code' => 'ORDER_PAYMENT',
                'name' => 'Order Payment',
                'nama' => 'Pembayaran Pesanan',
                'parentCode' => 'FINANCE',
                'url' => 'finance/order-payment',
                'icon' => null,
                'sort' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. Kembalikan akses role ke menu lama
        $roleCodes = DB::table('role_menu')
            ->where('menuCode', 'DIRECT_PAYMENT')
            ->pluck('roleCode')
            ->unique()
            ->values()
            ->all();

        if (! in_array('SPRADMIN', $roleCodes)) {
            $roleCodes[] = 'SPRADMIN';
        }

        foreach ($roleCodes as $roleCode) {
            $hasAccess = DB::table('role_menu')
                ->where('roleCode', $roleCode)
                ->where('menuCode', 'ORDER_PAYMENT')
                ->exists();

            if (! $hasAccess) {
                DB::table('role_menu')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => GenerateCode::generateCode('TRL', true),
                    'roleCode' => $roleCode,
                    'menuCode' => 'ORDER_PAYMENT',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 3. Hapus menu baru Pembayaran Langsung
        DB::table('role_menu')->where('menuCode', 'DIRECT_PAYMENT')->delete();
        DB::table('menu')->where('code', 'DIRECT_PAYMENT')->delete();

        // 4. Hapus kolom persentase pajak
        if (Schema::hasColumn('order_payment', 'ppn_percent')) {
            Schema::table('order_payment', function (Blueprint $table) {
                $table->dropColumn(['ppn_percent', 'pph_percent']);
            });
        }

        if (Schema::hasColumn('order_payment_history', 'ppn')) {
            Schema::table('order_payment_history', function (Blueprint $table) {
                $table->dropColumn(['ppn', 'pph', 'ppn_percent', 'pph_percent']);
            });
        }
    }
};
