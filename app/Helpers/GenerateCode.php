<?php

namespace App\Helpers;

use Carbon\Carbon;

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

    public static function generateCodeAscDate($prefix, $modelClass, $dateColumn = 'date', $date = null, $codeColumn = 'code')
    {
        $carbonDate = $date ? Carbon::parse($date) : Carbon::now();
        $codeDate = $carbonDate->format('ymd');

        $count = $modelClass::whereDate($dateColumn, $carbonDate->toDateString())->count();

        do {
            $increment = str_pad($count + 1, 5, '0', STR_PAD_LEFT);
            $code = "{$prefix}-{$codeDate}{$increment}";

            // Cek apakah kode sudah ada di database
            $exists = $modelClass::where($codeColumn, $code)->exists();

            $count++;
        } while ($exists);

        return $code;
    }
}
