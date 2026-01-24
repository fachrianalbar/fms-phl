<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogActivity
{
    /**
     * Log activity both for Eloquent Models and array/meta data.
     * If $data is a Model, attach it as performedOn and store attributes.
     * If $data is an array or scalar, store it under 'meta'.
     */
    protected function logActivity($title, $data, $log)
    {
        $activity = activity($title)->causedBy(Auth::user());

        if ($data instanceof Model) {
            $activity = $activity->performedOn($data)
                ->withProperties(['attributes' => $data->getAttributes()]);

            return $activity->log($log);
        }

        // If $data is an array, try to infer a subject model (e.g., Order)
        if (is_array($data)) {
            // Try common keys
            $orderModel = null;

            if (isset($data['orderId'])) {
                $orderModel = \App\Models\Operational\Order::where('id', $data['orderId'])->first();
            } elseif (isset($data['order_id'])) {
                $orderModel = \App\Models\Operational\Order::where('id', $data['order_id'])->first();
            } elseif (isset($data['orderCode'])) {
                $orderModel = \App\Models\Operational\Order::where('code', $data['orderCode'])->first();
            } elseif (isset($data['order_code'])) {
                $orderModel = \App\Models\Operational\Order::where('code', $data['order_code'])->first();
            }

            if ($orderModel) {
                $activity = $activity->performedOn($orderModel)->withProperties(['meta' => $data]);

                return $activity->log($log);
            }

            // Fallback: use the authenticated user as subject to satisfy non-null subject_id
            $activity = $activity->performedOn(Auth::user())->withProperties(['meta' => $data]);

            return $activity->log($log);
        }

        // Default behavior for scalar or unknown types
        $activity = $activity->performedOn(Auth::user())->withProperties(['meta' => $data]);

        return $activity->log($log);
    }
}
