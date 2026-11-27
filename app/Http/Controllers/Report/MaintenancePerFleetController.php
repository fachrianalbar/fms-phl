<?php

namespace App\Http\Controllers\Report;

use App\Exports\MaintenanceFleetDetailReport;
use App\Exports\MaintenanceFleetReport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Supplier;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Warehouse\Maintenance;
use App\Services\Report\MaintenancePaymentStatusService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class MaintenancePerFleetController extends Controller
{
    protected $title;

    protected $view;

    public function __construct(private readonly MaintenancePaymentStatusService $paymentStatus)
    {
        $this->title = 'Maintenance Per Fleet';
        $this->view = 'report.maintenance-fleet.';
    }

    public function index()
    {
        $fleet = Fleet::query()->orderBy('plateNumber')->get();
        $fleetCompany = FleetCompany::query()->orderBy('name')->get();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('fleetCompany', $fleetCompany)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $data = $this->paymentStatus->fleetSummaryQuery($this->summaryFilters($request));

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) use ($request) {
                $query = array_filter([
                    'fleetCode' => $row->fleetCode,
                    'startDate' => $request->startDate,
                    'endDate' => $request->endDate,
                ]);

                return '<a href="'.route('report.maintenance-fleet.detail', $query).'" class="btn btn-icon btn-sm bg-info-subtle" data-bs-toggle="tooltip" title="Detail">'
                    .'<i class="mdi mdi-eye-outline fs-14 text-info"></i>'
                    .'</a>';
            })
            ->editColumn('fleetCompanyName', fn ($row) => $row->fleetCompanyName ?: '-')
            ->editColumn('fleetCompanyType', fn ($row) => $row->fleetCompanyType ?: 'Internal')
            ->editColumn('totalMaintenance', fn ($row) => number_format((float) $row->totalMaintenance, 0, ',', '.'))
            ->editColumn('totalQty', fn ($row) => number_format((float) $row->totalQty, 1, ',', '.'))
            ->editColumn('totalCost', fn ($row) => number_format((float) $row->totalCost, 0, ',', '.'))
            ->editColumn('utangJatuhTempo', fn ($row) => number_format((float) $row->utangJatuhTempo, 0, ',', '.'))
            ->editColumn('totalUtang', fn ($row) => number_format((float) $row->totalUtang, 0, ',', '.'))
            ->filterColumn('plateNumber', fn (Builder $query, $keyword) => $query->where('fleet.plateNumber', 'like', "%{$keyword}%"))
            ->filterColumn('fleetCompanyName', fn (Builder $query, $keyword) => $query->where('fleet_company.name', 'like', "%{$keyword}%"))
            ->filterColumn('fleetCompanyType', function (Builder $query, $keyword) {
                if (str_starts_with('internal', mb_strtolower(trim($keyword)))) {
                    $query->where(fn (Builder $q) => $q->where('fleet_company.type', 'like', "%{$keyword}%")->orWhereNull('fleet_company.type'));

                    return;
                }

                $query->where('fleet_company.type', 'like', "%{$keyword}%");
            })
            ->orderColumn('plateNumber', 'fleet.plateNumber $1')
            ->orderColumn('fleetCompanyName', 'fleet_company.name $1')
            ->orderColumn('fleetCompanyType', 'fleet_company.type $1')
            ->orderColumn('totalMaintenance', 'totalMaintenance $1')
            ->orderColumn('totalQty', 'totalQty $1')
            ->orderColumn('totalCost', 'totalCost $1')
            ->orderColumn('utangJatuhTempo', 'utangJatuhTempo $1')
            ->orderColumn('totalUtang', 'totalUtang $1')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function detail(string $fleetCode, Request $request)
    {
        $fleet = $this->findFleet($fleetCode);
        $filters = $this->detailFilters($request);
        $summary = $this->paymentStatus->summarize($this->paymentStatus->fleetDetailQuery($fleetCode, $filters));

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('title', 'Maintenance Detail Per Fleet')
            ->with('fleet', $fleet)
            ->with('suppliers', Supplier::query()->orderBy('name')->get(['code', 'name']))
            ->with('summary', $summary)
            ->with('supplierCode', $filters['supplierCode'])
            ->with('startDate', $filters['startDate'])
            ->with('endDate', $filters['endDate']);
    }

    public function pdfMaintenanceFleet(Request $request)
    {
        $filters = $this->summaryFilters($request);
        $rows = $this->paymentStatus->fleetSummaryQuery($filters)->orderBy('fleet.plateNumber')->toBase()->get();

        $fleetName = $request->filled('fleetCode')
            ? Fleet::query()->where('code', $request->fleetCode)->value('plateNumber')
            : null;

        $fleetCompanyName = $request->filled('fleetCompanyCode')
            ? FleetCompany::query()->where('code', $request->fleetCompanyCode)->value('name')
            : null;

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.maintenance-fleet-pdf')
                ->with('rows', $rows)
                ->with('fleetName', $fleetName)
                ->with('fleetCompanyName', $fleetCompanyName)
                ->with('startDate', $filters['startDate'])
                ->with('endDate', $filters['endDate'])
        );

        return response($mpdf->Output('Maintenance-Fleet-Report.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Maintenance-Fleet-Report.pdf"',
        ]);
    }

    public function excelMaintenanceFleet(Request $request)
    {
        return Excel::download(new MaintenanceFleetReport($request), 'Maintenance-Fleet-Report.xlsx');
    }

    public function pdfMaintenanceFleetDetail(string $fleetCode, Request $request)
    {
        $fleet = $this->findFleet($fleetCode);
        $filters = $this->detailFilters($request);
        $query = $this->paymentStatus->fleetDetailQuery($fleetCode, $filters);
        $summary = $this->paymentStatus->summarize($query);
        $rows = $this->paymentStatus->orderLines($query->select($this->paymentStatus->lineSelects()))->toBase()->get();

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.maintenance-fleet-detail-pdf')
                ->with('fleet', $fleet)
                ->with('rows', $rows)
                ->with('summary', $summary)
                ->with('supplierName', $this->supplierName($filters['supplierCode']))
                ->with('startDate', $filters['startDate'])
                ->with('endDate', $filters['endDate'])
        );

        $filename = 'Maintenance-Fleet-Detail-'.$fleet->plateNumber.'.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function excelMaintenanceFleetDetail(string $fleetCode, Request $request)
    {
        $request->merge(['fleetCode' => $fleetCode]);

        $plateNumber = Fleet::query()->where('code', $fleetCode)->value('plateNumber') ?? $fleetCode;

        return Excel::download(new MaintenanceFleetDetailReport($request), 'Maintenance-Fleet-Detail-'.$plateNumber.'.xlsx');
    }

    public function datatableDetail(string $fleetCode, Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $data = $this->paymentStatus->orderLines(
            $this->paymentStatus->fleetDetailQuery($fleetCode, $this->detailFilters($request))
                ->select($this->paymentStatus->lineSelects())
        );

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('maintenanceDate', fn ($row) => Carbon::parse($row->date)->format('d-m-Y').' '.Carbon::parse($row->time)->format('H:i'))
            ->editColumn('itemName', fn ($row) => $row->itemName ?: '-')
            ->editColumn('supplierName', fn ($row) => $row->supplierName ?: '-')
            ->editColumn('qty', fn ($row) => number_format((float) $row->qty, 1, ',', '.'))
            ->editColumn('price', fn ($row) => 'Rp '.number_format((float) $row->price, 0, ',', '.'))
            ->editColumn('total', fn ($row) => 'Rp '.number_format((float) $row->total, 0, ',', '.'))
            ->editColumn('dueDate', fn ($row) => $row->dueDate ? Carbon::parse($row->dueDate)->format('d-m-Y') : '-')
            ->editColumn('paymentStatus', fn ($row) => MaintenancePaymentStatusService::statusBadge($row->paymentStatus))
            ->filterColumn('code', fn (Builder $query, $keyword) => $query->where('maintenance.code', 'like', "%{$keyword}%"))
            ->filterColumn('maintenanceDate', fn (Builder $query, $keyword) => $query->whereRaw("DATE_FORMAT(maintenance.date, '%d-%m-%Y') LIKE ?", ["%{$keyword}%"]))
            ->filterColumn('itemCode', fn (Builder $query, $keyword) => $query->where('maintenance_detail.itemCode', 'like', "%{$keyword}%"))
            ->filterColumn('itemName', fn (Builder $query, $keyword) => $query->where('item.name', 'like', "%{$keyword}%"))
            ->filterColumn('supplierName', fn (Builder $query, $keyword) => $query->where('supplier.name', 'like', "%{$keyword}%"))
            ->filterColumn('qty', fn (Builder $query, $keyword) => $this->paymentStatus->filterNumber($query, 'maintenance_detail.qty', $keyword))
            ->filterColumn('price', fn (Builder $query, $keyword) => $this->paymentStatus->filterNumber($query, 'maintenance_detail.price', $keyword))
            ->filterColumn('total', fn (Builder $query, $keyword) => $this->paymentStatus->filterNumber($query, MaintenancePaymentStatusService::LINE_AMOUNT, $keyword))
            ->filterColumn('dueDate', fn (Builder $query, $keyword) => $this->paymentStatus->filterDueDate($query, $keyword))
            ->filterColumn('paymentStatus', fn (Builder $query, $keyword) => $this->paymentStatus->filterPaymentStatus($query, $keyword))
            ->orderColumn('code', 'maintenance.code $1')
            ->orderColumn('maintenanceDate', 'maintenance.date $1, maintenance.time $1')
            ->orderColumn('itemCode', 'maintenance_detail.itemCode $1')
            ->orderColumn('itemName', 'item.name $1')
            ->orderColumn('supplierName', 'supplier.name $1')
            ->orderColumn('qty', 'maintenance_detail.qty $1')
            ->orderColumn('price', 'maintenance_detail.price $1')
            ->orderColumn('total', 'total $1')
            ->orderColumn('dueDate', 'dueDate $1')
            ->orderColumn('paymentStatus', 'paymentStatus $1')
            ->rawColumns(['paymentStatus'])
            ->toJson();
    }

    public function detailItems(string $maintenanceCode)
    {
        $maintenance = Maintenance::query()
            ->with([
                'warehouse',
                'details',
                'details.item',
                'details.item.supplier',
            ])
            ->where('code', $maintenanceCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $details = $maintenance->details->map(function ($detail) {
            $price = (float) ($detail->price ?? 0);
            $qty = (float) $detail->qty;

            return [
                'itemCode' => $detail->itemCode,
                'itemName' => $detail->item?->name ?? '-',
                'supplierName' => $detail->item?->supplier?->name ?? '-',
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ];
        })->values();

        return response()->json([
            'code' => $maintenance->code,
            'maintenanceDate' => Carbon::parse($maintenance->date)->format('d-m-Y').' '.Carbon::parse($maintenance->time)->format('H:i'),
            'warehouse' => $maintenance->warehouse?->name ?? '-',
            'details' => $details,
            'totalQty' => (float) $details->sum('qty'),
            'totalCost' => (float) ($maintenance->grand_total ?? 0),
        ]);
    }

    private function findFleet(string $fleetCode): Fleet
    {
        return Fleet::query()
            ->with(['company'])
            ->where('code', $fleetCode)
            ->whereNull('deleted_at')
            ->firstOrFail();
    }

    /**
     * @return array{fleetCode: ?string, fleetCompanyCode: ?string, startDate: ?string, endDate: ?string}
     */
    private function summaryFilters(Request $request): array
    {
        return [
            'fleetCode' => $request->input('fleetCode') ?: null,
            'fleetCompanyCode' => $request->input('fleetCompanyCode') ?: null,
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
        ];
    }

    /**
     * @return array{supplierCode: ?string, startDate: ?string, endDate: ?string}
     */
    private function detailFilters(Request $request): array
    {
        return [
            'supplierCode' => $request->input('supplierCode') ?: null,
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
        ];
    }

    private function supplierName(?string $supplierCode): ?string
    {
        return $supplierCode ? Supplier::query()->where('code', $supplierCode)->value('name') : null;
    }
}
