<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\StockTransaction;
use App\Services\Inventory\StockService;
use App\Services\Inventory\StockTransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Mpdf\Mpdf;


class StockTransactionController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;

    public function __construct(StockTransactionService $stockTransactionSvc, MenuService $menuSvc)
    {
        $this->service = $stockTransactionSvc;
        $this->title = "Stock Transaction";
        $this->view = "inventory.transaction-stock.";
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->service->findAll();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('data', $data)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();

            // Definisikan kolom filter dengan alias
            $filters = [
                'itemCode' => $request->itemCode,
                'purchase_data' => $request->purchaseCode,
                'maintenance_data' => $request->maintenanceCode,
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'purchase_data' => 'purchase.purchaseCode',
                'maintenance_data' => 'maintenance.maintenanceCode',
            ];

            $dateFilters = [
                'date' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('in', function ($row) {
                    $in = 0;

                    if ($row->type == 'IN') {
                        $in = $row->qty;
                    }
                    return $in;
                })
                ->addColumn('out', function ($row) {
                    $out = 0;

                    if ($row->type == 'OUT') {
                        $out = $row->qty;
                    }
                    return $out;
                })
                ->editColumn('item.name', function ($row) {
                    $item = '';

                    if (isset($row->item->name)) {
                        $item = $row->item->name;
                    }

                    return $item;
                })
                ->addColumn('bon', function ($row) {
                    $bon  = '';

                    if ($row->type == 'IN' && $row->transactionType == 1) {
                        $bon = $row->purchase->purchaseCode;
                    } else if ($row->type == 'OUT') {
                        $bon = $row->maintenance->maintenanceCode;
                    }

                    return $bon;
                })
                // ->addColumn('action', function ($row) {
                //     $btn = '<ul class="action">
                //                         <li class="edit"> <a href="' . route($this->view . 'edit', $row->id) . '"><i class="icon-pencil-alt"></i></a></li>
                //                         <li class="delete"><a href="javascript:deleteData(\'' . $row->id . '\')"><i class="icon-trash"></i></a></li>
                //                     </ul>';

                //     return $btn;
                // })
                ->rawColumns(['in', 'out', 'bon', 'item.name'])
                ->toJson();
        }
    }

    public function pdfStockTransaction(Request $request)
    {
        $data = StockTransaction::with([
            'item',
            'purchase',
            'maintenance'
        ])->orderBy('created_at', 'desc');

        // Definisikan kolom filter dengan alias
        $filters = [
            'itemCode' => $request->itemCode,
            'purchase_data' => $request->purchaseCode,
            'maintenance_data' => $request->maintenanceCode,
        ];

        // Hubungkan alias ke relasi dan kolom yang sesuai
        $relations = [
            'purchase_data' => 'purchase.purchaseCode',
            'maintenance_data' => 'maintenance.maintenanceCode',
        ];

        $dateFilters = [
            'date' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
            ]
        );

        $startDate = Carbon::parse($request->startDate)->format('d-m-Y');
        $endDate = Carbon::parse($request->endDate)->format('d-m-Y');

        $mpdf->WriteHTML(
            view($this->view . 'report.transaction-stock-pdf')
                ->with('data', $data)
                ->with('startDate', $startDate)
                ->with('endDate', $endDate)
        );

        return $mpdf->Output('Laporan Kartu Stock.pdf', 'I');
    }
}
