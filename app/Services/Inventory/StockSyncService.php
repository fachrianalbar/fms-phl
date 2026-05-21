<?php

namespace App\Services\Inventory;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Item;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Models\Warehouse\MaintenanceDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockSyncService
{
    public function audit(): array
    {
        $expected = $this->expectedTransactions();
        $existingKeys = StockTransaction::query()
            ->whereNotNull('warehouseCode')
            ->where('warehouseCode', '!=', '')
            ->get([
                'id',
                'date',
                'itemCode',
                'warehouseCode',
                'qtyIn',
                'qtyOut',
                'transactionCode',
                'transactionDetailCode',
                'transactionType',
            ])
            ->keyBy(fn (StockTransaction $transaction) => $this->makeKey(
                $transaction->transactionCode,
                $transaction->transactionDetailCode,
                $transaction->itemCode
            ));

        $expectedKeys = $expected->keyBy('key');
        $missing = $expected
            ->reject(fn (array $row) => $existingKeys->has($row['key']))
            ->values();
        $mismatched = $expected
            ->filter(function (array $row) use ($existingKeys) {
                $transaction = $existingKeys->get($row['key']);

                return $transaction && $this->needsUpdate($transaction, $row);
            })
            ->map(function (array $row) use ($existingKeys) {
                $transaction = $existingKeys->get($row['key']);

                return array_merge($row, [
                    'id' => $transaction->id,
                    'currentWarehouseCode' => $transaction->warehouseCode,
                    'currentQtyIn' => (float) $transaction->qtyIn,
                    'currentQtyOut' => (float) $transaction->qtyOut,
                    'currentTransactionType' => $transaction->transactionType,
                    'currentDate' => $this->normalizeDate($transaction->date),
                ]);
            })
            ->values();

        $orphans = StockTransaction::query()
            ->with(['item', 'warehouse'])
            ->whereNotNull('warehouseCode')
            ->where('warehouseCode', '!=', '')
            ->where(function ($query) {
                $query->whereNull('transactionType')
                    ->orWhere('transactionType', '!=', 'INITIAL');
            })
            ->get()
            ->reject(function (StockTransaction $transaction) use ($expectedKeys) {
                return $expectedKeys->has($this->makeKey(
                    $transaction->transactionCode,
                    $transaction->transactionDetailCode,
                    $transaction->itemCode
                ));
            })
            ->map(fn (StockTransaction $transaction) => [
                'id' => $transaction->id,
                'source' => $transaction->transactionType === 'OUT' ? 'Maintenance' : 'Purchase',
                'transactionCode' => $transaction->transactionCode,
                'transactionDetailCode' => $transaction->transactionDetailCode,
                'itemCode' => $transaction->itemCode,
                'itemName' => $transaction->item->name ?? '-',
                'warehouseCode' => $transaction->warehouseCode,
                'warehouseName' => $transaction->warehouse->name ?? '-',
                'qtyIn' => (float) $transaction->qtyIn,
                'qtyOut' => (float) $transaction->qtyOut,
                'transactionType' => $transaction->transactionType ?? '-',
                'date' => $transaction->date,
            ])
            ->values();

        return [
            'missing' => $missing,
            'mismatched' => $mismatched,
            'orphans' => $orphans,
            'summary' => [
                'missing' => $missing->count(),
                'mismatched' => $mismatched->count(),
                'orphans' => $orphans->count(),
            ],
        ];
    }

    public function sync(): array
    {
        return DB::transaction(function () {
            $audit = $this->audit();

            foreach ($audit['missing'] as $row) {
                StockTransaction::create([
                    'code' => GenerateCode::generateCode('FST', true),
                    'itemCode' => $row['itemCode'],
                    'warehouseCode' => $row['warehouseCode'],
                    'transactionCode' => $row['transactionCode'],
                    'transactionDetailCode' => $row['transactionDetailCode'],
                    'qtyIn' => $row['qtyIn'],
                    'qtyOut' => $row['qtyOut'],
                    'date' => $row['date'],
                    'transactionType' => $row['transactionType'],
                ]);
            }

            foreach ($audit['mismatched'] as $row) {
                StockTransaction::query()
                    ->whereKey($row['id'])
                    ->update($this->stockTransactionPayload($row));
            }

            foreach ($audit['orphans'] as $row) {
                StockTransaction::query()
                    ->whereKey($row['id'])
                    ->delete();
            }

            return [
                'inserted' => $audit['summary']['missing'],
                'updated' => $audit['summary']['mismatched'],
                'deleted' => $audit['summary']['orphans'],
            ];
        });
    }

