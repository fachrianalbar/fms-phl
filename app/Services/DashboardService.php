<?php

namespace App\Services;

use App\Models\Master\Fleet;
use App\Models\Mutation;
use App\Models\Operational\Order;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\DB;


class DashboardService
{
    use LogActivity;

    protected $fleet;
    protected $order;

    public function __construct(Fleet $fleet, Order $order)
    {
        $this->fleet = $fleet;
        $this->order = $order;
    }

    public function dashboardMaintenance()
    {
        return $this->fleet->select([
            'fleet.code',
            'fleet.plateNumber',
            DB::raw('COUNT(DISTINCT fms_maintenance.code) as total'),
            DB::raw('SUM(fms_item.price * fms_maintenance_detail.qty) as price')
        ])
            ->leftjoin('maintenance', 'maintenance.fleetCode', 'fleet.code')
            ->leftjoin('maintenance_detail',  'maintenance_detail.maintenanceCode', 'maintenance.code')
            ->leftjoin('item', 'item.code', 'maintenance_detail.itemCode')
            ->with(['maintenances'])
            ->orderBy('total', 'DESC')
            ->groupBy(['fleet.code', 'fleet.plateNumber']);
    }

    public function dashboardTruckOrder()
    {
        $latestOrders = DB::table('order as o1')
            ->select([
                'o1.fleetCode',
                'o1.shipmentNumber',
                'o1.status',
                'employee.name as driverName',
                'o1.created_at'
            ])
            ->join('employee', 'employee.code', '=', 'o1.driverCode')
            ->join(DB::raw('(
            SELECT fleetCode, MAX(created_at) as latest 
            FROM fms_order 
            GROUP BY fleetCode
        ) as `o2`'), function ($join) {
                $join->on('o1.fleetCode', '=', DB::raw('o2.fleetCode'))
                    ->on('o1.created_at', '=', DB::raw('o2.latest'));
            })
            ->whereNull('o1.deleted_at');

        return $this->fleet->select([
            'fleet.code',
            'fleet.plateNumber',
            'fleet_type.name as fleetTypeName',
            'latest.shipmentNumber',
            'latest.driverName',
            'latest.status'
        ])
            ->leftJoin('fleet_type', 'fleet_type.code', 'fleet.fleetTypeCode')
            ->leftJoin('order', 'order.fleetCode', 'fleet.code')
            ->leftJoinSub($latestOrders, 'latest', function ($join) {
                $join->on('fleet.code', '=', 'latest.fleetCode');
            })
            ->groupBy(['fleet.code', 'fleet.plateNumber', 'fleet_type.name', 'latest.shipmentNumber', 'latest.driverName', 'latest.status'])
            ->get();
    }
}
