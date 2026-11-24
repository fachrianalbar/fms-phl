<?php

namespace App\Services\Operational;

use App\Models\Master\CostComponent;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCommission;
use App\Models\Operational\OrderCost;

class OrderCommissionService
{
    /**
     * Query scope helper: all cost_component IDs/codes that represent commissions.
     */
    public static function commissionComponentQuery()
    {
        return CostComponent::query()
            ->where(function ($q) {
                $q->where('type', 'commission')
                    ->orWhereRaw("LOWER(name) LIKE '%komisi%'");
            });
    }

    /**
     * Sync/upsert order_commission records for a specific order.
     *
     * @param  Order|string  $orderOrCode
     */
    public static function syncForOrder($orderOrCode): void
    {
        $order = is_string($orderOrCode)
            ? Order::where('code', $orderOrCode)->first()
            : $orderOrCode;

        if (! $order) {
            return;
        }

        $commissionCosts = OrderCost::where('orderCode', $order->code)
            ->where(function ($q) {
                $q->whereHas('costComponent', function ($cc) {
                    $cc->where('type', 'commission')
                        ->orWhereRaw("LOWER(name) LIKE '%komisi%'");
                })->orWhereRaw("LOWER(COALESCE(componentName, '')) LIKE '%komisi%'");
            })
            ->with('costComponent')
            ->get();

        $syncedIds = [];

        foreach ($commissionCosts as $cost) {
            $costComponentId = $cost->costComponent?->id;

            $existing = OrderCommission::withTrashed()
                ->where('order_cost_id', $cost->id)
                ->first();

            if (! $existing && $costComponentId) {
                $existing = OrderCommission::withTrashed()
                    ->where('order_id', $order->id)
                    ->where('cost_component_id', $costComponentId)
                    ->whereNull('order_cost_id')
                    ->first();
            }

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                // Do not overwrite amount if already processed in a slip, only link order_cost_id
                $updateData = [
                    'order_cost_id' => $cost->id,
                    'cost_component_id' => $costComponentId,
                ];
                if ($existing->status === '0') {
                    $updateData['amount'] = $cost->nominal ?? 0;
                }

                $existing->update($updateData);
                $syncedIds[] = $existing->id;
            } else {
                $created = OrderCommission::create([
                    'order_id' => $order->id,
                    'order_cost_id' => $cost->id,
                    'cost_component_id' => $costComponentId,
                    'amount' => $cost->nominal ?? 0,
                    'status' => '0',
                ]);
                $syncedIds[] = $created->id;
            }
        }

        // Remove unprocessed (status = '0') rows whose order_cost was removed from the order
        OrderCommission::where('order_id', $order->id)
            ->where('status', '0')
            ->whereNotIn('id', $syncedIds)
            ->delete();
    }

    /**
     * Sync all orders that have commission costs matching optional date/component/customer filters.
     */
    public static function syncOrdersInRange(
        ?string $startDate = null,
        ?string $endDate = null,
        array $componentCodes = [],
        ?string $customerCode = null
    ): int {
        $commissionCodes = ! empty($componentCodes)
            ? $componentCodes
            : self::commissionComponentQuery()->withTrashed()->pluck('code')->toArray();

        $costOrderCodes = OrderCost::query()
            ->where(function ($q) use ($commissionCodes) {
                if (! empty($commissionCodes)) {
                    $q->whereIn('componentType', $commissionCodes);
                }
                if (empty($commissionCodes)) {
                    $q->orWhereRaw("LOWER(COALESCE(componentName, '')) LIKE '%komisi%'");
                }
            })
            ->select('orderCode');

        $ordersQuery = Order::query()
            ->whereIn('code', $costOrderCodes)
            ->whereNull('deleted_at');

        if ($startDate && $endDate) {
            $ordersQuery->whereBetween('orderDate', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $ordersQuery->whereDate('orderDate', '>=', $startDate);
        } elseif ($endDate) {
            $ordersQuery->whereDate('orderDate', '<=', $endDate);
        }

        if ($customerCode) {
            $ordersQuery->where('customerCode', $customerCode);
        }

        $orders = $ordersQuery->get();
        foreach ($orders as $order) {
            self::syncForOrder($order);
        }

        return $orders->count();
    }
}
