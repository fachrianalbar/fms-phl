<?php

namespace App\Services\Operational;

use App\Helpers\GenerateCode;
use App\Models\Data\Route;
use App\Models\Data\RoutePriceExternal;
use App\Models\Master\Customer;
use App\Models\Master\Employee;
use App\Models\Master\Fleet;
use App\Models\Operational\CustomerDetailOrder;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Models\Operational\OrderDriver;
use App\Models\Operational\OrderMaterial;
use App\Services\Finance\DirectPaymentService;
use App\Services\Finance\VendorPaymentService;
use App\Services\Operational\OrderCommissionService;
use App\Services\Operational\OrderDriverSalaryService;
use App\Services\UniqueCodeService;
use App\Traits\LogActivity;
use Illuminate\Support\Arr;

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

    public function __construct(Order $order, CustomerDetailOrder $customerDetailOrder, OrderCost $orderCost, Route $route, Customer $customer, Fleet $fleet, OrderMaterial $orderMaterial, private UniqueCodeService $uniqueCode, private VendorPaymentService $vendorPayment, private DirectPaymentService $directPayment)
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
        $code = $this->uniqueCode->resolve(
            model: Order::class,
            field: 'code',
            requestedCode: $request->input('code'),
        );
        $shipmentNumber = $this->uniqueCode->resolve(
            model: Order::class,
            field: 'shipmentNumber',
            requestedCode: $request->input('shipmentNumber'),
            normalize: fn ($value) => mb_strtoupper(trim($value)),
        );

        $data = $this->service->create(
            array_merge(['code' => $code->resolvedCode, 'shipmentNumber' => $shipmentNumber->resolvedCode], $this->buildOrderData($request))
        );

        // Cek apakah fleet adalah external
        $fleet = $this->fleet->where('code', $request->fleetCode)->first();
        $isExternalFleet = ($fleet && $fleet->company && strtolower($fleet->company->type) === 'external');

        // Ambil data terbaru route dan data komponen yang user kirim dari form create.
        $routeCostRows = $this->getRouteCostRows($request->routeData ?? null);
        $submittedCostRows = $this->extractSubmittedOrderCostRows($request);

        // Jika external fleet, hapus semua cost (jika ada data lama yang terlanjur)
        if ($isExternalFleet) {
            OrderCost::where('orderCode', $data->code)->delete();
            logger()->info('OrderCost cleared for external fleet', ['order' => $data->code]);
        } else {
            // Nominal komponen diambil dari isi form: default harga master route,
            // tetapi tetap bisa di-override user saat order dibuat.
            if ($request->has('costRowsSubmitted') || ! empty($submittedCostRows)) {
                $this->syncOrderCostRows($data->code, $submittedCostRows, $routeCostRows, true, true);
            } else {
                $this->persistRouteCostRows($data->code, $routeCostRows);
            }
        }

        if (isset($request->customerDetailCode)) {
            $this->storeCustomerDetailOrder($request);
        }

        if (isset($request->materialCode)) {
            $this->storeOrderMaterial($request);
        }

        // Auto upsert salary & commission cost components
        OrderDriverSalaryService::syncForOrder($data);
        OrderCommissionService::syncForOrder($data);

        $this->logActivity($title, $data, 'Create');

        return [
            'code' => $code,
            'shipmentNumber' => $shipmentNumber,
        ];
    }

    public function update($request, $id, $title)
    {

        $data = $this->getById($id);
        $previousDriverCode = $data->driverCode;
        $previousRouteCode = $data->routeCode;
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $shipmentNumber = $this->uniqueCode->resolve(
            model: Order::class,
            field: 'shipmentNumber',
            requestedCode: $request->input('shipmentNumber'),
            normalize: fn ($value) => mb_strtoupper(trim($value)),
            ignoreId: $data->id,
        );

        // Prepare update data and sanitize numeric fields (pass existing order to preserve snapshots if unchanged)
        $updateData = array_merge(['shipmentNumber' => $shipmentNumber->resolvedCode], $this->buildOrderData($request, $data));

        // Compute route's expected amount based on selected route and qty
        $selectedRoute = $this->route->where('code', $request->routeData)->first();
        $computedRouteAmount = null;
        if ($selectedRoute) {
            $computedRouteAmount = (float) ($selectedRoute->price * $request->qty);
        }

        // Get submitted (sanitized) routeAmount from update data
        $submittedRouteAmount = isset($updateData['routeAmount']) ? (float) $updateData['routeAmount'] : null;

        // Perform update
        $this->service->where('id', $id)->update($updateData);

        // Simpan histori perubahan supir agar penugasan lama tetap tercatat.
        $newDriverCode = $updateData['driverCode'] ?? null;
        if ($newDriverCode && $newDriverCode !== $previousDriverCode) {
            OrderDriver::create([
                'code' => GenerateCode::generateCode('ODR'),
                'orderCode' => $data->code,
                'driverCode' => $newDriverCode,
                'driverName' => $updateData['driverName'] ?? null,
                'description' => null,
            ]);
        }

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

        // Ambil data terbaru route dan data komponen yang user kirim dari form edit.
        $routeCostRows = $this->getRouteCostRows($request->routeData ?? null);
        $submittedCostRows = $this->extractSubmittedOrderCostRows($request);
        $routeChanged = (string) ($request->routeData ?? '') !== (string) $previousRouteCode;

        // Jika fleet external, hapus semua cost (tidak boleh ada cost untuk external fleet)
        // Ini juga mencakup cleanup jika sebelumnya ada data lama
        if ($isExternalFleet) {
            // Hapus semua cost menggunakan raw query agar pasti terhapus
            OrderCost::where('orderCode', $data->code)->delete();
            logger()->info('OrderCost cleared for external fleet on update', ['order' => $data->code]);
            // Jangan panggil storeOrderCost untuk fleet external
        } elseif ($request->has('costRowsSubmitted') || ! empty($submittedCostRows)) {
            // Upsert: nominal & nama komponen yang sudah tersimpan dipertahankan,
            // sehingga perubahan harga master tidak mengubah histori transaksi.
            // Rebuild penuh hanya dilakukan bila route order diganti.
            $this->syncOrderCostRows($data->code, $submittedCostRows, $routeCostRows, $routeChanged, true);
        }
        // Form tidak mengirim komponen biaya: biaya tersimpan dibiarkan apa adanya.

        if (isset($request->customerDetailCode)) {
            $this->customerDetailOrder->where('orderCode', $data->code)->delete();
            $this->storeCustomerDetailOrder($request);
        }

        if (isset($request->materialCode)) {
            $data->orderMaterial()->delete();
            $this->storeOrderMaterial($request);
        }

        // Auto upsert salary & commission cost components
        OrderDriverSalaryService::syncForOrder($data);
        OrderCommissionService::syncForOrder($data);

        // Nominal nota vendor mengikuti harga order terkini (tanpa generate ulang).
        $this->vendorPayment->syncNotaForOrderSafely($data->code, $title);

        // Nominal nota Pembayaran Langsung (customer non-DO) juga mengikuti harga order.
        $this->directPayment->syncNotaForOrderSafely($data->code, $title);

        $this->logActivity($title, $this->getById($id), 'After Update');

        return $shipmentNumber;
    }

    public function destroy($id, $title)
    {
        $data = $this->getById($id);

        if (! $data) {
            return;
        }

        // Pengaman ganda: order yang sudah dipakai nota vendor atau faktur tidak
        // boleh dihapus, agar dokumen finansial tsb tidak menjadi yatim.
        if ($data->vendorPayments()->exists() || $data->invoiceDetail()->exists()) {
            throw new \DomainException('Order tidak dapat dihapus karena sudah memiliki nota vendor atau faktur. Hapus/batalkan dokumen terkait terlebih dahulu.');
        }

        $this->logActivity($title, $data, 'Delete');

        $this->customerDetailOrder->where('orderCode', $data->code)->delete();

        $this->orderCost->where('orderCode', $data->code)->delete();

        $this->orderMaterial->where('orderCode', $data->code)->delete();

        \App\Models\Operational\OrderDriverSalary::where('order_id', $data->id)->where('status', '0')->delete();
        \App\Models\Operational\OrderCommission::where('order_id', $data->id)->where('status', '0')->delete();

        // Hapus lewat instance model agar SoftDeletes berlaku (bukan hard delete),
        // sehingga order yang terhapus masih bisa dipulihkan.
        $data->delete();
    }

    public function getCustomerDetailOrder($orderCode)
    {
        return $this->customerDetailOrder->where('orderCode', $orderCode)->with(['customerDetail'])->get();
    }

    private function getRouteCostRows($routeCode)
    {
        if (! $routeCode) {
            return [];
        }

        $route = $this->route->where('code', $routeCode)
            ->with('routeDetail')
            ->first();

        if (! $route || ! $route->routeDetail) {
            return [];
        }

        $rows = [];

        foreach ($route->routeDetail as $detail) {
            if (! isset($detail->componentCode)) {
                continue;
            }

            $rows[] = [
                'componentType' => $detail->componentCode,
                'nominal' => $this->normalizeCostNominal($detail->amount) ?? 0,
            ];
        }

        return $rows;
    }

    private function extractSubmittedOrderCostRows($request)
    {
        $componentNames = $request->input('componentName', []);
        $nominals = $request->input('nominal', []);
        $descriptions = $request->input('description', []);
        $types = $request->input('type', []);
        $drivers = $request->input('componentDriver', $request->input('internalCostDriver', []));

        if (! is_array($componentNames)) {
            $componentNames = [];
        }

        if (! is_array($nominals)) {
            $nominals = [];
        }

        if (! is_array($descriptions)) {
            $descriptions = [];
        }

        if (! is_array($types)) {
            $types = [];
        }

        if (! is_array($drivers)) {
            $drivers = [];
        }

        $maxRows = max(count($componentNames), count($nominals));
        $rows = [];

        for ($i = 0; $i < $maxRows; $i++) {
            $componentType = $componentNames[$i] ?? null;

            if (! $componentType) {
                continue;
            }

            $nominal = $this->normalizeCostNominal($nominals[$i] ?? null);

            if ($nominal === null) {
                continue;
            }

            $rows[] = [
                'componentType' => $componentType,
                'driverCode'    => ! empty($drivers[$i]) ? $drivers[$i] : null,
                'nominal'       => $nominal,
                'description'   => $descriptions[$i] ?? '',
                'type'          => $types[$i] ?? null,
            ];
        }

        return $rows;
    }

    private function normalizeCostNominal($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (int) round((float) $value);
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace(' ', '', $normalized);

        if (str_contains($normalized, ',')) {
            // Format Indonesia: 1.234,56
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            // Format tanpa decimal Indonesia: 1.234
            $normalized = str_replace('.', '', $normalized);
        }

        if (! is_numeric($normalized)) {
            return null;
        }

        return (int) round((float) $normalized);
    }

    /**
     * Sinkronkan baris biaya komponen order dengan isi form.
     *
     * Nominal & nama komponen disimpan sebagai snapshot transaksi: baris yang
     * sudah ada hanya diubah bila user mengirim nilai yang berbeda, sehingga
     * perubahan harga komponen/route master tidak menimpa order lama.
     *
     * @param  array<int, array<string, mixed>>  $submittedRows  baris dari form
     * @param  array<int, array<string, mixed>>  $routeRows  baris master route saat ini
     * @param  bool  $rebuildFromScratch  hapus dulu seluruh baris (mis. route diganti)
     * @param  bool  $trustSubmitted  perlakukan isi form sebagai sumber kebenaran
     */
    public function syncOrderCostRows($orderCode, array $submittedRows, array $routeRows = [], $rebuildFromScratch = false, $trustSubmitted = false)
    {
        $routeComponentCodes = [];
        foreach ($routeRows as $row) {
            if (! empty($row['componentType'])) {
                $routeComponentCodes[$row['componentType']] = true;
            }
        }

        if (empty($submittedRows) && ! $trustSubmitted) {
            // Tidak ada baris dari form (mis. order lama): gunakan master route.
            foreach ($routeRows as $row) {
                $submittedRows[] = [
                    'componentType' => $row['componentType'] ?? null,
                    'nominal' => (int) ($row['nominal'] ?? 0),
                    'description' => '',
                    'type' => null,
                ];
            }
        }

        if ($rebuildFromScratch) {
            $this->orderCost->where('orderCode', $orderCode)->delete();
        }

        $existing = $this->orderCost->where('orderCode', $orderCode)->get()->keyBy('componentType');
        $keptIds = [];

        foreach ($submittedRows as $row) {
            $componentType = $row['componentType'] ?? null;

            if (! $componentType) {
                continue;
            }

            $nominal = (int) ($row['nominal'] ?? 0);
            $existingRow = $existing->get($componentType);

            if ($existingRow) {
                $updates = [];

                if ((int) $existingRow->nominal !== $nominal) {
                    $updates['nominal'] = $nominal;
                }

                if (array_key_exists('description', $row)
                    && (string) ($existingRow->description ?? '') !== (string) ($row['description'] ?? '')) {
                    $updates['description'] = $row['description'] ?? '';
                }

                if (array_key_exists('type', $row)) {
                    $typeValue = $this->resolveCostTypeValue($row['type'] ?? null);
                    if ((string) ($existingRow->type ?? '') !== (string) ($typeValue ?? '')) {
                        $updates['type'] = $typeValue;
                    }
                }

                if (! empty($row['driverCode']) && $existingRow->driverCode !== $row['driverCode']) {
                    $updates['driverCode'] = $row['driverCode'];
                }

                if (! empty($updates)) {
                    $existingRow->fill($updates)->save();
                }

                $keptIds[] = $existingRow->id;

                continue;
            }

            $created = $this->orderCost->create([
                'code' => GenerateCode::generateUniqueCode('TOC', 'order_cost'),
                'componentType' => $componentType,
                'orderCode' => $orderCode,
                'driverCode' => $row['driverCode'] ?? null,
                'nominal' => $nominal,
                'description' => $row['description'] ?? '',
                'type' => $this->resolveCostTypeValue($row['type'] ?? null),
                'is_route' => isset($routeComponentCodes[$componentType]) ? 1 : 0,
            ]);

            $keptIds[] = $created->id;
        }

        $cleanup = $this->orderCost->where('orderCode', $orderCode);

        if (empty($keptIds)) {
            $cleanup->delete();
        } else {
            $cleanup->whereNotIn('id', $keptIds)->delete();
        }
    }

    private function persistRouteCostRows($orderCode, $routeCostRows)
    {
        foreach ($routeCostRows as $detail) {
            $componentType = $detail['componentType'] ?? null;

            if (! $componentType) {
                continue;
            }

            $this->orderCost->create([
                'code' => GenerateCode::generateUniqueCode('TOC', 'order_cost'),
                'componentType' => $componentType,
                'orderCode' => $orderCode,
                'nominal' => (int) ($detail['nominal'] ?? 0),
                'description' => '',
                'type' => null,
                'is_route' => 1,
            ]);
        }
    }

    private function resolveCostTypeValue($typeLabel)
    {
        if (! $typeLabel) {
            return null;
        }

        $normalizedType = strtolower(trim((string) $typeLabel));

        if ($normalizedType === 'ditagihkan' || $normalizedType === 'on charge') {
            return 'On Charge';
        }

        return null;
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
            // Sleep for 1ms to ensure unique millisecond component in code generator
            usleep(1000);

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

    private function buildOrderData($request, $existingOrder = null)
    {
        $route = $this->route->where('code', $request->routeData)->first();

        // Guard: jika route tidak ditemukan, jangan lanjut proses
        if (! $route) {
            throw new \Exception('Route dengan code ' . $request->routeData . ' tidak ditemukan');
        }

        // Debug: log route data
        logger()->info('Route data for order', [
            'routeCode' => $route->code,
            'price' => $route->price,
            'vendorPrice' => $route->vendorPrice,
            'personalVendorPrice' => $route->personalVendorPrice,
            'qty' => $request->qty,
        ]);

        // Check fleet company type
        $fleet = $this->fleet->where('code', $request->fleetCode)->with('company.company')->first();
        $isExternalFleet = ($fleet && $fleet->company && strcasecmp((string) $fleet->company->type, 'external') === 0);

        // Route amount selalu dihitung dari route terbaru agar konsisten antara create dan edit.
        $routeAmount = (float) (($route->price ?? 0) * $request->qty);

        $qty = (float) ($request->qty ?? 0);

        // --- NEW LOGIC: Customer's Company matches FleetCompany's Company (berlaku mulai 1 Oktober 2026) ---
        $isSameCompany = false;
        $orderDate = $request->orderDate ?? now()->format('Y-m-d');
        $isEffectiveNewRule = ($orderDate >= '2026-10-01');
        $customer = $this->customer->where('code', $request->customerCode)->with('company')->first();
        if ($isEffectiveNewRule && $fleet && $fleet->company && $fleet->company->company && $customer && $customer->company)
        {
            $fleetCompanyCode = $fleet->company->company->code ?? null;
            $customerCompanyCode = $customer->company->code ?? null;
            $isSameCompany = ($fleetCompanyCode && $customerCompanyCode && $fleetCompanyCode === $customerCompanyCode);
        }

        if ($isSameCompany) {
            // Harga beli mengikuti routeAmount saat mobil dan pelanggan dimiliki oleh perusahaan yang sama
            $vendorPriceSingle = ($qty > 0) ? ($routeAmount / $qty) : $route->price; // Harga per unit
            $vendorPrice = $routeAmount;
            $personalVendorPriceSingle = 0.0;
            $personalVendorPrice = 0.0;
        } elseif ($isExternalFleet) {
            $routePriceExt = RoutePriceExternal::where('route_id', $route->id)
                ->where('fleet_company_id', $fleet->company->id)
                ->first();

            $vendorPriceSingle = (float) ($routePriceExt ? $routePriceExt->amount : 0);

            if ($vendorPriceSingle == 0) {
                session()->flash('warning', 'Route ini dengan vendor ini belum di setting harga nya, silahkan setting harga.');
            }

            $vendorPrice = $vendorPriceSingle * $qty;
            $personalVendorPriceSingle = 0.0;
            $personalVendorPrice = 0.0;
        } else {
            $personalVendorPriceSingle = (float) ($route->personalVendorPrice ?? 0);
            
            if ($personalVendorPriceSingle == 0) {
                session()->flash('warning', 'Route ini belum di setting harga vendor internal nya, silahkan setting harga.');
            }

            $personalVendorPrice = $personalVendorPriceSingle * $qty;
            $vendorPriceSingle = 0.0;
            $vendorPrice = 0.0;
        }

        logger()->info('Calculated order prices', [
            'routeAmount' => $routeAmount,
            'price' => (float) ($route->price ?? 0),
            'vendorPrice' => $vendorPrice,
            'vendorPriceSingle' => $vendorPriceSingle,
            'personalVendorPrice' => $personalVendorPrice,
            'personalVendorPriceSingle' => $personalVendorPriceSingle,
            'isExternalFleet' => $isExternalFleet,
            'fleetCode' => $request->fleetCode,
        ]);

        // Preserve plateNumber snapshot if same fleet and snapshot exists, otherwise take current fleet's plate
        $plateNumber = null;
        if ($existingOrder instanceof Order && $existingOrder->fleetCode === $request->fleetCode && ! empty($existingOrder->plateNumber)) {
            $plateNumber = $existingOrder->plateNumber;
        } else {
            $plateNumber = $fleet?->plateNumber ?? null;
        }

        // Preserve driverName snapshot if the driver is unchanged, otherwise take the current employee name.
        $driverCode = $request->driverCode ?: null;
        $existingDriverName = $existingOrder instanceof Order ? $existingOrder->getRawOriginal('driverName') : null;
        if ($existingOrder instanceof Order && $existingOrder->driverCode === $driverCode && ! empty($existingDriverName)) {
            $driverName = $existingDriverName;
        } else {
            $driverName = $driverCode ? Employee::where('code', $driverCode)->value('name') : null;
        }

        $data = [
            'orderDate' => $request->orderDate,
            'notes' => $request->notes,
            'sto' => $request->sto,
            'salesOrder' => $request->salesOrder,
            'fleetCode' => $request->fleetCode,
            'plateNumber' => $plateNumber,
            'driverCode' => $driverCode,
            'driverName' => $driverName,
            'routeCode' => $request->routeData,
            'qty' => $request->qty,
            'orderTypeCode' => $request->orderTypeCode,
            'routeAmount' => $routeAmount,
            'price' => (float) ($route->price ?? 0),
            'vendorPrice' => $vendorPrice,
            'vendorPriceSingle' => $vendorPriceSingle,
            'personalVendorPrice' => $personalVendorPrice,
            'personalVendorPriceSingle' => $personalVendorPriceSingle,
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

        return $this->fleet->with('company')->whereNotIn('code', $fleetArr)->whereNotNull('fleetCompanyCode')->get();
    }

    public function checkRoutePriceMismatch($order)
    {
        if (! $order || ! $order->routeCode) {
            return false;
        }

        // Get the route
        $route = $this->route->where('code', $order->routeCode)->first();
        if (! $route) {
            return false;
        }

        // Get the fleet and check company type
        $fleet = $this->fleet->where('code', $order->fleetCode)->with('company.company')->first();
        $isExternalFleet = ($fleet && $fleet->company && strcasecmp((string) $fleet->company->type, 'external') === 0);

        // Check if customer's company matches fleet's company (hanya berlaku mulai 1 Oktober 2026)
        $customer = $this->customer->where('code', $order->customerCode)->with('company')->first();
        $isSameCompany = false;
        $isEffectiveNewRule = ($order->orderDate >= '2026-10-01');
        if ($isEffectiveNewRule && $fleet && $fleet->company && $fleet->company->company && $customer && $customer->company) {
            $fleetCompanyCode = $fleet->company->company->code ?? null;
            $customerCompanyCode = $customer->company->code ?? null;
            $isSameCompany = ($fleetCompanyCode && $customerCompanyCode && $fleetCompanyCode === $customerCompanyCode);
        }

        $qty = (float) ($order->qty ?? 0);
        $masterPrice = (float) ($route->price ?? 0);
        $computedRouteAmount = $masterPrice * $qty;

        if ($isSameCompany) {
            $vendorPriceSingle = ($qty > 0) ? ($computedRouteAmount / $qty) : $masterPrice;
            $vendorPrice = $computedRouteAmount;
            $personalVendorPriceSingle = 0.0;
            $personalVendorPrice = 0.0;
        } elseif ($isExternalFleet) {
            $routePriceExt = RoutePriceExternal::where('route_id', $route->id)
                ->where('fleet_company_id', $fleet->company->id)
                ->first();

            $vendorPriceSingle = (float) ($routePriceExt ? $routePriceExt->amount : 0);
            $vendorPrice = $vendorPriceSingle * $qty;
            $personalVendorPriceSingle = 0.0;
            $personalVendorPrice = 0.0;
        } else {
            $personalVendorPriceSingle = (float) ($route->personalVendorPrice ?? 0);
            $personalVendorPrice = $personalVendorPriceSingle * $qty;
            $vendorPriceSingle = 0.0;
            $vendorPrice = 0.0;
        }

        // Check price mismatch
        if ((float) $order->price !== $masterPrice ||
            (float) $order->routeAmount !== $computedRouteAmount ||
            (float) $order->vendorPriceSingle !== $vendorPriceSingle ||
            (float) $order->vendorPrice !== $vendorPrice ||
            (float) $order->personalVendorPriceSingle !== $personalVendorPriceSingle ||
            (float) $order->personalVendorPrice !== $personalVendorPrice) {
            return true;
        }

        // Get all master route details for this route
        $routeDetails = \App\Models\Data\RouteDetail::where('routeCode', $order->routeCode)->get()->keyBy('componentCode');

        // Get all order costs for this order
        $orderCosts = $this->orderCost->where('orderCode', $order->code)->get();

        foreach ($orderCosts as $cost) {
            if ($cost->is_route == 1) {
                $routeDetail = $routeDetails->get($cost->componentType);
                if (! $routeDetail || (int) $routeDetail->amount !== (int) $cost->nominal) {
                    return true;
                }
            }
        }

        // Also check if any master route detail is missing in the order's route costs
        $orderRouteComponents = $orderCosts->where('is_route', 1)->pluck('componentType')->toArray();
        foreach ($routeDetails as $componentCode => $detail) {
            if (! in_array($componentCode, $orderRouteComponents)) {
                return true;
            }
        }

        return false;
    }

    public function deleteOrderMaterial($id)
    {
        $data = $this->orderMaterial->where('id', $id)->first();

        $this->logActivity('Order Material', $data, 'Delete');

        $data->delete();
    }
}
