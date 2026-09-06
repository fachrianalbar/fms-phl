<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_nota_sequence', function (Blueprint $table) {
            $table->string('prefix', 10)->primary();
            $table->unsignedInteger('year');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
        });

        $year = (int) Carbon::now()->format('Y');
        foreach (['P', 'PHL', 'WTMS'] as $prefix) {
            $lastSequence = 0;
            $notas = DB::table('vendor_payment')
                ->where('nota_number', 'like', $prefix . '/%/' . $year)
                ->pluck('nota_number');

            foreach ($notas as $nota) {
                $parts = explode('/', (string) $nota);
                if (count($parts) === 3 && $parts[0] === $prefix && (int) $parts[2] === $year) {
                    $lastSequence = max($lastSequence, (int) $parts[1]);
                }
            }

            DB::table('vendor_nota_sequence')->insert([
                'prefix' => $prefix,
                'year' => $year,
                'last_sequence' => $lastSequence,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_nota_sequence');
    }
};
