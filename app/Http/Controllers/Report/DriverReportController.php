<?php

namespace App\Http\Controllers\Report;

use App\Exports\DriverListReportExport;
use App\Http\Controllers\Controller;
use App\Models\Master\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class DriverReportController extends Controller
{
    public const DRIVER_POSITION_CODES = ['KP_240823034043', 'FPS250612034049'];

    protected string $title = 'Daftar Supir';

    protected string $view = 'report.drivers.';

    public function index()
    {
        $base = Employee::query()->whereIn('positionCode', self::DRIVER_POSITION_CODES);

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('summary', [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('status', 1)->count(),
                'inactive' => (clone $base)->where('status', 0)->count(),
            ]);
    }

    public function datatable(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $query = $this->filteredQuery($this->extractFilters($request));

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', function (Employee $row) {
                $badge = $row->isActive()
                    ? '<span class="badge bg-success-subtle text-success ms-1" style="border-radius: 6px; font-size: 10px;">Aktif</span>'
                    : '<span class="badge bg-danger-subtle text-danger ms-1" style="border-radius: 6px; font-size: 10px;">Nonaktif</span>';

                return e($row->name ?: '-').' '.$badge;
            })
            ->editColumn('joinDate', fn (Employee $row) => $row->joinDate ? Carbon::parse($row->joinDate)->format('d-m-Y') : '-')
            ->editColumn('nik', fn (Employee $row) => $row->nik ?: '-')
            ->editColumn('sim', fn (Employee $row) => $row->sim ?: '-')
            ->editColumn('address', fn (Employee $row) => $row->address ?: '-')
            ->editColumn('phone', fn (Employee $row) => $row->phone ?: '-')
            ->addColumn('work_duration', fn (Employee $row) => $row->work_duration)
            ->filterColumn('joinDate', function (Builder $query, string $keyword) {
                $keyword = trim($keyword);
                if (in_array($keyword, ['-', '—'], true)) {
                    $query->whereNull('employee.joinDate');

                    return;
                }

                $query->where(function (Builder $q) use ($keyword) {
                    $q->whereRaw("DATE_FORMAT(employee.joinDate, '%d-%m-%Y') LIKE ?", ["%{$keyword}%"])
                        ->orWhere('employee.joinDate', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('work_duration', function (Builder $query, string $keyword) {
                $lower = mb_strtolower(trim($keyword));

                if ($lower === '' || in_array($lower, ['-', '—'], true)) {
                    $query->whereNull('employee.joinDate');

                    return;
                }

                if (str_contains($lower, '< 1') || $lower === '<') {
                    $query->whereNotNull('employee.joinDate')
                        ->whereRaw('TIMESTAMPDIFF(MONTH, employee.joinDate, CURDATE()) < 1');

                    return;
                }

                if (preg_match('/(\d+)\s*(tahun|thn)/u', $lower, $m) === 1) {
                    $query->whereNotNull('employee.joinDate')
                        ->whereRaw('TIMESTAMPDIFF(YEAR, employee.joinDate, CURDATE()) = ?', [(int) $m[1]]);

                    return;
                }

                if (preg_match('/(\d+)\s*(bulan|bln)/u', $lower, $m) === 1) {
                    $query->whereNotNull('employee.joinDate')
                        ->whereRaw('MOD(TIMESTAMPDIFF(MONTH, employee.joinDate, CURDATE()), 12) = ?', [(int) $m[1]]);

                    return;
                }

                if (preg_match('/^\d+$/', $lower) === 1) {
                    $num = (int) $lower;
                    $query->whereNotNull('employee.joinDate')
                        ->where(function (Builder $q) use ($num) {
                            $q->whereRaw('TIMESTAMPDIFF(YEAR, employee.joinDate, CURDATE()) = ?', [$num])
                                ->orWhereRaw('MOD(TIMESTAMPDIFF(MONTH, employee.joinDate, CURDATE()), 12) = ?', [$num]);
                        });

                    return;
                }

                if (str_contains('tahun', $lower)) {
                    $query->whereNotNull('employee.joinDate')
                        ->whereRaw('TIMESTAMPDIFF(YEAR, employee.joinDate, CURDATE()) >= 1');

                    return;
                }

                if (str_contains('bulan', $lower)) {
                    $query->whereNotNull('employee.joinDate')
                        ->whereRaw('(TIMESTAMPDIFF(MONTH, employee.joinDate, CURDATE()) < 1 OR MOD(TIMESTAMPDIFF(MONTH, employee.joinDate, CURDATE()), 12) > 0)');

                    return;
                }

                $query->whereRaw('1 = 0');
            })
            ->orderColumn('work_duration', function (Builder $query, string $order) {
                $dir = strtolower($order) === 'desc' ? 'asc' : 'desc';
                $query->orderByRaw("employee.joinDate IS NULL ASC, employee.joinDate {$dir}");
            })
            ->rawColumns(['name'])
            ->toJson();
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new DriverListReportExport($this->extractFilters($request)),
            'Daftar-Supir-'.date('Ymd').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->extractFilters($request);
        $rows = $this->filteredQuery($filters)->orderBy('name')->get();

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.drivers-pdf')
                ->with('rows', $rows)
                ->with('status', $filters['status'])
                ->with('startDate', $filters['startDate'])
                ->with('endDate', $filters['endDate'])
        );

        $filename = 'Daftar-Supir-'.date('Ymd').'.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * @param  array{status: ?string, startDate: ?string, endDate: ?string}  $filters
     */
    public function filteredQuery(array $filters): Builder
    {
        $query = Employee::query()
            ->select([
                'id',
                'code',
                'name',
                'joinDate',
                'nik',
                'sim',
                'address',
                'phone',
                'status',
            ])
            ->whereIn('positionCode', self::DRIVER_POSITION_CODES);

        if ($filters['status'] !== null && $filters['status'] !== '') {
            $query->where('status', (int) $filters['status']);
        }

        if ($filters['startDate']) {
            $query->whereDate('joinDate', '>=', $filters['startDate']);
        }

        if ($filters['endDate']) {
            $query->whereDate('joinDate', '<=', $filters['endDate']);
        }

        return $query;
    }

    /**
     * @return array{status: ?string, startDate: ?string, endDate: ?string}
     */
    private function extractFilters(Request $request): array
    {
        $status = $request->input('status');

        return [
            'status' => ($status !== null && $status !== '') ? (string) $status : null,
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
        ];
    }
}
