<?php

namespace App\Jobs;

use App\Helpers\SendNotif;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramNotification implements ShouldQueue
{
    use Queueable;

    public $chatId, $message;

    /**
     * Create a new job instance.
     */
    public function __construct($chatId, $message)
    {
        $this->chatId = $chatId;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SendNotif::sendTelegram($this->chatId, $this->message);
    }
}
