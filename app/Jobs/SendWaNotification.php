<?php

namespace App\Jobs;

use App\Helpers\SendNotif;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWaNotification implements ShouldQueue
{
    use Queueable;

    public $telephone, $message;

    /**
     * Create a new job instance.
     */
    public function __construct($telephone, $message)
    {
        $this->telephone = $telephone;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SendNotif::sendWa($this->telephone, $this->message);
    }
}
