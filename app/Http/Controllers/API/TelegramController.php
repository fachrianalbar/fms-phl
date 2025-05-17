<?php

namespace App\Http\Controllers\API;

use App\Helpers\GenerateCode;
use App\Helpers\SendNotif;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Operational\TelegramUser;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $message = $request->input('message');
        if (!$message) return response()->json(['status' => 'no message']);

        $chatId = $message['chat']['id'];
        $text = $message['text'];

        if (str_starts_with($text, "/start")) {
            $username = $message['chat']['username'] ?? null;
            $firstName = $message['chat']['first_name'] ?? null;
            $lastName = $message['chat']['last_name'] ?? null;

            $telegramUser = TelegramUser::where('chatId', $chatId)->first();

            if ($telegramUser) {
                $telegramUser->update([
                    "chatId" => $chatId,
                    "username" => $username,
                    "firstName" => $firstName,
                    "lastName" => $lastName
                ]);
                SendNotif::sendTelegram($chatId, "Halo $firstName, kamu sudah terdaftar.");
            } else {
                TelegramUser::create([
                    "code" => GenerateCode::generateCode("FTL"),
                    "chatId" => $chatId,
                    "username" => $username,
                    "firstName" => $firstName,
                    "lastName" => $lastName
                ]);
                SendNotif::sendTelegram($chatId, "Halo $firstName! Pendaftaran berhasil. 🎉");
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
