<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // return response()->json([
        //     'error' => 'Unauthorized',
        //     'message' => $request->header('total_kilat_key')
        // ], 401);
        if ($request->header('total-kilat-key') != env('TOTAL_KILAT_API_KEY') || $request->header('total-kilat-key') == null) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Key tidak valid!'
            ], 401);
        }
        return $next($request);
    }
}
