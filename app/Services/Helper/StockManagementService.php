<?php

namespace App\Services\Helper;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Stock;
use App\Models\StockTransaction;

class StockManagementService
{
    protected $stock;
    protected $stockTransaction;

    public function __construct(Stock $stock, StockTransaction $stockTransaction)
    {
        $this->stock = $stock;
        $this->stockTransaction = $stockTransaction;
    }

    /**
     * Create or update stock for IN transaction
     */
    public function processStockIn($itemCode, $warehouseCode, $qty, $transactionCode, $transactionDetailCode, $date, $type = 'IN')
    {
        // Create stock transaction
        $this->stockTransaction->create([
            'code' => GenerateCode::generateCode('ST'),
            'itemCode' => $itemCode,
            'warehouseCode' => $warehouseCode,
            'qtyIn' => $qty,
            'qtyOut' => 0,
            'transactionCode' => $transactionCode,
            'transactionDetailCode' => $transactionDetailCode,
            'date' => $date,
            'transactionType' => $type
        ]);

        // Update or create stock
        $stock = $this->stock->where('itemCode', $itemCode)
            ->first();

        if ($stock) {
            $stock->update([
                'stockIn' => $stock->stockIn + $qty
            ]);
        } else {
            $this->stock->create([
                'itemCode' => $itemCode,
                'stockIn' => $qty,
                'stockOut' => 0
            ]);
        }

        return $stock;
    }

    /**
     * Process stock for OUT transaction
     */
    public function processStockOut($itemCode, $warehouseCode, $qty, $transactionCode, $transactionDetailCode, $date, $type = 'OUT')
    {
        $stock = $this->stock->where('itemCode', $itemCode)
            ->first();

        if (!$stock || ($stock->stockIn - $stock->stockOut) < $qty) {
            throw new \Exception("Insufficient stock for item {$itemCode} in warehouse {$warehouseCode}");
        }

        // Create stock transaction
        $this->stockTransaction->create([
            'code' => GenerateCode::generateCode('FST'),
            'itemCode' => $itemCode,
            'warehouseCode' => $warehouseCode,
            'qtyOut' => $qty,
            'qtyIn' => 0,
            'transactionCode' => $transactionCode,
            'transactionDetailCode' => $transactionDetailCode,
            'date' => $date,
            'transactionType' => $type
        ]);

        // Update stock
        $stock->update([
            'stockOut' => $stock->stockOut + $qty
        ]);

        return $stock;
    }

    /**
     * Rollback stock transaction
     */
    public function rollbackStockTransaction($transactionCode, $transactionDetailCode, $itemCode, $warehouseCode)
    {
        $stockTransaction = $this->stockTransaction->where('transactionCode', $transactionCode)
            ->where('transactionDetailCode', $transactionDetailCode)
            ->where('itemCode', $itemCode)
            ->where('warehouseCode', $warehouseCode)
            ->first();

        if ($stockTransaction) {
            $stock = $this->stock->where('itemCode', $itemCode)
                ->first();

            if ($stock) {
                if ($stockTransaction->type === 'IN' || $stockTransaction->type === 'INITIAL') {
                    $stock->update([
                        'stockIn' => $stock->stockIn - $stockTransaction->qty
                    ]);
                } elseif ($stockTransaction->type === 'OUT') {
                    $stock->update([
                        'stockOut' => $stock->stockOut - $stockTransaction->qty
                    ]);
                }
            }

            $stockTransaction->delete();
        }
    }

    /**
     * Update stock transaction
     */
    public function updateStockTransaction($transactionCode, $transactionDetailCode, $itemCode, $warehouseCode, $newQty, $newItemCode = null, $newWarehouseCode = null, $newTransactionDetailCode = null)
    {
        $stockTransaction = $this->stockTransaction->where('transactionCode', $transactionCode)
            ->where('transactionDetailCode', $transactionDetailCode)
            ->where('itemCode', $itemCode)
            ->where('warehouseCode', $warehouseCode)
            ->first();

        if ($stockTransaction) {
            // Rollback old transaction
            $this->rollbackStockTransaction($transactionCode, $transactionDetailCode, $itemCode, $warehouseCode);

            // Create new transaction
            if ($stockTransaction->type === 'IN' || $stockTransaction->type === 'INITIAL') {
                $this->processStockIn(
                    $newItemCode ?? $itemCode,
                    $newWarehouseCode ?? $warehouseCode,
                    $newQty,
                    $transactionCode,
                    $newTransactionDetailCode ?? $transactionDetailCode,
                    $stockTransaction->date,
                    $stockTransaction->type
                );
            } elseif ($stockTransaction->type === 'OUT') {
                $this->processStockOut(
                    $newItemCode ?? $itemCode,
                    $newWarehouseCode ?? $warehouseCode,
                    $newQty,
                    $transactionCode,
                    $newTransactionDetailCode ?? $transactionDetailCode,
                    $stockTransaction->date,
                    $stockTransaction->type
                );
            }
        }
    }

    /**
     * Get available stock
     */
    public function getAvailableStock($itemCode)
    {
        $stock = $this->stock->where('itemCode', $itemCode)
            ->first();

        if ($stock) {
            return $stock->stockIn - $stock->stockOut;
        }

        return 0;
    }
}
