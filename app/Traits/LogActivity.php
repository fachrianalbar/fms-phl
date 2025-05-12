<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait LogActivity
{
    protected function logActivity($title, $data, $log)
    {
        return activity($title)
            ->performedOn($data)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data->getAttributes()])
            ->log($log);
    }
}
