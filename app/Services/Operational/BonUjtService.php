<?php

namespace App\Services\Operational;

use App\Helpers\GenerateCode;
use App\Models\Operational\BonUjt;
use App\Models\Operational\BonUjtDetail;
use App\Models\Operational\Order;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\Auth;

class BonUjtService
{
    use LogActivity;

    protected $service;

    protected $order;

    protected $bonUjtDetail;

    public function __construct(BonUjt $bonUjt, Order $order, BonUjtDetail $bonUjtDetail)
    {
        $this->service = $bonUjt;
        $this->order = $order;
        $this->bonUjtDetail = $bonUjtDetail;
    }

    public function findAll()
    {
        return $this->service->with([
            'fleetType',
        ])->get();
    }

    public function datatable()
    {
        return $this->service->with([
            'fleetType',
        ]);
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function getOrder()
    {
        return $this->order->where('bonUjt', 0)->with([
            // 'fleetDriver.fleet',
            'fleet',
            'fleet.type',
            // 'fleetDriver.employee',
            'driver',
            'customer',
            'route.originLocation',
            'route.destinationLocation',
            'route.originLocation',
            'orderType',
            'route.routeDetail',
        ])->orderBy('created_at', 'desc');
    }

    public function getOrderDetail($id)
    {
        $data = $this->getById($id);
        $orderCodeArr = $this->bonUjtDetail->where('bonUJtCode', $data->code)->pluck('orderCode');

        return $this->order->whereIn('code', $orderCodeArr)->with([
            'fleetDriver.fleet',
            // 'fleetDriver.employee',
            'fleet',
            'driver',
            'customer',
            'route.originLocation',
            'route.destinationLocation',
            'route.originLocation',
            'orderType',
            'route.routeDetail',
        ])->orderBy('created_at', 'desc')->get();
    }

    public function store($request, $title, $selectedOrders)
    {
        $data = $this->service->create([
            'code' => $request->code,
            'bon' => $request->bon,
            'date' => $request->date,
            'time' => $request->time,
            'submitDate' => $request->submitDate,
            // 'handover' => $request->handover,
            'note' => (int) $request->note,
            'createdBy' => Auth::user()->id,
            'fleetTypeCode' => $request->fleetTypeCode,
        ]);

        if (isset($request->order)) {
            foreach ($selectedOrders as $item) {
                $detail = $this->bonUjtDetail->create([
                    'code' => GenerateCode::generateCode('TBUD', true),
                    'bonUjtCode' => $request->code,
                    'orderCode' => $item,
                ]);

                $this->order->where('code', $item)->update([
                    'bonUjt' => 1,
                ]);

                $this->logActivity('Bon Ujt Detail', $detail, 'Create');
            }
        }

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'bon' => $request->bon,
            'date' => $request->date,
            'time' => $request->time,
            'submitDate' => $request->submitDate,
            // 'handover' => $request->handover,
            'note' => (int) $request->note,
            'fleetTypeCode' => $request->fleetTypeCode,

        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        foreach ($data->details as $item) {
            $this->order->where('code', $item->orderCode)->update([
                'bonUjt' => 0,
            ]);

            $this->bonUjtDetail->where('id', $item->id)->delete();

            $this->logActivity('Bon Ujt Detail', $item, 'Delete');
        }

        $this->service->where('id', $id)->delete();
    }

    public function storeBonUjtDetail($request, $id, $selectedOrders)
    {
        $bonUjt = $this->getById($id);
        if (isset($request->order)) {

            foreach ($selectedOrders as $item) {
                $detail = $this->bonUjtDetail->create([
                    'code' => GenerateCode::generateCode('TBUD'),
                    'bonUjtCode' => $bonUjt->code,
                    'orderCode' => $item,
                ]);

                $this->order->where('code', $item)->update([
                    'bonUjt' => 1,
                ]);

                $this->logActivity('Bon Ujt Detail', $detail, 'Create');
            }
        }
    }

    public function destroyBonUjtDetail($id)
    {
        $order = $this->order->where('id', $id)->first();

        $this->order->where('id', $id)->update([
            'bonUjt' => 0,
        ]);

        $data = $this->bonUjtDetail->where('orderCode', $order->code)->first();

        $this->logActivity('Bon Ujt Detail', $data, 'Delete');

        $this->bonUjtDetail->where('orderCode', $order->code)->delete();
    }
}
