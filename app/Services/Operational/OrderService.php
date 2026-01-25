<?php

namespace App\Services\Operational;

use App\Helpers\GenerateCode;
use App\Models\Data\Route;
use App\Models\Master\Customer;
use App\Models\Master\Fleet;
use App\Models\Operational\CustomerDetailOrder;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Models\Operational\OrderMaterial;
use App\Traits\LogActivity;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class OrderService
{
    use LogActivity;

    protected $service;

    protected $customerDetailOrder;

    protected $orderCost;

    protected $route;

    protected $customer;

    protected $fleet;

    protected $orderMaterial;

    public function __construct(Order $order, CustomerDetailOrder $customerDetailOrder, OrderCost $orderCost, Route $route, Customer $customer, Fleet $fleet, OrderMaterial $orderMaterial)
    {
        $this->service = $order;
        $this->customerDetailOrder = $customerDetailOrder;
        $this->orderCost = $orderCost;
        $this->route = $route;
        $this->customer = $customer;
        $this->fleet = $fleet;
        $this->orderMaterial = $orderMaterial;
    }

    public function findAll()
    {
        return $this->service->with([
            'driver',
            'customer',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type',
            'unit',
        ])->orderBy('created_at', 'desc')->get();
    }

    public function datatable()
    {
        return $this->service->with([
            'fleetDriver.fleet',
            'driver',
            'customer',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.company',
            'fleet.type',
            'unit',
            'orderStatus',
        ])->orderBy('orderDate', 'asc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function finishOrder($id)
    {
        $this->service->where('id', $id)->update([
            'status' => 3,
        ]);
    }

    public function store($request, $title)
    {
        $data = $this->service->create(
            array_merge(['code' => $request->code, 'shipmentNumber' => $request->shipmentNumber], $this->buildOrderData($request))
        );

        // Cek apakah fleet adalah external
        $fleet = $this->fleet->where('code', $request->fleetCode)->first();
        $isExternalFleet = ($fleet && $fleet->company && strtolower($fleet->company->type) === 'external');

        // Jika external fleet, hapus semua cost (jika ada data lama yang terlanjur)
        if ($isExternalFleet) {
            OrderCost::where('orderCode', $data->code)->delete();
            logger()->info('OrderCost cleared for external fleet', ['order' => $data->code]);
        } else {
            // Jika bukan fleet external, copy cost dari route_detail dengan is_route = 1
            if (isset($request->routeData)) {
                $route = $this->route->where('code', $request->routeData)
                    ->with('routeDetail')
                    ->first();

                if ($route && $route->routeDetail) {
                    foreach ($route->routeDetail as $detail) {
                        $this->orderCost->create([
                            'code' => GenerateCode::generateCode('TOC', true),
                            'componentType' => $detail->componentCode,
                            'orderCode' => $data->code,
                            'nominal' => $detail->amount,
                            'description' => '',
                            'type' => null,
                            'is_route' => 1, // Dari route_detail
                        ]);
                    }
                }
            }
        }

        // Jangan panggil storeOrderCost() saat create - komponen route sudah auto-copy
        // User hanya bisa tambah komponen custom SETELAH order dibuat via separate form

        if (isset($request->customerDetailCode)) {
            $this->storeCustomerDetailOrder($request);
        }

        if (isset($request->materialCode)) {
            $this->storeOrderMaterial($request);
        }

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {

        $data = $this->getById($id);
        $this->logActivity($title, $this->getById($id), 'Before Update');

        // Prepare update data and sanitize numeric fields (pass isUpdate = true)
        $updateData = array_merge(['shipmentNumber' => $request->shipmentNumber], $this->buildOrderData($request, true));

        // Compute route's expected amount based on selected route and qty
        $selectedRoute = $this->route->where('code', $request->routeData)->first();
        $computedRouteAmount = null;
        if ($selectedRoute) {
            $computedRouteAmount = (int) ($selectedRoute->price * $request->qty);
        }

        // Get submitted (sanitized) routeAmount from update data
        $submittedRouteAmount = isset($updateData['routeAmount']) ? (int) $updateData['routeAmount'] : null;

        // Perform update
        $this->service->where('id', $id)->update($updateData);

        // If submitted route amount differs from computed route price, record it
        if ($computedRouteAmount !== null && $submittedRouteAmount !== null && $submittedRouteAmount !== $computedRouteAmount) {
            $message = 'Route price diperbarui dari ' . number_format($computedRouteAmount, 0, ',', '.') . ' menjadi ' . number_format($submittedRouteAmount, 0, ',', '.');
            // Flash message (controller will show this on redirect)
            try {
                session()->flash('info', $message);
            } catch (\Exception $e) {
                // ignore if session not available in this context
            }

            // Log activity for audit
            $this->logActivity('Route Price Changed', ['orderId' => $data->id, 'from' => $computedRouteAmount, 'to' => $submittedRouteAmount], 'Update');
        }

        // Refresh $data setelah update agar relasi tetap konsisten
        $data = $this->getById($id);

        // Cek apakah fleet adalah external (gunakan nilai tersimpan di order untuk akurat)
        $fleet = $this->fleet->where('code', $data->fleetCode)->first();
        $isExternalFleet = ($fleet && $fleet->company && strtolower($fleet->company->type) === 'external');

        // Jika fleet external, hapus semua cost (tidak boleh ada cost untuk external fleet)
        // Ini juga mencakup cleanup jika sebelumnya ada data lama
        if ($isExternalFleet) {
            // Hapus semua cost menggunakan raw query agar pasti terhapus
            OrderCost::where('orderCode', $data->code)->delete();
            logger()->info('OrderCost cleared for external fleet on update', ['order' => $data->code]);
            // Jangan panggil storeOrderCost untuk fleet external
        } else {
            // Jika bukan external fleet: update cost dengan logic berikut
            // 1. Delete cost lama dari route detail (is_route = 1)
            // 2. Insert cost baru dari route detail terpilih
            // 3. Preserve cost custom user (is_route = 0)

            // Delete hanya cost dengan is_route = 1 (dari route_detail lama)
            // Preserve cost dengan is_route = 0 (custom/tambahan user)
            OrderCost::where('orderCode', $data->code)->where('is_route', 1)->delete();

            // Get the current route data (from request routeData)
            $currentRoute = $this->route->where('code', $request->routeData)
                ->with('routeDetail')
                ->first();

            // Generate new order costs from the route details dengan is_route = 1
            if ($currentRoute && $currentRoute->routeDetail) {
                foreach ($currentRoute->routeDetail as $detail) {
                    $orderCost = $this->orderCost->create([
                        'code' => GenerateCode::generateCode('TOC', true),
                        'componentType' => $detail->componentCode,
                        'orderCode' => $data->code,
                        'nominal' => $detail->amount,
                        'description' => '',
                        'type' => null,
                        'is_route' => 1, // Dari route_detail
                    ]);

                    $this->logActivity('Order Cost', $orderCost, 'Create');
                }
            }

            // Store komponen custom yang user input di form (is_route = 0)
            if (isset($request->nominal)) {
                $this->storeOrderCost($request);
            }
        }

        if (isset($request->customerDetailCode)) {
            $this->customerDetailOrder->where('orderCode', $data->code)->delete();
            $this->storeCustomerDetailOrder($request);
        }

        if (isset($request->materialCode)) {
            $data->orderMaterial()->delete();
            $this->storeOrderMaterial($request);
        }

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        $this->customerDetailOrder->where('orderCode', $data->code)->delete();

        $this->orderCost->where('orderCode', $data->code)->delete();

        $this->orderMaterial->where('orderCode', $data->code)->delete();

        $this->service->where('id', $id)->update([
            'code' => $data->code . '-del-' . Str::random(3),
            'shipmentNumber' => $data->shipmentNumber . '-del-' . Str::random(3),
        ]);

        $this->service->where('id', $id)->delete();
    }

    public function storeOrderTax($selectedOrders)
    {
        $this->service->whereIn('code', $selectedOrders)->update(['is_order_tax' => 1]);
    }

    public function getCustomerDetailOrder($orderCode)
    {
        return $this->customerDetailOrder->where('orderCode', $orderCode)->with(['customerDetail'])->get();
    }

    private function storeOrderCost($request)
    {
        $filtered = Arr::only($request->all(), ['componentName', 'description', 'nominal', 'type', 'is_route']);

        // Guard: jika tidak ada nominal yang dikirim, skip
        if (empty($filtered['nominal'])) {
            return;
        }

        for ($i = 0; $i < count($filtered['nominal']); $i++) {

            // Skip jika adalah komponen dari route (is_route = 1)
            if ((isset($filtered['is_route'][$i]) ? $filtered['is_route'][$i] : 0) == 1) {
                continue;
            }

            // Skip jika tipe adalah 'Ditagihkan'
            if ((isset($filtered['type'][$i]) ? $filtered['type'][$i] : '') == 'Ditagihkan') {
                continue;
            }

            // Guard: skip jika tidak ada nominal atau componentName untuk row ini
            if (! isset($filtered['nominal'][$i]) || ! isset($filtered['componentName'][$i])) {
                continue;
            }

            // Skip jika komponen sudah ada di database (untuk is_route = 0 yang lama)
            $exists = $this->orderCost
                ->where('orderCode', $request->code)
                ->where('componentType', $filtered['componentName'][$i])
                ->where('is_route', 0)
                ->exists();

            if ($exists) {
                continue; // Jangan insert, sudah ada
            }

            $orderCost = $this->orderCost->create([
                'code' => GenerateCode::generateCode('TOC', true),
                'componentType' => $filtered['componentName'][$i],
                'orderCode' => $request->code,
                'nominal' => (int) str_replace('.', '', $filtered['nominal'][$i]),
                'description' => $filtered['description'][$i] ?? '',
                'type' => isset($request->not_return_do) ? 'On Charge' : null,
                'is_route' => 0,
            ]);

            $this->logActivity('Order Cost', $orderCost, 'Create');
        }
    }

    private function storeOrderCostOnCharge($request)
    {
        $filtered = Arr::only($request->all(), ['componentName', 'description', 'nominal', 'type', 'is_route']);

        // Guard: jika tidak ada nominal yang dikirim, skip
        if (empty($filtered['nominal'])) {
            return;
        }

        for ($i = 0; $i < count($filtered['nominal']); $i++) {

            // Skip jika adalah komponen dari route (is_route = 1)
            if ((isset($filtered['is_route'][$i]) ? $filtered['is_route'][$i] : 0) == 1) {
                continue;
            }

            // Skip jika tipe adalah 'Tidak Ditagihkan'
            if ((isset($filtered['type'][$i]) ? $filtered['type'][$i] : '') == 'Tidak Ditagihkan') {
                continue;
            }

            // Guard: skip jika tidak ada nominal atau componentName untuk row ini
            if (! isset($filtered['nominal'][$i]) || ! isset($filtered['componentName'][$i])) {
                continue;
            }

            // Skip jika komponen sudah ada di database (untuk is_route = 0 yang lama)
            $exists = $this->orderCost
                ->where('orderCode', $request->code)
                ->where('componentType', $filtered['componentName'][$i])
                ->where('is_route', 0)
                ->exists();

            if ($exists) {
                continue; // Jangan insert, sudah ada
            }

            $orderCost = $this->orderCost->create([
                'code' => GenerateCode::generateCode('TOC', true),
                'componentType' => $filtered['componentName'][$i],
                'orderCode' => $request->code,
                'nominal' => (int) str_replace('.', '', $filtered['nominal'][$i]),
                'description' => $filtered['description'][$i] ?? '',
                'type' => isset($request->not_return_do) ? 'On Charge' : null,
                'is_route' => 0,
            ]);

            $this->logActivity('Order Cost On Charge', $orderCost, 'Create');
        }
    }

    private function storeOrderMaterial($request)
    {
        $filtered = Arr::only($request->all(), ['materialCode', 'unitCode', 'materialQty', 'unitCode2', 'materialQty2']);

        for ($i = 0; $i < count($request->materialCode); $i++) {

            $orderMaterial = $this->orderMaterial->create([
                'code' => GenerateCode::generateCode('FOM', true),
                'orderCode' => $request->code,
                'materialCode' => $filtered['materialCode'][$i] ?? null,
                'unitCode' => $filtered['unitCode'][$i] ?? null,
                'materialQty' => (int) $filtered['materialQty'][$i] ?? null,
                'unitCode2' => $filtered['unitCode2'][$i] ?? null,
                'materialQty2' => (int) $filtered['materialQty2'][$i] ?? null,
            ]);

            $this->logActivity('Order Material', $orderMaterial, 'Create');
        }
    }

    private function storeCustomerDetailOrder($request)
    {
        $filtered = Arr::only($request->all(), ['customerDetailCode', 'value']);

        for ($i = 0; $i < count($request->customerDetailCode); $i++) {

            $customerDetailOrder = $this->customerDetailOrder->create([
                'code' => GenerateCode::generateCode('FCDO', true),
                'customerDetailCode' => $filtered['customerDetailCode'][$i],
                'value' => $filtered['value'][$i],
                'orderCode' => $request->code,
            ]);

            $this->logActivity('Customer Detail Order', $customerDetailOrder, 'Create');
        }
    }

    private function buildOrderData($request, $isUpdate = false)
    {
        $route = $this->route->where('code', $request->routeData)->first();

        // Recalculate berdasarkan route, bukan dari user input
        $routeAmount = (int) ($route->price * $request->qty);
        $vendorPrice = (int) ($request->qty * $route->vendorPrice);
        $personalVendorPrice = (int) ($route->personalVendorPrice * $request->qty);

        $data = [
            'orderDate' => $request->orderDate,
            'notes' => $request->notes,
            'sto' => $request->sto,
            'salesOrder' => $request->salesOrder,
            'fleetCode' => $request->fleetCode,
            'driverCode' => $request->driverCode,
            'routeCode' => $request->routeData,
            'qty' => $request->qty,
            'orderTypeCode' => $request->orderTypeCode,
            'routeAmount' => $routeAmount,
            'vendorPrice' => $vendorPrice,
            'personalVendorPrice' => $personalVendorPrice,
            'customerCode' => $request->customerCode,
        ];

        if (! is_null($request->returnDate)) {
            $data['returnDate'] = $request->returnDate;
        }

        return $data;
    }

    public function shipmentFormat($id)
    {
        $customer = $this->customer->where('id', $id)->with(['company'])->first();

        // Ambil shipment terakhir untuk tahun berjalan
        $lastShipment = $this->service
            ->where('customerCode', $customer->code)
            ->whereYear('created_at', now()->year)
            ->orderByDesc('created_at')
            ->first();

        // Default increment = 1
        $lastNumber = 0;

        if ($lastShipment && preg_match('/\/(\d{5})\//', $lastShipment->shipmentNumber, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        // Loop sampai dapat shipmentNumber yang unik
        do {
            $lastNumber++;
            $increment = str_pad($lastNumber, 5, '0', STR_PAD_LEFT);

            $shipmentNumber = $customer->company->format . '/' . $customer->code . '/' . $increment . '/' . now()->year;

            $checkShipment = $this->service->where('shipmentNumber', $shipmentNumber)->first();
        } while ($checkShipment); // jika sudah ada, ulangi lagi

        return $shipmentNumber;
    }

    public function getFleet($fleet = null)
    {
        $fleetArr = $this->service->where('status', 0)->pluck('fleetCode')->toArray();

        if ($fleet) {
            $fleetArr = $this->service->where('status', 0)->where('fleetCode', '!=', $fleet)->pluck('fleetCode')->toArray();
        }

        return $this->fleet->whereNotIn('code', $fleetArr)->get();
    }

    public function deleteOrderMaterial($id)
    {
        $data = $this->orderMaterial->where('id', $id)->first();

        $this->logActivity('Order Material', $data, 'Delete');

        $data->delete();
    }
}
