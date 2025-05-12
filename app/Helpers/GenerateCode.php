<?php

namespace App\Helpers;

use Carbon\Carbon;

class GenerateCode
{
    public static function generateCode(string $name, $mil = false)
    {
        if ($mil) {
            return $name . substr(str_replace('.', '', Carbon::now()->format('ymdHis.u')), 0, -3);
        } else {
            return $name . Carbon::now()->format('ymdHis');
        }
    }
}