    public function createPlan(): array
    {
        $audit = $this->audit();
        $jobId = (string) Str::uuid();
        $actions = [];

        foreach ($audit['missing'] as $row) {
            $actions[] = [
                'type' => 'insert',
                'payload' => $this->stockTransactionPayload($row),
            ];
        }

        foreach ($audit['mismatched'] as $row) {
            $actions[] = [
                'type' => 'update',
                'id' => $row['id'],
                'payload' => $this->stockTransactionPayload($row),
            ];
        }

        foreach ($audit['orphans'] as $row) {
            $actions[] = [
                'type' => 'delete',
                'id' => $row['id'],
            ];
        }

        $plan = [
            'actions' => $actions,
            'summary' => $audit['summary'],
        ];

        Cache::put($this->planCacheKey($jobId), $plan, now()->addHour());

        return [
            'jobId' => $jobId,
            'total' => count($actions),
            'summary' => $audit['summary'],
        ];
    }

    public function processPlanChunk(string $jobId, int $offset, int $limit = 50): array
    {
        $plan = Cache::get($this->planCacheKey($jobId));

        if (! $plan) {
            throw new \InvalidArgumentException('Sync job sudah expired atau tidak ditemukan.');
        }

        $total = count($plan['actions']);
        $actions = array_slice($plan['actions'], $offset, $limit);
        $counts = [
            'inserted' => 0,
            'updated' => 0,
            'deleted' => 0,
        ];

        DB::transaction(function () use ($actions, &$counts) {
            foreach ($actions as $action) {
                if ($action['type'] === 'insert') {
                    $lookup = [
                        'transactionCode' => $action['payload']['transactionCode'],
                        'transactionDetailCode' => $action['payload']['transactionDetailCode'],
                        'itemCode' => $action['payload']['itemCode'],
                    ];
                    $transaction = StockTransaction::query()->firstOrNew($lookup);
                    $isNew = ! $transaction->exists;

                    if ($isNew) {
                        $transaction->code = GenerateCode::generateCode('FST', true);
                    }

                    $transaction->fill($action['payload']);
                    $transaction->save();

                    if ($isNew) {
                        $counts['inserted']++;
                    } else {
                        $counts['updated']++;
                    }

                    continue;
                }

                if ($action['type'] === 'update') {
                    StockTransaction::query()
                        ->whereKey($action['id'])
                        ->update($action['payload']);
                    $counts['updated']++;

                    continue;
                }

                if ($action['type'] === 'delete') {
                    StockTransaction::query()
                        ->whereKey($action['id'])
                        ->delete();
                    $counts['deleted']++;
                }
            }
        });

        $processed = min($offset + count($actions), $total);
        $done = $processed >= $total;

        if ($done) {
            Cache::forget($this->planCacheKey($jobId));
        }

        return [
            'processed' => $processed,
            'total' => $total,
            'percent' => $total > 0 ? (int) floor(($processed / $total) * 100) : 100,
            'done' => $done,
            'counts' => $counts,
        ];
    }

    private function expectedTransactions(): Collection
    {
        return $this->expectedPurchaseTransactions()
            ->merge($this->expectedMaintenanceTransactions())
            ->map(function (array $row) {
                $row['key'] = $this->makeKey(
                    $row['transactionCode'],
                    $row['transactionDetailCode'],
                    $row['itemCode']
                );

                return $row;
            })
            ->unique('key')
            ->values();
    }

