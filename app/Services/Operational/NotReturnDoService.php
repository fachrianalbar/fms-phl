<?php

namespace App\Services\Operational;

use App\Helpers\GenerateCode;
use App\Models\Data\Route;
use App\Models\Master\Fleet;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Models\OrderDetail;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotReturnDoService
{
    use LogActivity;

    protected $service;

    protected $orderCost;

    protected $route;

    protected $fleet;

    public function __construct(Order $notReturnDo, OrderCost $orderCost, Route $route, Fleet $fleet)
    {
        $this->service = $notReturnDo;
        $this->orderCost = $orderCost;
        $this->route = $route;
        $this->fleet = $fleet;
    }

    public function findAll()
    {
        return $this->service->where('status', 3)->with([
            'fleetDriver.fleet',
            'driver',
            // 'fleetDriver.employee',
            'customer',
            'route.originLocation',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type',
            'fleet.company',
            'orderType',
            'orderStatus',
        ])->get();
    }

    public function datatable()
    {
        return $this->service->where('status', 3)->with([
            'fleetDriver.fleet',
            'driver',
            // 'fleetDriver.employee',
            'customer',
            'customerDetailOrders.customerDetail',
            'route.originLocation',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type',
            'fleet.company',
            'orderType',
            'orderStatus',
        ])->orderBy('orderDate', 'asc');
    }

    /**
     * Update NotReturnDo Order dengan logic khusus
     * - Hitung ulang routeAmount jika qty berubah (qty x price dari Route)
     * - Handle external fleet dengan personalVendorPrice
     * - TIDAK mengupdate cost component (hanya delete jika fleet berubah)
     */
    public function updateOrder($request, $id, $title)
    {
        $order = $this->service->where('id', $id)->firstOrFail();

        // Log before update
        $this->logActivity($title, $order, 'Before Update');

        // Fetch route data
        $route = $this->route->where('code', $request->routeData)->firstOrFail();

        // Fetch fleet data
        $fleet = $this->fleet->where('code', $request->fleetCode)->with('company')->first();
        $isExternalFleet = ($fleet && $fleet->company && strtolower($fleet->company->type) === 'external');

        // Check whether user requested to update prices from master or customer changed
        $isUpdateMasterPrice = (string) $request->input('update_master_price', '0') === '1';
        $qty = (float) $request->qty;

        $isCustomerChanged = ($request->has('customerCode') && ! empty($request->customerCode) && $order->customerCode !== $request->customerCode);

        if ($isUpdateMasterPrice || $order->routeCode !== $request->routeData || $isCustomerChanged) {
            // Update to latest master route prices
            $priceSingle = (float) ($route->price ?? 0);
            $routeAmount = (float) ($priceSingle * $qty);

            if ($isExternalFleet) {
                $routePriceExt = \App\Models\Data\RoutePriceExternal::where('route_id', $route->id)
                    ->where('fleet_company_id', $fleet->company->id)
                    ->first();
                $vendorPriceSingle = (float) ($routePriceExt ? $routePriceExt->amount : 0);
                $vendorPrice = (float) ($vendorPriceSingle * $qty);
                $personalVendorPriceSingle = 0.0;
                $personalVendorPrice = 0.0;
            } else {
                $vendorPriceSingle = 0.0;
                $vendorPrice = 0.0;
                $personalVendorPriceSingle = (float) ($route->personalVendorPrice ?? 0);
                $personalVendorPrice = (float) ($personalVendorPriceSingle * $qty);
            }
        } else {
            // Retain existing unit prices from order, calculate totals based on (possibly updated) qty
            $existingPriceSingle = (float) ($order->price ?? ($order->qty > 0 ? $order->routeAmount / $order->qty : 0));
            $priceSingle = $existingPriceSingle > 0 ? $existingPriceSingle : (float) ($route->price ?? 0);
            $routeAmount = (float) ($priceSingle * $qty);

            if ($isExternalFleet) {
                $existingVendorPriceSingle = (float) ($order->vendorPriceSingle ?? ($order->qty > 0 ? $order->vendorPrice / $order->qty : 0));
                $vendorPriceSingle = $existingVendorPriceSingle > 0 ? $existingVendorPriceSingle : 0.0;
                $vendorPrice = (float) ($vendorPriceSingle * $qty);
                $personalVendorPriceSingle = 0.0;
                $personalVendorPrice = 0.0;
            } else {
                $vendorPriceSingle = 0.0;
                $vendorPrice = 0.0;
                $existingPersonalVendorPriceSingle = (float) ($order->personalVendorPriceSingle ?? ($order->qty > 0 ? $order->personalVendorPrice / $order->qty : 0));
                $personalVendorPriceSingle = $existingPersonalVendorPriceSingle > 0 ? $existingPersonalVendorPriceSingle : (float) ($route->personalVendorPrice ?? 0);
                $personalVendorPrice = (float) ($personalVendorPriceSingle * $qty);
            }
        }

        // Prepare update data
        $updateData = [
            'customerCode' => $request->customerCode ?? $order->customerCode,
            'orderDate' => $request->orderDate,
            'fleetCode' => $request->fleetCode,
            'driverCode' => $request->driverCode,
            'routeCode' => $request->routeData,
            'qty' => $qty,
            'price' => $priceSingle,
            'routeAmount' => $routeAmount,
            'vendorPriceSingle' => $vendorPriceSingle,
            'vendorPrice' => $vendorPrice,
            'personalVendorPriceSingle' => $personalVendorPriceSingle,
            'personalVendorPrice' => $personalVendorPrice,
            'notes' => $request->notes,
            'orderTypeCode' => $request->orderTypeCode,
        ];

        // Handle returnDate (optional)
        if (! is_null($request->returnDate)) {
            $updateData['returnDate'] = $request->returnDate;
        }

        // Perform update
        $this->service->where('id', $id)->update($updateData);

        // Refresh data after update
        $order = $this->service->where('id', $id)->firstOrFail();

        // Handle costs based on fleet type
        if ($isExternalFleet) {
            // If fleet external, remove route-based costs (is_route = 1) but keep manual costs (is_route = 0)
            $this->orderCost->where('orderCode', $order->code)->where('is_route', 1)->delete();
            logger()->info('Route-based OrderCost cleared for external fleet on NotReturnDo update', ['order' => $order->code]);
        }

        // Handle all cost components (Biaya Komponen - unified form)
        if ($request->has('internalCostComponent')) {
            $internalCostComponentsRaw = $request->internalCostComponent;
            if (! is_array($internalCostComponentsRaw)) {
                $internalCostComponentsRaw = [$internalCostComponentsRaw];
            }

            $internalCostTypesRaw = $request->internalCostType ?? [];
            if (! is_array($internalCostTypesRaw)) {
                $internalCostTypesRaw = [$internalCostTypesRaw];
            }

            $internalCostNominalsRaw = $request->internalCostNominal ?? [];
            if (! is_array($internalCostNominalsRaw)) {
                $internalCostNominalsRaw = [$internalCostNominalsRaw];
            }

            $internalCostDescriptionsRaw = $request->internalCostDescription ?? [];
            if (! is_array($internalCostDescriptionsRaw)) {
                $internalCostDescriptionsRaw = [$internalCostDescriptionsRaw];
            }

            $internalCostIdsRaw = $request->internalCostId ?? [];
            if (! is_array($internalCostIdsRaw)) {
                $internalCostIdsRaw = [$internalCostIdsRaw];
            }

            $internalCostIsRouteRaw = $request->internalCostIsRoute ?? [];
            if (! is_array($internalCostIsRouteRaw)) {
                $internalCostIsRouteRaw = [$internalCostIsRouteRaw];
            }

            $internalCostDeletesRaw = $request->internalCostDelete ?? [];
            if (! is_array($internalCostDeletesRaw)) {
                $internalCostDeletesRaw = [$internalCostDeletesRaw];
            }

            $internalCostComponents = array_filter($internalCostComponentsRaw, fn ($c) => ! empty($c));

            // First, delete costs that are marked for deletion
            foreach ($internalCostIdsRaw as $index => $costId) {
                if (! empty($costId) && isset($internalCostDeletesRaw[$index]) && $internalCostDeletesRaw[$index] == '1') {
                    $this->orderCost->where('id', $costId)->delete();
                }
            }

            // Collect existing IDs that are NOT deleted (to preserve them)
            $preservedIds = [];
            foreach ($internalCostIdsRaw as $index => $costId) {
                if (! empty($costId) && (! isset($internalCostDeletesRaw[$index]) || $internalCostDeletesRaw[$index] != '1')) {
                    $preservedIds[] = $costId;
                }
            }

            // Delete ALL costs that are no longer in the form
            if (! empty($preservedIds)) {
                $this->orderCost->where('orderCode', $order->code)
                    ->whereNotIn('id', $preservedIds)
                    ->delete();
            } elseif (count($internalCostComponents) === 0) {
                // If no valid components submitted, delete all costs
                $this->orderCost->where('orderCode', $order->code)->delete();
            }

            // Process each cost component
            foreach ($internalCostComponentsRaw as $index => $componentCode) {
                if (empty($componentCode)) {
                    continue;
                }

                $type = $internalCostTypesRaw[$index] ?? 'On Charge';
                $nominalRaw = $internalCostNominalsRaw[$index] ?? 0;
                $nominal = (int) str_replace('.', '', (string) $nominalRaw);
                $description = $internalCostDescriptionsRaw[$index] ?? null;
                $existingId = $internalCostIdsRaw[$index] ?? null;
                $isRoute = $internalCostIsRouteRaw[$index] ?? 0;
                $isDeleted = $internalCostDeletesRaw[$index] ?? '0';

                // Skip if this cost is marked for deletion
                if ($isDeleted == '1') {
                    continue;
                }

                // If this is an existing cost, update it
                if (! empty($existingId)) {
                    $this->orderCost->where('id', $existingId)->update([
                        'componentType' => $componentCode,
                        'nominal' => $nominal,
                        'type' => $type,
                        'description' => $description,
                    ]);
                } else {
                    // Create new cost
                    $this->orderCost->create([
                        'code' => GenerateCode::generateCode('OCT'),
                        'orderCode' => $order->code,
                        'componentType' => $componentCode,
                        'nominal' => $nominal,
                        'type' => $type,
                        'description' => $description,
                        'is_route' => (int) $isRoute,
                    ]);
                }
            }

            logger()->info('Cost components updated for NotReturnDo', [
                'order' => $order->code,
                'requested' => count($internalCostComponentsRaw),
                'valid' => count($internalCostComponents),
            ]);
        }
        // Update Material Data if provided in the request
        if (isset($request->materialCode)) {
            $order->orderMaterial()->delete();

            $materialCodes = $request->materialCode;
            $unitCodes = $request->unitCode ?? [];
            $materialQties = $request->materialQty ?? [];
            $unitCodes2 = $request->unitCode2 ?? [];
            $materialQties2 = $request->materialQty2 ?? [];

            for ($i = 0; $i < count($materialCodes); $i++) {
                if (empty($materialCodes[$i])) {
                    continue;
                }

                // Sleep for 1ms to ensure unique millisecond component in code generator
                usleep(1000);

                $orderMaterial = $order->orderMaterial()->create([
                    'code' => GenerateCode::generateCode('FOM', true),
                    'orderCode' => $order->code,
                    'materialCode' => $materialCodes[$i] ?? null,
                    'unitCode' => $unitCodes[$i] ?? null,
                    'materialQty' => isset($materialQties[$i]) ? (int) $materialQties[$i] : null,
                    'unitCode2' => $unitCodes2[$i] ?? null,
                    'materialQty2' => isset($materialQties2[$i]) ? (int) $materialQties2[$i] : null,
                ]);

                $this->logActivity('Order Material', $orderMaterial, 'Create');
            }
        }

        // Log after update
        $this->logActivity($title, $this->service->where('id', $id)->firstOrFail(), 'After Update');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->firstOrFail();
    }

    /**
     * Upload Surat Jalan files untuk order
     * - Generate encrypted filename menggunakan SHA256
     * - Store file di storage public
     * - Create OrderDetail record dengan type 'surat_jalan'
     */
    public function uploadSuratJalan($request, string $code)
    {
        try {
            DB::beginTransaction();

            $order = $this->service->where('code', $code)->firstOrFail();

            $uploadedCount = 0;

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    // Generate encrypted filename menggunakan SHA256
                    $originalName = $file->getClientOriginalName();
                    $encryptedName = hash('sha256', $originalName . time() . uniqid()) . '.' . $file->getClientOriginalExtension();

                    // Store file di storage public
                    $path = $file->storeAs('order-detail', $encryptedName, 'public');

                    // Create OrderDetail record
                    OrderDetail::create([
                        'id' => Str::uuid(),
                        'order_id' => $order->id,
                        'file' => $path,
                        'type' => 'surat_jalan',
                    ]);

                    $uploadedCount++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "$uploadedCount file berhasil diupload",
                'count' => $uploadedCount,
            ];
        } catch (\Throwable $th) {
            DB::rollback();

            throw $th;
        }
    }

    public function rollbackStatus($id)
    {
        $this->service->where('id', $id)->update([
            'status' => 0,
        ]);
    }
}
