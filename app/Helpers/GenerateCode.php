<?php

namespace App\Helpers;

use App\Services\UniqueCodeService;
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
