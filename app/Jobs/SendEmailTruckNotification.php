<?php

namespace App\Jobs;

use App\Helpers\SendNotif;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailTruckNotification implements ShouldQueue
{
    use Queueable;

    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SendNotif::EmailTruckOrderMonitoring($this->data);
    }
}
