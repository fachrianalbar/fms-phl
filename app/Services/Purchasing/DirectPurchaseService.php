<?php

namespace App\Services\Purchasing;

use App\Helpers\GenerateCode;
use App\Models\Bank\UserBank;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Warehouse;
use App\Models\LiveMutation;
use App\Models\Master\Fleet;
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
                'userBank.bank',
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
                'userBank.bank',
                'paymentHistories.userBank.bank',
            ])
            ->first();
    }

    public function store(Request $request, string $title)
    {
        $warehouseCode = $request->warehouseCode ?? Warehouse::query()->first()->code;
        $fleet = Fleet::query()->where('code', $request->fleetCode)->first();

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

        // 3. Simpan data Purchase (Direct Purchase, Langsung Lunas)
        $purchase = $this->service->newQuery()->create([
            'code' => $purchaseCode,
            'supplierCode' => $request->supplierCode,
            'warehouseCode' => $warehouseCode,
            'is_direct' => true,
            'fleetCode' => $request->fleetCode,
            'date' => $request->date,
            'time' => $request->time,
            'dueDate' => $request->date,
            'receivedDate' => $request->date,
            'paymentDate' => $request->date,
            'status' => 3, // Selesai / Complete
            'paymentStatus' => 'Paid', // Lunas seketika
            'paidAmount' => $totalNominal,
            'nominal' => $totalNominal,
            'userBankCode' => $request->userBankCode ?: null,
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

        // 7. Jika Akun Kas / Bank Dipilih, Catat Pengeluaran Kas
        if (! empty($request->userBankCode) && $totalNominal > 0) {
            $userBank = UserBank::query()->where('code', $request->userBankCode)->first();
            if ($userBank) {
                $batchCode = GenerateCode::generateUniqueCode('FSP', 'supplier_payment_batch');
                $plateNumber = $fleet ? $fleet->plateNumber : $request->fleetCode;
                $desc = 'Pembelian Langsung Armada ' . $plateNumber . ' (' . $purchaseCode . ')';

                PurchasePaymentHistory::query()->create([
                    'purchaseCode' => $purchaseCode,
                    'batch_code' => $batchCode,
                    'amount' => $totalNominal,
                    'paymentDate' => $request->date,
                    'userBankCode' => $userBank->code,
                    'description' => $desc,
                ]);

                Mutation::query()->create([
                    'code' => GenerateCode::generateUniqueCode('FMT', 'mutation'),
                    'userBankCode' => $userBank->code,
                    'nominal' => $totalNominal,
                    'type' => 'Out',
                    'date' => $request->date,
                    'description' => $desc . ' sebesar Rp ' . number_format($totalNominal, 0, '.', ','),
                    'transactionCode' => $purchaseCode,
                    'transactionTypeCode' => self::TRANSACTION_TYPE,
                ]);

                $this->applyBankMovement($userBank->code, $totalNominal, 'out');
            }
        }

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

        $fleet = Fleet::query()->where('code', $request->fleetCode)->first();
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

        // 3. Rollback Mutasi Bank lama jika ada
        $oldMutations = Mutation::query()->where('transactionCode', $purchaseCode)->get();
        foreach ($oldMutations as $mut) {
            $this->applyBankMovement($mut->userBankCode, (float) $mut->nominal, 'in');
            $mut->forceDelete();
        }
        PurchasePaymentHistory::query()->where('purchaseCode', $purchaseCode)->forceDelete();

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
        $purchase->update([
            'supplierCode' => $request->supplierCode,
            'warehouseCode' => $warehouseCode,
            'fleetCode' => $request->fleetCode,
            'date' => $request->date,
            'time' => $request->time,
            'dueDate' => $request->date,
            'receivedDate' => $request->date,
            'paymentDate' => $request->date,
            'nominal' => $totalNominal,
            'paidAmount' => $totalNominal,
            'userBankCode' => $request->userBankCode ?: null,
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

        // 8. Terapkan Mutasi Kas Baru jika Dipilih
        if (! empty($request->userBankCode) && $totalNominal > 0) {
            $userBank = UserBank::query()->where('code', $request->userBankCode)->first();
            if ($userBank) {
                $batchCode = GenerateCode::generateUniqueCode('FSP', 'supplier_payment_batch');
                $plateNumber = $fleet ? $fleet->plateNumber : $request->fleetCode;
                $desc = 'Pembelian Langsung Armada ' . $plateNumber . ' (' . $purchaseCode . ')';

                PurchasePaymentHistory::query()->create([
                    'purchaseCode' => $purchaseCode,
                    'batch_code' => $batchCode,
                    'amount' => $totalNominal,
                    'paymentDate' => $request->date,
                    'userBankCode' => $userBank->code,
                    'description' => $desc,
                ]);

                Mutation::query()->create([
                    'code' => GenerateCode::generateUniqueCode('FMT', 'mutation'),
                    'userBankCode' => $userBank->code,
                    'nominal' => $totalNominal,
                    'type' => 'Out',
                    'date' => $request->date,
                    'description' => $desc . ' sebesar Rp ' . number_format($totalNominal, 0, '.', ','),
                    'transactionCode' => $purchaseCode,
                    'transactionTypeCode' => self::TRANSACTION_TYPE,
                ]);

                $this->applyBankMovement($userBank->code, $totalNominal, 'out');
            }
        }

        $this->logActivity($title, $purchase, 'After Update');
    }

    public function destroy(string $id, string $title)
    {
        $purchase = $this->getById($id);
        if (! $purchase) {
            throw new \Exception('Data Pembelian Langsung tidak ditemukan.');
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

        // 3. Rollback Mutasi Kas & Bank jika ada
        $mutations = Mutation::query()->where('transactionCode', $purchaseCode)->get();
        foreach ($mutations as $mut) {
            $this->applyBankMovement($mut->userBankCode, (float) $mut->nominal, 'in');
            $mut->forceDelete();
        }
        PurchasePaymentHistory::query()->where('purchaseCode', $purchaseCode)->forceDelete();

        // 4. Hapus detail purchase & purchase
        $purchase->details()->delete();
        $purchase->delete();
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
