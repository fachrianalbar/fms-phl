<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    protected $service;

    public function __construct(ActivityLog $activityLog)
    {
        $this->service = $activityLog;
    }

    public function findAll()
    {
        return $this->service->select('activity_log.*')->with(['user', 'user.role'])->latest();
    }

    public function getLogName()
    {
        return $this->service->select('log_name')->distinct()->pluck('log_name')->toArray();
    }
}
