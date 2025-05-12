<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;



class SendNotif
{
    public static function EmailTruckOrderMonitoring($data)
    {
        Mail::send('operational.monitoring-order.notif.email', $data, function ($message) use ($data) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $message->to($data["email"])->subject('⚠️ Peringatan! Truk Keluar dari Jalur');
        });
    }

    public static function sendWa(string $telephone, string $message)
    {
        $userkey = env('WA_USER_KEY');
        $passkey = env('WA_PASS_KEY');
        $url = env('WA_URL');

        Http::withOptions([
            'verify' => false,

        ])->post($url, [
            'userkey' => $userkey,
            'passkey' => $passkey,
            'to' => $telephone,
            'message' => $message,
        ]);
    }

    public static function sendTelegram(string $chat_id, string $message)
    {
        $url = env("TELE_BOT_URL") . env("TELE_BOT_KEY") . '/sendMessage';
        Http::post($url, [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }
}
