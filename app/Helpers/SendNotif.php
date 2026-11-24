<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class SendNotif
{
    public static function sendWa(string $telephone, string $message)
    {
        $userkey = config('services.whatsapp.user_key');
        $passkey = config('services.whatsapp.pass_key');
        $url = config('services.whatsapp.url');
        $verifySsl = filter_var(config('services.whatsapp.verify_ssl', true), FILTER_VALIDATE_BOOLEAN);

        Http::withOptions([
            'verify' => $verifySsl,
        ])->post($url, [
            'userkey' => $userkey,
            'passkey' => $passkey,
            'to' => $telephone,
            'message' => $message,
        ]);
    }
}
