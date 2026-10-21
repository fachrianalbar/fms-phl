<?php

namespace App\Services\Purchasing;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Warehouse;
use App\Models\LiveMutation;
use App\Models\Mutation;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\Purchasing\PurchasePaymentHistory;
use App\Models\StockTransaction;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Models\Warehouse\MaintenanceFifo;
use App\Models\Warehouse\MaintenancePurchase;
use App\Services\UniqueCodeService;
use App\Traits\LogActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DirectPurchaseService
{
    use LogActivity;

    /** Kode tipe mutasi "Purchase" pada tabel transaction_type */
    public const TRANSACTION_TYPE = 'FTT250306114138';

    /** Jenis pembayaran pembelian langsung (tidak terhubung ke akun kas/bank) */
    public const PAYMENT_TYPE_CASH = 'Tunai';

    public const PAYMENT_TYPE_TRANSFER = 'Transfer';

    public const PAYMENT_TYPE_TEMPO = 'Tempo';

    public const PAYMENT_TYPES = [
        self::PAYMENT_TYPE_CASH,
        self::PAYMENT_TYPE_TRANSFER,
        self::PAYMENT_TYPE_TEMPO,
    ];

    protected Purchase $service;

    public function __construct(
        Purchase $purchase,
        private UniqueCodeService $uniqueCode
    ) {
        $this->service = $purchase;
    }

    public function datatable()
    {
        return $this->service->query()
            ->with([
                'supplier',
                'warehouse',
                'fleet',
                'details.item',
                'maintenances',
                'transferUserBank.bank',
            ])
            ->where('is_direct', true)
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc');
    }

    public function stats(): array
    {
        $query = $this->service->newQuery()->where('is_direct', true);

        $totalCount = (int) $query->count();
        $totalNominal = (float) $query->sum('nominal');

        $fleetCount = (int) $this->service->newQuery()
            ->where('is_direct', true)
            ->distinct('fleetCode')
            ->count('fleetCode');

        return [
            'totalCount' => $totalCount,
            'totalNominal' => $totalNominal,
            'fleetCount' => $fleetCount,
        ];
    }

    public function getById(string $id)
    {
        return $this->service->query()
            ->where('id', $id)
            ->where('is_direct', true)
            ->with([
                'supplier',
                'warehouse',
                'fleet',
                'details.item',
                'maintenances.details.item',
                'transferUserBank.bank',
            ])
            ->first();
    }

    public function store(Request $request, string $title)
    {
        $warehouseCode = $request->warehouseCode ?? Warehouse::query()->first()->code;

        // 1. Resolve Unique Code untuk Purchase (PL-...)
        $carbonDate = Carbon::parse($request->date);
        $basePurchaseCode = 'PL-' . $carbonDate->format('ymd');
        $codePurchase = $this->uniqueCode->resolve(
            model: Purchase::class,
            field: 'code',
            requestedCode: $request->input('code'),
            prefix: $basePurchaseCode,
            digits: 4
        );
        $purchaseCode = $codePurchase->resolvedCode;

        // 2. Resolve Unique Code untuk Maintenance (MNT-...)
        $baseMntCode = 'MNT-' . $carbonDate->format('ymd');
        $codeMnt = $this->uniqueCode->resolve(
            model: Maintenance::class,
            field: 'code',
            requestedCode: null,
            prefix: $baseMntCode,
            digits: 5
        );
        $maintenanceCode = $codeMnt->resolvedCode;

        // Hitung total nominal terlebih dahulu
        $totalNominal = 0;
        $filtered = Arr::only($request->all(), ['qty', 'itemCode', 'price', 'description']);
        if (isset($filtered['itemCode'])) {
            for ($i = 0; $i < count($filtered['itemCode']); $i++) {
                $price = (int) str_replace(['Rp', '.', ' '], '', $filtered['price'][$i]);
                $qty = (float) $filtered['qty'][$i];
                $totalNominal += ($price * $qty);
            }
        }

        // 3. Simpan data Purchase (Direct Purchase)
        //    Pembelian langsung tetap dicatat sebagai hutang supplier seperti PO
        //    biasa (paymentStatus Unpaid), pelunasan dilakukan lewat menu Hutang Vendor.
        $paymentType = $this->normalizePaymentType($request->paymentType);
        $transferUserBankCode = $paymentType === self::PAYMENT_TYPE_TRANSFER
            ? $request->transferUserBankCode
            : null;

        $purchase = $this->service->newQuery()->create([
            'code' => $purchaseCode,
            'supplierCode' => $request->supplierCode,
            'warehouseCode' => $warehouseCode,
            'is_direct' => true,
            'fleetCode' => $request->fleetCode,
            'date' => $request->date,
            'time' => $request->time,
            'dueDate' => $request->dueDate ?: $request->date,
            'receivedDate' => $request->date,
            'paymentDate' => null,
            'status' => 0, // Purchase Created, sama seperti PO belum dibayar
            'paymentType' => $paymentType,
            'transferUserBankCode' => $transferUserBankCode,
            'paymentStatus' => 'Unpaid',
            'paidAmount' => 0,
            'nominal' => $totalNominal,
            'userBankCode' => null,
        ]);

        // 4. Simpan data Maintenance (Servis Mobil)
        $maintenance = Maintenance::query()->create([
            'code' => $maintenanceCode,
            'date' => $request->date,
            'time' => $request->time,
            'fleetCode' => $request->fleetCode,
            'warehouseCode' => $warehouseCode,
            'status' => 0, // Aktif
            'grand_total' => number_format($totalNominal, 2, '.', ''),
        ]);

        // 5. Hubungkan Maintenance dan Purchase melalui tabel pivot
        MaintenancePurchase::query()->create([
            'maintenance_id' => $maintenance->id,
            'purchase_id' => $purchase->id,
        ]);

        // 6. Detail Barang, Pemotongan & Pencatatan Stok (IN lalu OUT)
        if (isset($filtered['itemCode'])) {
            for ($i = 0; $i < count($filtered['itemCode']); $i++) {
                $itemCode = $filtered['itemCode'][$i];
                $price = (int) str_replace(['Rp', '.', ' '], '', $filtered['price'][$i]);
                $qty = (float) $filtered['qty'][$i];
                $description = $filtered['description'][$i] ?? null;

                // Update Master Item Price
                Item::query()->where('code', $itemCode)->update(['price' => $price]);

                // 6a. Simpan PurchaseDetail
                $purchaseDetail = PurchaseDetail::query()->create([
                    'code' => GenerateCode::generateCode('FPD', true),
                    'itemCode' => $itemCode,
                    'qty' => $qty,
                    'receivedQty' => $qty,
                    'qtyUsed' => $qty, // Langsung digunakan di mobil
                    'status' => 1,
                    'purchaseCode' => $purchaseCode,
                    'price' => $price,
                    'description' => $description,
                ]);

                // 6b. Catat Stok Masuk (Stock In)
                $stock = Stock::query()->where('itemCode', $itemCode)->first();
                if ($stock) {
                    $stock->increment('stockIn', $qty);
                } else {
                    Stock::query()->create([
                        'code' => GenerateCode::generateCode('TSTC', true),
                        'itemCode' => $itemCode,
                        'stockIn' => $qty,
                        'stockOut' => 0,
                    ]);
                }

                // 6c. Catat Transaksi Stok IN
                StockTransaction::query()->create([
                    'code' => GenerateCode::generateCode('FST', true),
                    'itemCode' => $itemCode,
                    'warehouseCode' => $warehouseCode,
                    'qtyIn' => $qty,
                    'qtyOut' => 0,
                    'transactionCode' => $purchaseCode,
                    'transactionDetailCode' => $purchaseDetail->code,
                    'date' => $request->date . ' ' . $request->time,
                    'transactionType' => 'IN',
                ]);

                // 6d. Simpan MaintenanceDetail
                $maintenanceDetail = MaintenanceDetail::query()->create([
                    'code' => GenerateCode::generateCode('FMD', true),
                    'maintenanceCode' => $maintenanceCode,
                    'itemCode' => $itemCode,
                    'qty' => (string) $qty,
                    'price' => number_format($price, 2, '.', ''),
                    'total' => number_format($qty * $price, 2, '.', ''),
                    'description' => $description,
                ]);

                // 6e. Catat MaintenanceFifo (koneksi penggunaan PO)
                MaintenanceFifo::query()->create([
                    'code' => GenerateCode::generateCode('MF', true),
                    'maintenanceDetailCode' => $maintenanceDetail->code,
                    'purchaseDetailCode' => $purchaseDetail->code,
                    'qty' => $qty,
                ]);

                // 6f. Catat Stok Keluar (Stock Out)
                Stock::query()->where('itemCode', $itemCode)->increment('stockOut', $qty);

                // 6g. Catat Transaksi Stok OUT
                StockTransaction::query()->create([
                    'code' => GenerateCode::generateCode('FST', true),
                    'itemCode' => $itemCode,
                    'warehouseCode' => $warehouseCode,
                    'qtyIn' => 0,
                    'qtyOut' => $qty,
                    'transactionCode' => $maintenanceCode,
                    'transactionDetailCode' => $maintenanceDetail->code,
                    'date' => $request->date . ' ' . $request->time,
                    'transactionType' => 'OUT',
                ]);
            }
        }

        // 7. Catat aktivitas
        $this->logActivity($title, $purchase, 'Create');

        return $codePurchase;
    }

    public function update(Request $request, string $id, string $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $purchase = $this->getById($id);
        if (! $purchase) {
            throw new \Exception('Data Pembelian Langsung tidak ditemukan.');
        }

        // Nominal tidak boleh diubah setelah ada pelunasan, karena sisa hutang
        // dan mutasi bank dihitung dari nilai tagihan saat pembayaran.
        if ((float) $purchase->paidAmount > 0) {
            throw new \Exception(
                'Pembelian ini sudah memiliki pembayaran. Batalkan pembayaran di menu Hutang Vendor terlebih dahulu sebelum mengubah data.'
            );
        }

        $warehouseCode = $request->warehouseCode ?? $purchase->warehouseCode;
        $purchaseCode = $purchase->code;

        // Ambil data Maintenance terkait
        $maintenance = $purchase->maintenances->first();
        $maintenanceCode = $maintenance ? $maintenance->code : null;

        // 1. Rollback Stock IN dari Purchase lama
        $purchaseStockTx = StockTransaction::query()->where('transactionCode', $purchaseCode)->get();
        foreach ($purchaseStockTx as $tx) {
            Stock::query()->where('itemCode', $tx->itemCode)->decrement('stockIn', $tx->qtyIn);
        }
        StockTransaction::query()->where('transactionCode', $purchaseCode)->forceDelete();

        // 2. Rollback Stock OUT dari Maintenance lama
        if ($maintenanceCode) {
            $maintenanceStockTx = StockTransaction::query()->where('transactionCode', $maintenanceCode)->get();
            foreach ($maintenanceStockTx as $tx) {
                Stock::query()->where('itemCode', $tx->itemCode)->decrement('stockOut', $tx->qtyOut);
            }
            StockTransaction::query()->where('transactionCode', $maintenanceCode)->forceDelete();

            // Hapus MaintenanceFifo lama
            MaintenanceFifo::query()->whereIn('maintenanceDetailCode', $maintenance->details->pluck('code'))->delete();
            $maintenance->details()->delete();
        }

        // 3. Rollback mutasi lama yang terikat langsung ke kode pembelian
        //    (data lama). Mutasi Hutang Vendor terikat ke kode batch, bukan ke
        //    kode pembelian, sehingga tidak tersentuh di sini.
        $oldMutations = Mutation::query()->where('transactionCode', $purchaseCode)->get();
        foreach ($oldMutations as $mut) {
            $this->applyBankMovement($mut->userBankCode, (float) $mut->nominal, 'in');
            $mut->forceDelete();
        }

        // 4. Hitung ulang total nominal baru
        $totalNominal = 0;
        $filtered = Arr::only($request->all(), ['qty', 'itemCode', 'price', 'description']);
        if (isset($filtered['itemCode'])) {
            for ($i = 0; $i < count($filtered['itemCode']); $i++) {
                $price = (int) str_replace(['Rp', '.', ' '], '', $filtered['price'][$i]);
                $qty = (float) $filtered['qty'][$i];
                $totalNominal += ($price * $qty);
            }
        }

        // 5. Update Header Purchase
        //    Status pembayaran tidak direset: pelunasan dikelola lewat menu
        //    Hutang Vendor, jadi hanya disesuaikan bila nominal berubah.
        $paymentType = $this->normalizePaymentType($request->paymentType);
        $paidAmount = (float) $purchase->paidAmount;
        $paymentStatus = $paidAmount <= 0
            ? 'Unpaid'
            : ($paidAmount >= $totalNominal ? 'Paid' : 'Partial');

        $purchase->update([
            'supplierCode' => $request->supplierCode,
            'warehouseCode' => $warehouseCode,
            'fleetCode' => $request->fleetCode,
            'date' => $request->date,
            'time' => $request->time,
            'dueDate' => $request->dueDate ?: $request->date,
            'receivedDate' => $request->date,
            'nominal' => $totalNominal,
            'paymentType' => $paymentType,
            'transferUserBankCode' => $paymentType === self::PAYMENT_TYPE_TRANSFER
                ? $request->transferUserBankCode
                : null,
            'paymentStatus' => $paymentStatus,
            'status' => $paymentStatus === 'Paid' ? 3 : $purchase->status,
        ]);

        // 6. Update Header Maintenance
        if ($maintenance) {
            $maintenance->update([
                'fleetCode' => $request->fleetCode,
                'warehouseCode' => $warehouseCode,
                'date' => $request->date,
                'time' => $request->time,
                'grand_total' => number_format($totalNominal, 2, '.', ''),
            ]);
        } else {
            $carbonDate = Carbon::parse($request->date);
            $baseMntCode = 'MNT-' . $carbonDate->format('ymd');
            $codeMnt = $this->uniqueCode->resolve(
                model: Maintenance::class,
                field: 'code',
                requestedCode: null,
                prefix: $baseMntCode,
                digits: 5
            );
            $maintenanceCode = $codeMnt->resolvedCode;
            $maintenance = Maintenance::query()->create([
                'code' => $maintenanceCode,
                'date' => $request->date,
                'time' => $request->time,
                'fleetCode' => $request->fleetCode,
                'warehouseCode' => $warehouseCode,
                'status' => 0,
                'grand_total' => number_format($totalNominal, 2, '.', ''),
            ]);

            MaintenancePurchase::query()->create([
                'maintenance_id' => $maintenance->id,
                'purchase_id' => $purchase->id,
            ]);
        }

        // Hapus detail purchase lama
        $purchase->details()->delete();

        // 7. Simpan Ulang Detail Barang & Stok
        if (isset($filtered['itemCode'])) {
            for ($i = 0; $i < count($filtered['itemCode']); $i++) {
                $itemCode = $filtered['itemCode'][$i];
                $price = (int) str_replace(['Rp', '.', ' '], '', $filtered['price'][$i]);
                $qty = (float) $filtered['qty'][$i];
                $description = $filtered['description'][$i] ?? null;

                Item::query()->where('code', $itemCode)->update(['price' => $price]);

                $purchaseDetail = PurchaseDetail::query()->create([
                    'code' => GenerateCode::generateCode('FPD', true),
                    'itemCode' => $itemCode,
                    'qty' => $qty,
                    'receivedQty' => $qty,
                    'qtyUsed' => $qty,
                    'status' => 1,
                    'purchaseCode' => $purchaseCode,
                    'price' => $price,
                    'description' => $description,
                ]);

                $stock = Stock::query()->where('itemCode', $itemCode)->first();
                if ($stock) {
                    $stock->increment('stockIn', $qty);
                } else {
                    Stock::query()->create([
                        'code' => GenerateCode::generateCode('TSTC', true),
                        'itemCode' => $itemCode,
                        'stockIn' => $qty,
                        'stockOut' => 0,
                    ]);
                }

                StockTransaction::query()->create([
                    'code' => GenerateCode::generateCode('FST', true),
                    'itemCode' => $itemCode,
                    'warehouseCode' => $warehouseCode,
                    'qtyIn' => $qty,
                    'qtyOut' => 0,
                    'transactionCode' => $purchaseCode,
                    'transactionDetailCode' => $purchaseDetail->code,
                    'date' => $request->date . ' ' . $request->time,
                    'transactionType' => 'IN',
                ]);

                $maintenanceDetail = MaintenanceDetail::query()->create([
                    'code' => GenerateCode::generateCode('FMD', true),
                    'maintenanceCode' => $maintenance->code,
                    'itemCode' => $itemCode,
                    'qty' => (string) $qty,
                    'price' => number_format($price, 2, '.', ''),
                    'total' => number_format($qty * $price, 2, '.', ''),
                    'description' => $description,
                ]);

                MaintenanceFifo::query()->create([
                    'code' => GenerateCode::generateCode('MF', true),
                    'maintenanceDetailCode' => $maintenanceDetail->code,
                    'purchaseDetailCode' => $purchaseDetail->code,
                    'qty' => $qty,
                ]);

                Stock::query()->where('itemCode', $itemCode)->increment('stockOut', $qty);

                StockTransaction::query()->create([
                    'code' => GenerateCode::generateCode('FST', true),
                    'itemCode' => $itemCode,
                    'warehouseCode' => $warehouseCode,
                    'qtyIn' => 0,
                    'qtyOut' => $qty,
                    'transactionCode' => $maintenance->code,
                    'transactionDetailCode' => $maintenanceDetail->code,
                    'date' => $request->date . ' ' . $request->time,
                    'transactionType' => 'OUT',
                ]);
            }
        }

        // 8. Catat aktivitas
        $this->logActivity($title, $purchase, 'After Update');
    }

    public function destroy(string $id, string $title)
    {
        $purchase = $this->getById($id);
        if (! $purchase) {
            throw new \Exception('Data Pembelian Langsung tidak ditemukan.');
        }

        // Pembayaran dikelola lewat menu Hutang Vendor (batch + mutasi bank),
        // sehingga penghapusan hanya aman bila belum ada pelunasan sama sekali.
        if ((float) $purchase->paidAmount > 0
            || PurchasePaymentHistory::query()->where('purchaseCode', $purchase->code)->exists()) {
            throw new \Exception(
                'Pembelian ini sudah memiliki pembayaran. Batalkan pembayaran di menu Hutang Vendor terlebih dahulu sebelum menghapus.'
            );
        }

        $this->logActivity($title, $purchase, 'Delete');

        $purchaseCode = $purchase->code;
        $maintenance = $purchase->maintenances->first();
        $maintenanceCode = $maintenance ? $maintenance->code : null;

        // 1. Rollback Stock IN dari Purchase
        $purchaseStockTx = StockTransaction::query()->where('transactionCode', $purchaseCode)->get();
        foreach ($purchaseStockTx as $tx) {
            Stock::query()->where('itemCode', $tx->itemCode)->decrement('stockIn', $tx->qtyIn);
        }
        StockTransaction::query()->where('transactionCode', $purchaseCode)->forceDelete();

        // 2. Rollback Stock OUT dari Maintenance
        if ($maintenance) {
            $maintenanceStockTx = StockTransaction::query()->where('transactionCode', $maintenance->code)->get();
            foreach ($maintenanceStockTx as $tx) {
                Stock::query()->where('itemCode', $tx->itemCode)->decrement('stockOut', $tx->qtyOut);
            }
            StockTransaction::query()->where('transactionCode', $maintenance->code)->forceDelete();

            MaintenanceFifo::query()->whereIn('maintenanceDetailCode', $maintenance->details->pluck('code'))->delete();
            $maintenance->details()->delete();
            MaintenancePurchase::query()->where('maintenance_id', $maintenance->id)->delete();
            $maintenance->delete();
        }

        // 3. Rollback Mutasi Kas & Bank data lama (pembelian langsung tidak
        //    lagi membuat mutasi sendiri; mutasi kini milik batch Hutang Vendor).
        $mutations = Mutation::query()->where('transactionCode', $purchaseCode)->get();
        foreach ($mutations as $mut) {
            $this->applyBankMovement($mut->userBankCode, (float) $mut->nominal, 'in');
            $mut->forceDelete();
        }

        // 4. Hapus detail purchase & purchase
        $purchase->details()->delete();
        $purchase->delete();
    }

    /**
     * Normalisasi jenis pembayaran yang dikirim dari form.
     */
    private function normalizePaymentType(?string $paymentType): string
    {
        $normalized = ucfirst(strtolower(trim((string) $paymentType)));

        return in_array($normalized, self::PAYMENT_TYPES, true) ? $normalized : self::PAYMENT_TYPE_CASH;
    }

    /**
     * Terapkan pergerakan saldo bank pada live_mutation.
     */
    private function applyBankMovement(string $userBankCode, float $amount, string $direction): void
    {
        $liveMutation = LiveMutation::query()
            ->where('userBankCode', $userBankCode)
            ->lockForUpdate()
            ->first();

        if (! $liveMutation) {
            return;
        }

        $amount = (int) round($amount);
        $currentCredit = (float) $liveMutation->credit;
        $currentDebit = (float) $liveMutation->debit;

        if ($direction === 'out') {
            $currentCredit += $amount;
        } else {
            $currentDebit += $amount;
        }

        $liveMutation->credit = $currentCredit;
        $liveMutation->debit = $currentDebit;
        $liveMutation->balance = $currentDebit - $currentCredit;
        $liveMutation->save();
    }
}
