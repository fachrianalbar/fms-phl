<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\StockSyncExport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\StockSyncService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class StockSyncController extends Controller
{
    public function __construct(private readonly StockSyncService $stockSyncService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['warehouseCode', 'itemCode', 'issueType', 'source']);
        $audit = $this->stockSyncService->filterAudit($this->stockSyncService->audit(), $filters);

        return view('inventory.stock-sync.index', [
            'title' => 'Sinkronisasi Stock',
            'audit' => $audit,
            'filters' => $filters,
            'warehouses' => Warehouse::query()->orderBy('name')->get(['code', 'name']),
            'items' => Item::query()->orderBy('code')->get(['code', 'name']),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['warehouseCode', 'itemCode', 'issueType', 'source']);
        $audit = $this->stockSyncService->filterAudit($this->stockSyncService->audit(), $filters);

        return Excel::download(
            new StockSyncExport($audit),
            'Sinkronisasi-Stok-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['warehouseCode', 'itemCode', 'issueType', 'source']);
        $audit = $this->stockSyncService->filterAudit($this->stockSyncService->audit(), $filters);

        $filterSummary = [];
        if (! empty($filters['warehouseCode'])) {
            $whName = Warehouse::query()->where('code', $filters['warehouseCode'])->value('name');
            $filterSummary[] = 'Gudang: '.($whName ?: $filters['warehouseCode']);
        }
        if (! empty($filters['itemCode'])) {
            $itemName = Item::query()->where('code', $filters['itemCode'])->value('name');
            $filterSummary[] = 'Barang: '.$filters['itemCode'].($itemName ? ' - '.$itemName : '');
        }
        if (! empty($filters['issueType'])) {
            $issueLabel = match ($filters['issueType']) {
                'missing' => 'Missing',
                'mismatched' => 'Tidak Sinkron',
                'orphans' => 'Orphan',
                default => $filters['issueType'],
            };
            $filterSummary[] = 'Kategori: '.$issueLabel;
        }
        if (! empty($filters['source'])) {
            $filterSummary[] = 'Sumber: '.$filters['source'];
        }

        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'default_font' => 'sans-serif',
        ]);

        $mpdf->WriteHTML(view('inventory.stock-sync.report.stock-sync-pdf', [
            'title' => 'Laporan Audit Sinkronisasi Stok',
            'audit' => $audit,
            'filterSummary' => $filterSummary,
            'printedAt' => now(),
        ])->render());

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Sinkronisasi-Stok-'.now()->format('YmdHis').'.pdf"');
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
