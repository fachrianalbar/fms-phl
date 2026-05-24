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

        // Calculate routeAmount: qty x price dari route
        $routeAmount = (int) (($route->price ?? 0) * $request->qty);

        // Handle personalVendorPrice untuk external fleet
        $personalVendorPrice = 0;
        if ($isExternalFleet) {
            $personalVendorPrice = (int) (($route->personalVendorPrice ?? 0) * $request->qty);
        }

        // Prepare update data
        $updateData = [
            'orderDate' => $request->orderDate,
            'fleetCode' => $request->fleetCode,
            'driverCode' => $request->driverCode,
            'routeCode' => $request->routeData,
            'qty' => $request->qty,
            'routeAmount' => $routeAmount,
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

        // Handle "On Charge" and "Off Charge" manual costs (is_route = 0) for both internal and external fleets
        // Only delete existing costs if the request actually provides at least one valid component
        if ($request->has('externalCostComponent')) {
            // Normalize to array if single value provided
            $externalCostComponentsRaw = $request->externalCostComponent;
            if (! is_array($externalCostComponentsRaw)) {
                $externalCostComponentsRaw = [$externalCostComponentsRaw];
            }

            $externalCostTypesRaw = $request->externalCostType ?? [];
            if (! is_array($externalCostTypesRaw)) {
                $externalCostTypesRaw = [$externalCostTypesRaw];
            }

            $externalCostNominalsRaw = $request->externalCostNominal ?? [];
            if (! is_array($externalCostNominalsRaw)) {
                $externalCostNominalsRaw = [$externalCostNominalsRaw];
            }

            $externalCostDescriptionsRaw = $request->externalCostDescription ?? [];
            if (! is_array($externalCostDescriptionsRaw)) {
                $externalCostDescriptionsRaw = [$externalCostDescriptionsRaw];
            }

            $externalCostIdsRaw = $request->externalCostId ?? [];
            if (! is_array($externalCostIdsRaw)) {
                $externalCostIdsRaw = [$externalCostIdsRaw];
            }

            $externalCostDeletesRaw = $request->externalCostDelete ?? [];
            if (! is_array($externalCostDeletesRaw)) {
                $externalCostDeletesRaw = [$externalCostDeletesRaw];
            }

            $externalCostComponents = array_filter($externalCostComponentsRaw, fn($c) => ! empty($c));

            if (count($externalCostComponents) > 0) {
                // First, delete costs that are marked for deletion
                foreach ($externalCostIdsRaw as $index => $costId) {
                    if (! empty($costId) && isset($externalCostDeletesRaw[$index]) && $externalCostDeletesRaw[$index] == '1') {
                        $this->orderCost->where('id', $costId)->delete();
                    }
                }

                // Delete existing manual costs (is_route = 0) that are not in the current list
                $existingIds = array_filter($externalCostIdsRaw, fn($id) => ! empty($id));
                if (! empty($existingIds)) {
                    $this->orderCost->where('orderCode', $order->code)
                        ->where('is_route', 0)
                        ->whereNotIn('id', $existingIds)
                        ->delete();
                } else {
                    // If no existing IDs, delete all existing manual costs
                    $this->orderCost->where('orderCode', $order->code)
                        ->where('is_route', 0)
                        ->delete();
                }

                foreach ($externalCostComponentsRaw as $index => $componentCode) {
                    if (empty($componentCode)) {
                        continue;
                    }

                    $type = $externalCostTypesRaw[$index] ?? 'On Charge';
                    $nominalRaw = $externalCostNominalsRaw[$index] ?? 0;
                    $nominal = (int) str_replace('.', '', (string) $nominalRaw);
                    $description = $externalCostDescriptionsRaw[$index] ?? null;
                    $existingId = $externalCostIdsRaw[$index] ?? null;
                    $isDeleted = $externalCostDeletesRaw[$index] ?? '0';

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
                            'is_route' => 0,
                        ]);
                    }
                }

                // Log final state after creating costs
                $countAfter = $this->orderCost->where('orderCode', $order->code)->count();

                logger()->info('Internal fleet external costs updated for NotReturnDo', [
                    'order' => $order->code,
                    'requested' => count($externalCostComponentsRaw),
                    'valid' => count($externalCostComponents),
                    'count_after' => $countAfter,
                ]);
            } else {
                // No valid components provided; keep existing costs untouched
                logger()->info('No external cost components provided in request; existing costs kept', ['order' => $order->code]);
            }
        } else {
            logger()->info('No external cost component fields present in request', ['order' => $order->code]);
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
