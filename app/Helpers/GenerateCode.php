<?php

namespace App\Helpers;

use App\Services\UniqueCodeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateCode
{
    public static function generateCode(string $name, $mil = false)
    {
        if ($mil) {
            return $name.substr(str_replace('.', '', Carbon::now()->format('ymdHis.u')), 0, -3);
        } else {
            return $name.Carbon::now()->format('ymdHis');
        }
    }

    /**
     * Generate kode dengan presisi mikrodetik dan pastikan unik di tabel
     * (aman untuk pemanggilan berulang dalam satu loop / request bersamaan).
     *
     * Pengecekan dilakukan terhadap seluruh baris (termasuk soft-deleted)
     * karena unique index pada kolom code berlaku untuk semua baris.
     */
    public static function generateUniqueCode(string $name, string $table, string $column = 'code'): string
    {
        do {
            $code = self::generateCode($name, true);
        } while (DB::table($table)->where($column, $code)->exists());

        return $code;
    }

    public static function generateCodeAscDate($prefix, $modelClass, $dateColumn = 'date', $date = null, $codeColumn = 'code')
    {
        $carbonDate = $date ? Carbon::parse($date) : Carbon::now();
        $codeDate = $carbonDate->format('ymd');

        $base = "{$prefix}-{$codeDate}";
        $requestedCode = "{$base}00001";

        return app(UniqueCodeService::class)->resolve(
            model: $modelClass,
            field: $codeColumn,
            requestedCode: $requestedCode,
            prefix: $base,
            digits: 5,
        )->resolvedCode;
    }
}
