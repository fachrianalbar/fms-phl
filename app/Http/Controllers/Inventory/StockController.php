<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Mpdf\Mpdf;

class StockController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;

    public function __construct(StockService $stockSvc, MenuService $menuSvc)
    {
        $this->service = $stockSvc;
        $this->title = "Stock";
        $this->view = "inventory.stock.";
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stock = $this->service->findAll();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('stock', $stock)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();

            // Definisikan kolom filter dengan alias
            $filters = [
                'itemCode' => $request->itemCode,
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [];

            $dateFilters = [];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('item.name', function ($row) {
                    $item = '';

                    if (isset($row->item->name)) {
                        $item = $row->item->name;
                    }

                    return $item;
                })
                ->addColumn('total', function ($row) {
                    $total = $row->stockIn - $row->stockOut;

                    return $total;
                })
                ->rawColumns(['item.name', 'total'])
                ->toJson();
        }
    }

    public function pdfStock(Request $request)
    {
        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
            ]
        );

        $data = $this->service->findAll();

        $mpdf->WriteHTML(
            view($this->view . 'report.stock-pdf')
                ->with('data', $data)
        );

        return $mpdf->Output('Laporan Stock.pdf', 'I');
    }
}
