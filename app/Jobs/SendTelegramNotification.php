<?php

namespace App\Jobs;

use App\Helpers\SendNotif;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramNotification implements ShouldQueue
{
    use Queueable;

    public $chatId;

    public $message;

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
