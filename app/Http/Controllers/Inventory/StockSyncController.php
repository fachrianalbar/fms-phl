<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\StockSyncService;
use Illuminate\Http\Request;

class StockSyncController extends Controller
{
    public function __construct(private readonly StockSyncService $stockSyncService) {}

    public function index()
    {
        return view('inventory.stock-sync.index', [
            'title' => 'Sinkronisasi Stock',
            'audit' => $this->stockSyncService->audit(),
        ]);
    }

    public function sync()
    {
        $result = $this->stockSyncService->sync();

        return redirect()
            ->route('inventory.stock-sync.index')
            ->with('success', "Sinkronisasi stock selesai. Insert: {$result['inserted']}, Update: {$result['updated']}, Hapus: {$result['deleted']}.");
    }

    public function prepare()
    {
        return response()->json([
            'success' => true,
            'data' => $this->stockSyncService->createPlan(),
        ]);
    }

    public function processChunk(Request $request)
    {
        $validated = $request->validate([
            'jobId' => ['required', 'string'],
            'offset' => ['required', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        try {
            return response()->json([
                'success' => true,
                'data' => $this->stockSyncService->processPlanChunk(
                    $validated['jobId'],
                    (int) $validated['offset'],
                    (int) ($validated['limit'] ?? 50)
                ),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
