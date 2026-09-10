<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom tipe rekening (Internal/External).
     *
     * user_bank.rekening_type menyimpan kategori kepemilikan rekening:
     * - 'internal' : rekening milik sendiri / antar entitas internal
     * - 'external' : rekening pihak ketiga / eksternal
     */
    public function up()
    {
        if (! Schema::hasColumn('user_bank', 'rekening_type')) {
            Schema::table('user_bank', function (Blueprint $table) {
                $table->string('rekening_type', 20)->default('external')->after('type');
            });
        }

        // Isi semua data lama dengan nilai default 'external'
        \Illuminate\Support\Facades\DB::table('user_bank')
            ->whereNull('rekening_type')
            ->orWhere('rekening_type', '')
            ->update(['rekening_type' => 'external']);
    }

    public function down()
    {
        if (Schema::hasColumn('user_bank', 'rekening_type')) {
            Schema::table('user_bank', function (Blueprint $table) {
                $table->dropColumn('rekening_type');
            });
        }
    }
};
