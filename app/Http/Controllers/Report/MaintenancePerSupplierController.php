<?php

namespace App\Http\Controllers\Report;

use App\Exports\MaintenanceSupplierDetailReport;
use App\Exports\MaintenanceSupplierReport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Supplier;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Services\Report\MaintenancePaymentStatusService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class MaintenancePerSupplierController extends Controller
{
    protected $title;

    protected $view;

    public function __construct(private readonly MaintenancePaymentStatusService $paymentStatus)
    {
        $this->title = 'Maintenance Per Supplier';
        $this->view = 'report.maintenance-supplier.';
    }

    public function index()
    {
        $suppliers = Supplier::query()->orderBy('name')->get(['code', 'name']);

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('suppliers', $suppliers)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $data = $this->paymentStatus->supplierSummaryQuery($this->summaryFilters($request));

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) use ($request) {
                $query = array_filter([
                    'supplierCode' => $row->supplierCode,
                    'startDate' => $request->startDate,
                    'endDate' => $request->endDate,
                ]);

                return '<a href="'.route('report.maintenance-supplier.detail', $query).'" class="btn btn-icon btn-sm bg-info-subtle" data-bs-toggle="tooltip" title="Detail">'
                    .'<i class="mdi mdi-eye-outline fs-14 text-info"></i>'
                    .'</a>';
            })
            ->editColumn('supplierName', fn ($row) => $row->supplierName ?: '-')
            ->editColumn('totalMaintenance', fn ($row) => number_format((float) $row->totalMaintenance, 0, ',', '.'))
            ->editColumn('totalQty', fn ($row) => number_format((float) $row->totalQty, 1, ',', '.'))
            ->editColumn('totalCost', fn ($row) => number_format((float) $row->totalCost, 0, ',', '.'))
            ->editColumn('utangJatuhTempo', fn ($row) => number_format((float) $row->utangJatuhTempo, 0, ',', '.'))
            ->editColumn('totalUtang', fn ($row) => number_format((float) $row->totalUtang, 0, ',', '.'))
            ->filterColumn('supplierName', fn (Builder $query, $keyword) => $query->where('supplier.name', 'like', "%{$keyword}%"))
            ->orderColumn('supplierName', 'supplier.name $1')
            ->orderColumn('totalMaintenance', 'totalMaintenance $1')
            ->orderColumn('totalQty', 'totalQty $1')
            ->orderColumn('totalCost', 'totalCost $1')
            ->orderColumn('utangJatuhTempo', 'utangJatuhTempo $1')
            ->orderColumn('totalUtang', 'totalUtang $1')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function detail(string $supplierCode, Request $request)
    {
        $supplier = $this->findSupplier($supplierCode);
        $filters = $this->detailFilters($request);
        $summary = $this->paymentStatus->summarize($this->paymentStatus->supplierDetailQuery($supplierCode, $filters));

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('title', 'Maintenance Detail Per Supplier')
            ->with('supplier', $supplier)
            ->with('fleetCompanies', FleetCompany::query()->orderBy('name')->get(['code', 'name', 'type']))
            ->with('fleets', Fleet::query()->orderBy('plateNumber')->get(['code', 'plateNumber']))
            ->with('summary', $summary)
            ->with('fleetCompanyCode', $filters['fleetCompanyCode'])
            ->with('fleetCode', $filters['fleetCode'])
            ->with('startDate', $filters['startDate'])
            ->with('endDate', $filters['endDate']);
    }

    public function datatableDetail(string $supplierCode, Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $data = $this->paymentStatus->orderLines(
            $this->paymentStatus->supplierDetailQuery($supplierCode, $this->detailFilters($request))
                ->select(array_merge(
                    $this->paymentStatus->lineSelects(),
                    [
                        'fleet_company.name as fleetCompanyName',
                        'fleet.plateNumber',
                    ]
                ))
        );

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('maintenanceDate', fn ($row) => Carbon::parse($row->date)->format('d-m-Y').' '.Carbon::parse($row->time)->format('H:i'))
            ->editColumn('itemName', fn ($row) => $row->itemName ?: '-')
            ->editColumn('fleetCompanyName', fn ($row) => $row->fleetCompanyName ?: '-')
            ->editColumn('plateNumber', fn ($row) => $row->plateNumber ?: '-')
            ->editColumn('qty', fn ($row) => number_format((float) $row->qty, 1, ',', '.'))
            ->editColumn('price', fn ($row) => 'Rp '.number_format((float) $row->price, 0, ',', '.'))
            ->editColumn('total', fn ($row) => 'Rp '.number_format((float) $row->total, 0, ',', '.'))
            ->editColumn('dueDate', fn ($row) => $row->dueDate ? Carbon::parse($row->dueDate)->format('d-m-Y') : '-')
            ->editColumn('paymentStatus', fn ($row) => MaintenancePaymentStatusService::statusBadge($row->paymentStatus))
            ->filterColumn('code', fn (Builder $query, $keyword) => $query->where('maintenance.code', 'like', "%{$keyword}%"))
            ->filterColumn('maintenanceDate', fn (Builder $query, $keyword) => $query->whereRaw("DATE_FORMAT(maintenance.date, '%d-%m-%Y') LIKE ?", ["%{$keyword}%"]))
            ->filterColumn('itemCode', fn (Builder $query, $keyword) => $query->where('maintenance_detail.itemCode', 'like', "%{$keyword}%"))
            ->filterColumn('itemName', fn (Builder $query, $keyword) => $query->where('item.name', 'like', "%{$keyword}%"))
            ->filterColumn('fleetCompanyName', fn (Builder $query, $keyword) => $query->where('fleet_company.name', 'like', "%{$keyword}%"))
            ->filterColumn('plateNumber', fn (Builder $query, $keyword) => $query->where('fleet.plateNumber', 'like', "%{$keyword}%"))
            ->filterColumn('qty', fn (Builder $query, $keyword) => $this->paymentStatus->filterNumber($query, 'maintenance_detail.qty', $keyword))
            ->filterColumn('price', fn (Builder $query, $keyword) => $this->paymentStatus->filterNumber($query, 'maintenance_detail.price', $keyword))
            ->filterColumn('total', fn (Builder $query, $keyword) => $this->paymentStatus->filterNumber($query, MaintenancePaymentStatusService::LINE_AMOUNT, $keyword))
            ->filterColumn('dueDate', fn (Builder $query, $keyword) => $this->paymentStatus->filterDueDate($query, $keyword))
            ->filterColumn('paymentStatus', fn (Builder $query, $keyword) => $this->paymentStatus->filterPaymentStatus($query, $keyword))
            ->orderColumn('code', 'maintenance.code $1')
            ->orderColumn('maintenanceDate', 'maintenance.date $1, maintenance.time $1')
            ->orderColumn('itemCode', 'maintenance_detail.itemCode $1')
            ->orderColumn('itemName', 'item.name $1')
            ->orderColumn('fleetCompanyName', 'fleet_company.name $1')
            ->orderColumn('plateNumber', 'fleet.plateNumber $1')
            ->orderColumn('qty', 'maintenance_detail.qty $1')
            ->orderColumn('price', 'maintenance_detail.price $1')
            ->orderColumn('total', 'total $1')
            ->orderColumn('dueDate', 'dueDate $1')
            ->orderColumn('paymentStatus', 'paymentStatus $1')
            ->rawColumns(['paymentStatus'])
            ->toJson();
    }

    public function pdfMaintenanceSupplier(Request $request)
    {
        $filters = $this->summaryFilters($request);
        $rows = $this->paymentStatus->supplierSummaryQuery($filters)->orderBy('supplier.name')->toBase()->get();

        $supplierName = $filters['supplierCode']
            ? Supplier::query()->where('code', $filters['supplierCode'])->value('name')
            : null;

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.maintenance-supplier-pdf')
                ->with('rows', $rows)
                ->with('supplierName', $supplierName)
                ->with('startDate', $filters['startDate'])
                ->with('endDate', $filters['endDate'])
        );

        return response($mpdf->Output('Maintenance-Supplier-Report.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Maintenance-Supplier-Report.pdf"',
        ]);
    }

    public function excelMaintenanceSupplier(Request $request)
    {
        return Excel::download(new MaintenanceSupplierReport($request), 'Maintenance-Supplier-Report.xlsx');
    }

    public function pdfMaintenanceSupplierDetail(string $supplierCode, Request $request)
    {
        $supplier = $this->findSupplier($supplierCode);
        $filters = $this->detailFilters($request);
        $query = $this->paymentStatus->supplierDetailQuery($supplierCode, $filters);
        $summary = $this->paymentStatus->summarize($query);
        $rows = $this->paymentStatus->orderLines(
            $query->select(array_merge(
                $this->paymentStatus->lineSelects(),
                [
                    'fleet_company.name as fleetCompanyName',
                    'fleet.plateNumber',
                ]
            ))
        )->toBase()->get();

        $fleetCompanyName = $filters['fleetCompanyCode']
            ? FleetCompany::query()->where('code', $filters['fleetCompanyCode'])->value('name')
            : null;

        $fleetPlate = $filters['fleetCode']
            ? Fleet::query()->where('code', $filters['fleetCode'])->value('plateNumber')
            : null;

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.maintenance-supplier-detail-pdf')
                ->with('supplier', $supplier)
                ->with('rows', $rows)
                ->with('summary', $summary)
                ->with('fleetCompanyName', $fleetCompanyName)
                ->with('fleetPlate', $fleetPlate)
                ->with('startDate', $filters['startDate'])
                ->with('endDate', $filters['endDate'])
        );

        $filename = 'Maintenance-Supplier-Detail-'.$supplier->code.'.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function excelMaintenanceSupplierDetail(string $supplierCode, Request $request)
    {
        $request->merge(['supplierCode' => $supplierCode]);

        $supplier = $this->findSupplier($supplierCode);

        return Excel::download(new MaintenanceSupplierDetailReport($request), 'Maintenance-Supplier-Detail-'.$supplier->code.'.xlsx');
    }

    private function findSupplier(string $supplierCode): Supplier
    {
        return Supplier::query()
            ->where('code', $supplierCode)
            ->whereNull('deleted_at')
            ->firstOrFail();
    }

    /**
     * @return array{supplierCode: ?string, startDate: ?string, endDate: ?string}
     */
    private function summaryFilters(Request $request): array
    {
        return [
            'supplierCode' => $request->input('supplierCode') ?: null,
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
        ];
    }

    /**
     * @return array{fleetCompanyCode: ?string, fleetCode: ?string, startDate: ?string, endDate: ?string}
     */
    private function detailFilters(Request $request): array
    {
        return [
            'fleetCompanyCode' => $request->input('fleetCompanyCode') ?: null,
            'fleetCode' => $request->input('fleetCode') ?: null,
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
        ];
    }
}
