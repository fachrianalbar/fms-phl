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

        // Always resolve driver ID from order
        $driverId = $order->driver?->id;
        if (! $driverId && $order->driverCode) {
            $driverId = Employee::where('code', $order->driverCode)->value('id');
        }

        // If driver does not exist, remove any unprocessed (status = '0') records and exit
        if (! $driverId) {
            OrderDriverSalary::where('order_id', $order->id)
                ->where('status', '0')
                ->delete();

            return;
        }

        // Fetch all order costs with their cost components
        $orderCosts = OrderCost::where('orderCode', $order->code)
            ->with('costComponent')
            ->get();

        // Filter salary costs (type = 'salary' or name contains 'gaji')
        $salaryCosts = $orderCosts->filter(function ($cost) {
            return $cost->costComponent && (
                $cost->costComponent->type === 'salary' ||
                stripos($cost->costComponent->name, 'gaji') !== false
            );
        });

        $activeCostComponentIds = [];

        foreach ($salaryCosts as $cost) {
            $costComponentId = $cost->costComponent->id;
            $activeCostComponentIds[] = $costComponentId;

            // Upsert into order_driver_salary
            OrderDriverSalary::updateOrCreate(
                [
                    'order_id'          => $order->id,
                    'cost_component_id' => $costComponentId,
                ],
                [
                    'driver_id' => $driverId,
                    'amount'    => $cost->nominal ?? 0,
                ]
            );
        }

        // Clean up any unprocessed (status = '0') salary components that were removed from the order
        OrderDriverSalary::where('order_id', $order->id)
            ->where('status', '0')
            ->whereNotIn('cost_component_id', $activeCostComponentIds)
            ->delete();
    }
}