    private function expectedPurchaseTransactions(): Collection
    {
        return PurchaseDetail::query()
            ->with(['item', 'purchase.warehouse'])
            ->whereNotNull('itemCode')
            ->whereHas('purchase', function ($query) {
                $query->whereNotNull('warehouseCode')
                    ->where('warehouseCode', '!=', '');
            })
            ->get()
            ->map(function (PurchaseDetail $detail) {
                $qty = $this->purchaseStockQty($detail);

                return [
                    'source' => 'Purchase',
                    'transactionCode' => $detail->purchaseCode,
                    'transactionDetailCode' => $detail->code,
                    'itemCode' => $detail->itemCode,
                    'itemName' => $detail->item->name ?? '-',
                    'warehouseCode' => $detail->purchase->warehouseCode ?? null,
                    'warehouseName' => $detail->purchase->warehouse->name ?? '-',
                    'qtyIn' => $qty,
                    'qtyOut' => 0,
                    'transactionType' => 'IN',
                    'date' => $this->sourceDate($detail->purchase?->date, $detail->created_at),
                ];
            });
    }

    private function expectedMaintenanceTransactions(): Collection
    {
        return MaintenanceDetail::query()
            ->with(['item', 'maintenance.warehouse'])
            ->whereNotNull('itemCode')
            ->whereHas('maintenance', function ($query) {
                $query->whereNotNull('warehouseCode')
                    ->where('warehouseCode', '!=', '');
            })
            ->get()
            ->reject(fn (MaintenanceDetail $detail) => $detail->item?->type === Item::TYPE_JASA)
            ->map(function (MaintenanceDetail $detail) {
                return [
                    'source' => 'Maintenance',
                    'transactionCode' => $detail->maintenanceCode,
                    'transactionDetailCode' => $detail->code,
                    'itemCode' => $detail->itemCode,
                    'itemName' => $detail->item->name ?? '-',
                    'warehouseCode' => $detail->maintenance->warehouseCode ?? null,
                    'warehouseName' => $detail->maintenance->warehouse->name ?? '-',
                    'qtyIn' => 0,
                    'qtyOut' => (float) $detail->qty,
                    'transactionType' => 'OUT',
                    'date' => $this->sourceDate($detail->maintenance?->date, $detail->created_at),
                ];
            });
    }

    private function makeKey(?string $transactionCode, ?string $transactionDetailCode, ?string $itemCode): string
    {
        return implode('|', [
            $transactionCode ?? '',
            $transactionDetailCode ?? '',
            $itemCode ?? '',
        ]);
    }

    private function stockTransactionPayload(array $row): array
    {
        return [
            'itemCode' => $row['itemCode'],
            'warehouseCode' => $row['warehouseCode'],
            'transactionCode' => $row['transactionCode'],
            'transactionDetailCode' => $row['transactionDetailCode'],
            'qtyIn' => $row['qtyIn'],
            'qtyOut' => $row['qtyOut'],
            'date' => $row['date'],
            'transactionType' => $row['transactionType'],
        ];
    }

    private function needsUpdate(StockTransaction $transaction, array $row): bool
    {
        return $transaction->warehouseCode !== $row['warehouseCode']
            || abs((float) $transaction->qtyIn - (float) $row['qtyIn']) > 0.0001
            || abs((float) $transaction->qtyOut - (float) $row['qtyOut']) > 0.0001
            || $transaction->transactionType !== $row['transactionType']
            || $this->normalizeDate($transaction->date) !== $this->normalizeDate($row['date']);
    }

    private function normalizeDate($date): ?string
    {
        if (! $date) {
            return null;
        }

        return substr((string) $date, 0, 10);
    }

    private function sourceDate($date, $fallback): ?string
    {
        if ($date) {
            return $date;
        }

        return $fallback?->format('Y-m-d');
    }

    private function purchaseStockQty(PurchaseDetail $detail): float
    {
        $qty = (float) $detail->qty;
        $receivedQty = $detail->receivedQty !== null ? (float) $detail->receivedQty : null;

        if ($receivedQty === null || $receivedQty <= 0) {
            return $qty;
        }

        if ($qty > 0 && $receivedQty > $qty) {
            return $qty;
        }

        return $receivedQty;
    }

    private function planCacheKey(string $jobId): string
    {
        return "stock-sync-plan:{$jobId}";
    }
}
