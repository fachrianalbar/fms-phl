<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckMaintenance
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // allow the maintenance page itself so we don't loop
        if ($request->is('maintenance') || $request->is('maintenance/*')) {
            return $next($request);
        }

        // allow access to assets (so css/js/images still load)
        if ($request->is('assets/*') || $request->is('vendor/*')) {
            return $next($request);
        }

        $value = DB::table('settings')->where('object', 'is_maintenance')->value('value');

        if ($value === 'true') {
            $message = 'APLIKASI ANDA TIDAK DAPAT DI AKSES. SILAHKAN HUBUNGI ADMINISTRATOR UNTUK INFORMASI LEBIH LANJUT.';

            return response()->view('maintenance', [
                'message' => $message,
            ], 503);
        }

        return $next($request);
    }
}
