<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;


class GetTokenHelper
{

    public static function fetchToken()
    {
        $response = Http::get(env('API_TOTAL_KILAT') . 'token', [
            'grant_type' => 'totalkilatgps',
            'account_name' => 'pushdata',
            'account_password' => 'password',
        ]);

        $data = $response->json();

        Cache::put(
            'access_token',
            $data['access_token'],
            now()->addHours(1)
        );
        return $data['access_token'];
    }
    public static function getToken()
    {
        $token = Cache::get('access_token');

        if (!$token) {
            $token = self::fetchToken();
        }

        return $token;
    }

    public static function checkToken($data)
    {
        if ($data['errcode'] === 30001) {
            return null;
        }

        return true;
    }
}
