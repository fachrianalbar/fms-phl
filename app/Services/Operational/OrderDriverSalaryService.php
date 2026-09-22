<?php

namespace App\Services\Operational;

use App\Models\Master\Employee;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Models\Operational\OrderDriverSalary;

class OrderDriverSalaryService
{
    /**
     * Sync/upsert order_driver_salary records for a specific order.
     *
     * @param Order|string $orderOrCode
     * @return void
     */
    public static function syncForOrder($orderOrCode)
    {
        $order = is_string($orderOrCode)
            ? Order::where('code', $orderOrCode)->first()
            : $orderOrCode;

        if (! $order) {
            return;
        }

        // Resolve order primary driver ID
        $primaryDriverId = $order->driver?->id;
        if (! $primaryDriverId && $order->driverCode) {
            $primaryDriverId = Employee::where('code', $order->driverCode)->value('id');
        }

        // Only load salary costs; the old implementation loaded every cost and
        // filtered them in PHP before syncing.
        $salaryCosts = OrderCost::where('orderCode', $order->code)
            ->whereHas('costComponent', function ($q) {
                $q->where('type', 'salary')
                    ->orWhere('name', 'like', '%gaji%');
            })
            ->with('costComponent')
            ->get();

        $syncedSalaryRowIds = [];

        foreach ($salaryCosts as $cost) {
            $costComponentId = $cost->costComponent->id;

            // Determine driver for this cost: specific driver on cost, or fallback to order primary driver
            $rowDriverId = null;
            if (! empty($cost->driverCode)) {
                $rowDriverId = Employee::where('code', $cost->driverCode)->value('id');
            }
            if (! $rowDriverId) {
                $rowDriverId = $primaryDriverId;
            }

            if (! $rowDriverId) {
                continue;
            }

            // Find existing record by order_cost_id first, or by order_id + cost_component_id + driver_id
            $existing = OrderDriverSalary::where('order_cost_id', $cost->id)->first();
            if (! $existing) {
                $existing = OrderDriverSalary::where('order_id', $order->id)
                    ->where('cost_component_id', $costComponentId)
                    ->where('driver_id', $rowDriverId)
                    ->whereNull('order_cost_id')
                    ->first();
            }

            if ($existing) {
                $existing->update([
                    'order_cost_id'     => $cost->id,
                    'cost_component_id' => $costComponentId,
                    'driver_id'         => $rowDriverId,
                    'amount'            => $cost->nominal ?? 0,
                ]);
                $syncedSalaryRowIds[] = $existing->id;
            } else {
                $created = OrderDriverSalary::create([
                    'order_id'          => $order->id,
                    'order_cost_id'     => $cost->id,
                    'cost_component_id' => $costComponentId,
                    'driver_id'         => $rowDriverId,
                    'amount'            => $cost->nominal ?? 0,
                    'status'            => '0',
                ]);
                $syncedSalaryRowIds[] = $created->id;
            }
        }

        // Clean up any unprocessed (status = '0') salary components that were removed from the order
        OrderDriverSalary::where('order_id', $order->id)
            ->where('status', '0')
            ->whereNotIn('id', $syncedSalaryRowIds)
            ->delete();
    }
}
