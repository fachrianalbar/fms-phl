<?php

namespace App\Http\Controllers\Report;

use App\Helpers\FilterHelper;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Master\Employee;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Models\Operational\OrderDriverSalary;
use App\Models\Report\DriverSalary;
use App\Models\Report\DriverSalaryDetail;
use App\Services\Master\EmployeeService;
use App\Services\Master\FleetService;
use App\Services\Operational\OrderDriverSalaryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class DriverSalaryController extends Controller
{
    protected $title;

    protected $view;

    protected $driverSvc;

    protected $fleetSvc;

    public function __construct(
        EmployeeService $driverSvc,
        FleetService $fleetSvc,
    ) {
        $this->title = 'Driver Salary';
        $this->view = 'report.driver-salary.';
        $this->driverSvc = $driverSvc;
        $this->fleetSvc = $fleetSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $driver = $this->driverSvc->findAll();
        $fleet = $this->fleetSvc->findAll();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('driver', $driver)
            ->with('fleet', $fleet)
            ->with('title', $this->title);
    }

    /**
     * Build the base query for unpaid driver salary report.
     * Returns orders that have at least one OrderDriverSalary with status = '0'.
     */
    private function buildSalaryQuery(Request $request)
    {
        $query = Order::with(['fleet', 'driver', 'route.originLocation', 'route.destinationLocation'])
            ->whereHas('orderDriverSalaries', function ($q) {
                $q->where('status', '0');
            })
            ->whereNull('order.deleted_at');

        // Apply filters
        $filters = [
            'driverCode' => $request->driverCode,
            'fleetCode'  => $request->fleetCode,
        ];

        $dateFilters = [
            'orderDate' => [
                'start' => $request->startDate,
                'end'   => $request->endDate,
            ],
        ];

        $query = FilterHelper::applyFilters($query, $filters, [], $dateFilters);

        return $query->orderBy('driverCode')->orderBy('orderDate', 'asc');
    }

    /**
     * Get unpaid salary total for a single order from order_driver_salary.
     */
    private function getSalaryTotal($orderCode)
    {
        return OrderDriverSalary::where('status', '0')
            ->whereHas('order', function ($q) use ($orderCode) {
                $q->where('code', $orderCode);
            })
            ->sum('amount');
    }

    /**
     * AJAX datatable endpoint - returns flat order rows for DataTable rendering.
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->buildSalaryQuery($request);

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->addColumn('driverName', function ($row) {
                    return $row->driver->name ?? '-';
                })
                ->addColumn('plateNumber', function ($row) {
                    return $row->fleet->plateNumber ?? '-';
                })
                ->addColumn('routeName', function ($row) {
                    if (! $row->route) {
                        return '-';
                    }
                    $origin = $row->route->originLocation->name ?? '';
                    $dest   = $row->route->destinationLocation->name ?? '';

                    return $row->route->name . ' (' . $origin . ' - ' . $dest . ')';
                })
                ->addColumn('salaryTotal', function ($row) {
                    $total = $this->getSalaryTotal($row->code);

                    return number_format($total, 0, ',', '.');
                })
                ->addColumn('salaryTotalRaw', function ($row) {
                    return $this->getSalaryTotal($row->code);
                })
                ->rawColumns(['driverName', 'plateNumber', 'routeName', 'salaryTotal'])
                ->toJson();
        }
    }

    /**
     * Store a new processed driver salary.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driverCode' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'adjustments' => 'nullable|array',
            'adjustments.*.date' => 'required|date',
            'adjustments.*.description' => 'required|string',
            'adjustments.*.type' => 'required|in:addition,deduction',
            'adjustments.*.nominal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->route('report.driver-salary.index')
                ->with('fail', $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($request->startDate)->format('Y-m-d');
            $endDate = Carbon::parse($request->endDate)->format('Y-m-d');
            $employee = Employee::where('code', $request->driverCode)->first();
            $driverId = $employee?->id;

            // Calculate total salary directly from order_driver_salary (status = 0)
            $orderDriverSalaries = OrderDriverSalary::with('order')
                ->where('status', '0')
                ->where(function ($q) use ($driverId, $request) {
                    if ($driverId) {
                        $q->where('driver_id', $driverId);
                    } else {
                        $q->whereHas('driver', function ($q2) use ($request) {
                            $q2->where('code', $request->driverCode);
                        });
                    }
                })
                ->whereHas('order', function ($q) use ($startDate, $endDate) {
                    $q->whereDate('orderDate', '>=', $startDate)
                      ->whereDate('orderDate', '<=', $endDate)
                      ->whereNull('deleted_at');
                })
                ->get();

            $totalSalary = $orderDriverSalaries->sum('amount');

            // Calculate total adjustment
            $totalAdjustment = 0;
            $adjustments = $request->adjustments ?? [];
            foreach ($adjustments as $adj) {
                if ($adj['type'] === 'addition') {
                    $totalAdjustment += floatval($adj['nominal']);
                } else {
                    $totalAdjustment -= floatval($adj['nominal']);
                }
            }

            $grandTotal = $totalSalary + $totalAdjustment;

            // Create driver salary record
            $driverSalary = DriverSalary::create([
                'code' => GenerateCode::generateCode('DS'),
                'driverCode' => $request->driverCode,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'totalSalary' => $totalSalary,
                'totalAdjustment' => $totalAdjustment,
                'grandTotal' => $grandTotal,
                'notes' => $request->notes,
            ]);

            // Create adjustment details
            foreach ($adjustments as $adj) {
                DriverSalaryDetail::create([
                    'code' => GenerateCode::generateCode('DSD', true),
                    'driverSalaryCode' => $driverSalary->code,
                    'date' => $adj['date'],
                    'description' => $adj['description'],
                    'type' => $adj['type'],
                    'nominal' => floatval($adj['nominal']),
                ]);
            }

            // Update order_driver_salary: set status = '1' and link driver_salary_id
            OrderDriverSalary::whereIn('id', $orderDriverSalaries->pluck('id'))->update([
                'status' => '1',
                'driver_salary_id' => $driverSalary->id,
            ]);

            DB::commit();

            return redirect()->route('report.driver-salary.index')
                ->with('success', 'Gaji driver berhasil diproses. Kode: ' . $driverSalary->code);
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route('report.driver-salary.index')
                ->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Show processed salary detail.
     */
    public function show($id)
    {
        $salary = DriverSalary::with(['driver', 'details'])->findOrFail($id);

        // Get all order_driver_salary linked to this salary slip
        $orderDriverSalaries = OrderDriverSalary::with(['order.fleet', 'order.route.originLocation', 'order.route.destinationLocation', 'costComponent'])
            ->where('driver_salary_id', $salary->id)
            ->get();

        // Fallback for legacy records
        if ($orderDriverSalaries->isEmpty()) {
            $employee = Employee::where('code', $salary->driverCode)->first();
            $orderDriverSalaries = OrderDriverSalary::with(['order.fleet', 'order.route.originLocation', 'order.route.destinationLocation', 'costComponent'])
                ->where('driver_id', $employee?->id)
                ->whereHas('order', function ($q) use ($salary) {
                    $q->whereDate('orderDate', '>=', $salary->startDate)
                      ->whereDate('orderDate', '<=', $salary->endDate)
                      ->whereNull('deleted_at');
                })
                ->get();
        }

        $groupedOrders = $orderDriverSalaries->groupBy('order_id');
        $orders = collect([]);
        foreach ($groupedOrders as $orderId => $items) {
            $order = $items->first()->order;
            if ($order) {
                $order->salaryAmount = $items->sum('amount');
                $orders->push($order);
            }
        }

        return view($this->view . 'show')
            ->with('salary', $salary)
            ->with('orders', $orders)
            ->with('title', $this->title . ' - Detail');
    }

    /**
     * Delete a processed salary.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $salary = DriverSalary::findOrFail($id);

            // Revert linked order_driver_salary status back to 0
            OrderDriverSalary::where('driver_salary_id', $salary->id)->update([
                'status' => '0',
                'driver_salary_id' => null,
            ]);

            // Delete details first
            DriverSalaryDetail::where('driverSalaryCode', $salary->code)->delete();
            $salary->delete();

            DB::commit();

            return redirect()->route('report.driver-salary.index')
                ->with('success', 'Data gaji berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route('report.driver-salary.index')
                ->with('fail', 'Gagal menghapus: ' . $th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $salary = DriverSalary::with(['driver', 'details'])->findOrFail($id);

        // Fetch order_driver_salary records linked to this salary slip
        $orderDriverSalaries = OrderDriverSalary::with(['order.fleet', 'order.route.originLocation', 'order.route.destinationLocation'])
            ->where('driver_salary_id', $salary->id)
            ->get();

        // Fallback for legacy
        if ($orderDriverSalaries->isEmpty()) {
            $employee = Employee::where('code', $salary->driverCode)->first();
            $orderDriverSalaries = OrderDriverSalary::with(['order.fleet', 'order.route.originLocation', 'order.route.destinationLocation'])
                ->where('driver_id', $employee?->id)
                ->whereHas('order', function ($q) use ($salary) {
                    $q->whereDate('orderDate', '>=', $salary->startDate)
                      ->whereDate('orderDate', '<=', $salary->endDate)
                      ->whereNull('deleted_at');
                })
                ->get();
        }

        $groupedOrders = $orderDriverSalaries->groupBy('order_id');
        $orderList = [];
        foreach ($groupedOrders as $orderId => $items) {
            $order = $items->first()->order;
            if (! $order) continue;

            $salaryAmount = $items->sum('amount');
            $routeName = '-';
            if ($order->route) {
                $origin = $order->route->originLocation->name ?? '';
                $dest = $order->route->destinationLocation->name ?? '';
                $routeName = $order->route->name . ' (' . $origin . ' - ' . $dest . ')';
            }

            $orderList[] = [
                'orderCode' => $order->code,
                'orderDate' => Carbon::parse($order->orderDate)->format('d-m-Y'),
                'plateNumber' => $order->fleet->plateNumber ?? '-',
                'routeName' => $routeName,
                'salary' => $salaryAmount,
                'salaryFormatted' => number_format($salaryAmount, 0, ',', '.'),
            ];
        }

        return response()->json([
            'salary' => $salary,
            'orders' => $orderList,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'driverCode' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'adjustments' => 'nullable|array',
            'adjustments.*.date' => 'required|date',
            'adjustments.*.description' => 'required|string',
            'adjustments.*.type' => 'required|in:addition,deduction',
            'adjustments.*.nominal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($request->startDate)->format('Y-m-d');
            $endDate = Carbon::parse($request->endDate)->format('Y-m-d');
            $driverSalary = DriverSalary::findOrFail($id);
            $employee = Employee::where('code', $request->driverCode)->first();
            $driverId = $employee?->id;

            // 1. Release previous order_driver_salary records
            OrderDriverSalary::where('driver_salary_id', $driverSalary->id)->update([
                'status' => '0',
                'driver_salary_id' => null,
            ]);

            // 2. Fetch order_driver_salary records for new driver/period
            $orderDriverSalaries = OrderDriverSalary::where(function ($q) use ($driverId, $request) {
                    if ($driverId) {
                        $q->where('driver_id', $driverId);
                    } else {
                        $q->whereHas('driver', function ($q2) use ($request) {
                            $q2->where('code', $request->driverCode);
                        });
                    }
                })
                ->whereHas('order', function ($q) use ($startDate, $endDate) {
                    $q->whereDate('orderDate', '>=', $startDate)
                      ->whereDate('orderDate', '<=', $endDate)
                      ->whereNull('deleted_at');
                })
                ->get();

            $totalSalary = $orderDriverSalaries->sum('amount');

            // Calculate total adjustment
            $totalAdjustment = 0;
            $adjustments = $request->adjustments ?? [];
            foreach ($adjustments as $adj) {
                if ($adj['type'] === 'addition') {
                    $totalAdjustment += floatval($adj['nominal']);
                } else {
                    $totalAdjustment -= floatval($adj['nominal']);
                }
            }

            $grandTotal = $totalSalary + $totalAdjustment;

            // Update main record
            $driverSalary->update([
                'driverCode' => $request->driverCode,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalSalary' => $totalSalary,
                'totalAdjustment' => $totalAdjustment,
                'grandTotal' => $grandTotal,
                'notes' => $request->notes,
            ]);

            // Re-sync details: delete existing ones and create new ones
            DriverSalaryDetail::where('driverSalaryCode', $driverSalary->code)->delete();

            foreach ($adjustments as $adj) {
                DriverSalaryDetail::create([
                    'code' => GenerateCode::generateCode('DSD', true),
                    'driverSalaryCode' => $driverSalary->code,
                    'date' => $adj['date'],
                    'description' => $adj['description'],
                    'type' => $adj['type'],
                    'nominal' => floatval($adj['nominal']),
                ]);
            }

            // 3. Mark new order_driver_salary records as status = 1 and link driver_salary_id
            OrderDriverSalary::whereIn('id', $orderDriverSalaries->pluck('id'))->update([
                'status' => '1',
                'driver_salary_id' => $driverSalary->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gaji driver berhasil diperbarui. Kode: ' . $driverSalary->code
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX datatable for processed salaries.
     */
    public function datatableProcessed(Request $request)
    {
        if ($request->ajax()) {
            $query = DriverSalary::with('driver');

            if ($request->filled('filterDriverCode')) {
                $query->where('driverCode', $request->filterDriverCode);
            }

            if ($request->filled('filterStartDate')) {
                $query->whereDate('startDate', '>=', $request->filterStartDate);
            }

            if ($request->filled('filterEndDate')) {
                $query->whereDate('endDate', '<=', $request->filterEndDate);
            }

            $query->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('code', function ($row) {
                    return '<span class="badge bg-light text-primary border fw-bold" style="font-family:monospace; font-size:12px; padding:6px 10px; border-radius:6px;">' . e($row->code) . '</span>';
                })
                ->addColumn('driverName', function ($row) {
                    $name = e($row->driver->name ?? '-');
                    $initials = strtoupper(substr($name, 0, 2));
                    return '<div class="d-flex align-items-center gap-2">
                                <div class="avatar-badge-sm">' . $initials . '</div>
                                <div class="fw-bold text-dark" style="font-size:13.5px;">' . $name . '</div>
                            </div>';
                })
                ->filterColumn('driverName', function ($query, $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->whereHas('driver', function ($q2) use ($keyword) {
                            $q2->where('name', 'like', "%{$keyword}%");
                        })->orWhere('driverCode', 'like', "%{$keyword}%")
                          ->orWhere('code', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('periode', function ($row) {
                    $start = Carbon::parse($row->startDate)->format('d/m/Y');
                    $end = Carbon::parse($row->endDate)->format('d/m/Y');
                    return '<span class="badge bg-light text-dark border fw-normal" style="font-size:11.5px; padding:5px 10px; border-radius:6px;">
                                <i class="mdi mdi-calendar-range text-primary me-1"></i> ' . $start . ' &ndash; ' . $end . '
                            </span>';
                })
                ->addColumn('totalSalaryFormatted', function ($row) {
                    return '<span class="fw-semibold text-secondary" style="font-size:13.5px;">Rp ' . number_format($row->totalSalary, 0, ',', '.') . '</span>';
                })
                ->addColumn('totalAdjustmentFormatted', function ($row) {
                    if ($row->totalAdjustment > 0) {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:11.5px;">+Rp ' . number_format($row->totalAdjustment, 0, ',', '.') . '</span>';
                    } elseif ($row->totalAdjustment < 0) {
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size:11.5px;">-Rp ' . number_format(abs($row->totalAdjustment), 0, ',', '.') . '</span>';
                    }
                    return '<span class="text-muted" style="font-size:12px;">Rp 0</span>';
                })
                ->addColumn('grandTotalFormatted', function ($row) {
                    return '<span class="fw-bold text-indigo" style="color:#4f46e5; font-size:14px;">Rp ' . number_format($row->grandTotal, 0, ',', '.') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('report.driver-salary.show', $row->id);
                    $pdfUrl = route('report.driver-salary.pdf-processed', $row->id);
                    $buttons = '<div class="btn-group btn-group-sm" role="group">';
                    $buttons .= '<a href="' . $showUrl . '" class="btn btn-outline-primary btn-icon" title="Lihat Detail"><i class="mdi mdi-eye"></i></a>';
                    $buttons .= '<a href="' . $pdfUrl . '" target="_blank" class="btn btn-outline-danger btn-icon" title="Cetak Slip PDF"><i class="mdi mdi-file-pdf-box"></i></a>';
                    $buttons .= '<button type="button" onclick="editSalary(\'' . $row->id . '\')" class="btn btn-outline-success btn-icon" title="Edit Gaji"><i class="mdi mdi-pencil"></i></button>';
                    $buttons .= '<button type="button" onclick="deleteSalary(\'' . $row->id . '\')" class="btn btn-outline-warning btn-icon" title="Hapus Gaji"><i class="mdi mdi-delete"></i></button>';
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['code', 'driverName', 'periode', 'totalSalaryFormatted', 'totalAdjustmentFormatted', 'grandTotalFormatted', 'action'])
                ->toJson();
        }
    }

    /**
     * AJAX - Get order salary data for preview before processing.
     * Reads directly from order_driver_salary where status = '0' (unpaid).
     */
    public function getOrderSalary(Request $request)
    {
        $driverCode = $request->driverCode;
        $rawStart = $request->startDate;
        $rawEnd = $request->endDate;

        if (!$driverCode || !$rawStart || !$rawEnd) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 422);
        }

        try {
            $startDate = Carbon::parse($rawStart)->format('Y-m-d');
            $endDate = Carbon::parse($rawEnd)->format('Y-m-d');
        } catch (\Exception $e) {
            $startDate = $rawStart;
            $endDate = $rawEnd;
        }

        $employee = Employee::where('code', $driverCode)->first();
        $driverId = $employee?->id;

        // Auto-sync any existing orders in this range on-the-fly
        $ordersToSync = Order::where('driverCode', $driverCode)
            ->whereDate('orderDate', '>=', $startDate)
            ->whereDate('orderDate', '<=', $endDate)
            ->whereNull('deleted_at')
            ->get();

        foreach ($ordersToSync as $o) {
            OrderDriverSalaryService::syncForOrder($o);
        }

        // Fetch unpaid salary components from order_driver_salary
        $orderDriverSalaries = OrderDriverSalary::with(['order.fleet', 'order.route.originLocation', 'order.route.destinationLocation', 'costComponent'])
            ->where('status', '0')
            ->where(function ($q) use ($driverId, $driverCode) {
                if ($driverId) {
                    $q->where('driver_id', $driverId);
                } else {
                    $q->whereHas('driver', function ($q2) use ($driverCode) {
                        $q2->where('code', $driverCode);
                    });
                }
            })
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereDate('orderDate', '>=', $startDate)
                  ->whereDate('orderDate', '<=', $endDate)
                  ->whereNull('deleted_at');
            })
            ->get();

        $groupedByOrder = $orderDriverSalaries->groupBy('order_id');
        $result = [];
        $totalSalary = 0;

        foreach ($groupedByOrder as $orderId => $items) {
            $order = $items->first()->order;
            if (! $order) {
                continue;
            }

            $salary = $items->sum('amount');
            $totalSalary += $salary;

            $routeName = '-';
            if ($order->route) {
                $origin = $order->route->originLocation->name ?? '';
                $dest = $order->route->destinationLocation->name ?? '';
                $routeName = $order->route->name . ' (' . $origin . ' - ' . $dest . ')';
            }

            $result[] = [
                'orderCode' => $order->code,
                'shipmentNumber' => $order->shipmentNumber ?? '-',
                'orderDate' => Carbon::parse($order->orderDate)->format('d-m-Y'),
                'plateNumber' => $order->fleet->plateNumber ?? '-',
                'routeName' => $routeName,
                'salary' => $salary,
                'salaryFormatted' => number_format($salary, 0, ',', '.'),
            ];
        }

        return response()->json([
            'orders' => $result,
            'totalSalary' => $totalSalary,
            'totalSalaryFormatted' => number_format($totalSalary, 0, ',', '.'),
        ]);
    }

    /**
     * AJAX - Get processed salary detail.
     */
    public function getDetail($id)
    {
        $salary = DriverSalary::with(['driver', 'details'])->findOrFail($id);

        return response()->json($salary);
    }

    /**
     * Generate PDF Slip Gaji per driver (from report filter - original behavior).
     */
    public function pdfDriverSalary(Request $request)
    {
        $query = $this->buildSalaryQuery($request);
        $orders = $query->get();

        // Group by driverCode
        $grouped = $orders->groupBy('driverCode');

        // Build data structure per driver
        $driverData = [];
        foreach ($grouped as $driverCode => $driverOrders) {
            $driver = $driverOrders->first()->driver;
            $fleet  = $driverOrders->first()->fleet;

            $rows = [];
            $grandTotal = 0;

            foreach ($driverOrders as $index => $order) {
                $salaryTotal = $this->getSalaryTotal($order->code);
                $grandTotal += $salaryTotal;

                $routeName = '-';
                if ($order->route) {
                    $origin = $order->route->originLocation->name ?? '';
                    $dest   = $order->route->destinationLocation->name ?? '';
                    $routeName = $order->route->name . ' (' . $origin . ' - ' . $dest . ')';
                }

                $rows[] = [
                    'no'             => $index + 1,
                    'orderCode'      => $order->code,
                    'shipmentNumber' => $order->shipmentNumber ?? '-',
                    'date'           => Carbon::parse($order->orderDate)->format('d-m-Y'),
                    'route'          => $routeName,
                    'salary'         => $salaryTotal,
                ];
            }

            // Determine month label from date filter or from data
            $monthLabel = '';
            if ($request->startDate) {
                $monthLabel = Carbon::parse($request->startDate)->translatedFormat('F Y');
            } elseif ($driverOrders->count() > 0) {
                $monthLabel = Carbon::parse($driverOrders->first()->orderDate)->translatedFormat('F Y');
            }

            $driverData[] = [
                'driverName'   => $driver->name ?? '-',
                'plateNumber'  => $fleet->plateNumber ?? '-',
                'month'        => $monthLabel,
                'rows'         => $rows,
                'grandTotal'   => $grandTotal,
                'adjustments'  => [],
            ];
        }

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format'      => [215, 330],
            'tempDir'     => storage_path('app/mpdf-temp'),
        ]);
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $html = view($this->view . 'report.driver-salary-pdf')
            ->with('driverData', $driverData)
            ->render();

        $mpdf->WriteHTML($html);

        return $mpdf->Output('Slip_Gaji_Driver.pdf', 'I');
    }

    /**
     * Generate PDF Slip Gaji for a processed salary.
     */
    public function pdfDriverSalaryProcessed($id)
    {
        $salary = DriverSalary::with(['driver', 'details'])->findOrFail($id);

        // Get order_driver_salary records linked to this salary slip
        $orderDriverSalaries = OrderDriverSalary::with(['order.fleet', 'order.route.originLocation', 'order.route.destinationLocation'])
            ->where('driver_salary_id', $salary->id)
            ->get();

        // Fallback for legacy data
        if ($orderDriverSalaries->isEmpty()) {
            $employee = Employee::where('code', $salary->driverCode)->first();
            $orderDriverSalaries = OrderDriverSalary::with(['order.fleet', 'order.route.originLocation', 'order.route.destinationLocation'])
                ->where('driver_id', $employee?->id)
                ->whereHas('order', function ($q) use ($salary) {
                    $q->whereDate('orderDate', '>=', $salary->startDate)
                      ->whereDate('orderDate', '<=', $salary->endDate)
                      ->whereNull('deleted_at');
                })
                ->get();
        }

        $groupedOrders = $orderDriverSalaries->groupBy('order_id');
        $rows = [];
        $grandTotal = 0;
        $fleet = null;
        $index = 1;

        foreach ($groupedOrders as $orderId => $items) {
            $order = $items->first()->order;
            if (! $order) continue;

            $salaryTotal = $items->sum('amount');
            $grandTotal += $salaryTotal;

            if (!$fleet && $order->fleet) {
                $fleet = $order->fleet;
            }

            $routeName = '-';
            if ($order->route) {
                $origin = $order->route->originLocation->name ?? '';
                $dest   = $order->route->destinationLocation->name ?? '';
                $routeName = $order->route->name . ' (' . $origin . ' - ' . $dest . ')';
            }

            $rows[] = [
                'no'             => $index++,
                'orderCode'      => $order->code,
                'shipmentNumber' => $order->shipmentNumber ?? '-',
                'date'           => Carbon::parse($order->orderDate)->format('d-m-Y'),
                'route'          => $routeName,
                'salary'         => $salaryTotal,
            ];
        }

        $monthLabel = Carbon::parse($salary->startDate)->translatedFormat('F Y');

        // Build adjustments array
        $adjustments = [];
        foreach ($salary->details as $detail) {
            $adjustments[] = [
                'date' => Carbon::parse($detail->date)->format('d-m-Y'),
                'description' => $detail->description,
                'type' => $detail->type,
                'nominal' => $detail->nominal,
            ];
        }

        $driverData = [[
            'driverName'     => $salary->driver->name ?? '-',
            'plateNumber'    => $fleet->plateNumber ?? '-',
            'month'          => $monthLabel,
            'rows'           => $rows,
            'grandTotal'     => $salary->grandTotal,
            'totalSalary'    => $salary->totalSalary,
            'totalAdjustment' => $salary->totalAdjustment,
            'adjustments'    => $adjustments,
        ]];

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format'      => [215, 330],
            'tempDir'     => storage_path('app/mpdf-temp'),
        ]);
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $html = view($this->view . 'report.driver-salary-pdf')
            ->with('driverData', $driverData)
            ->render();

        $mpdf->WriteHTML($html);

        return $mpdf->Output('Slip_Gaji_' . ($salary->driver->name ?? 'Driver') . '.pdf', 'I');
    }

    /**
     * Sync existing processed driver salary data into order_driver_salary with status = '1' and driver_salary_id.
     */
    public function syncExistingStatus(Request $request)
    {
        if (! in_array(Auth::user()->roleCode, ['SPRADMIN', 'SPRUSER'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action. Hanya Super Admin yang diizinkan.',
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $salaries = DriverSalary::all();
            $totalUpdated = 0;
            $totalOrders = 0;

            foreach ($salaries as $ds) {
                $orders = Order::where('driverCode', $ds->driverCode)
                    ->whereDate('orderDate', '>=', $ds->startDate)
                    ->whereDate('orderDate', '<=', $ds->endDate)
                    ->whereNull('deleted_at')
                    ->get();

                foreach ($orders as $order) {
                    OrderDriverSalaryService::syncForOrder($order);
                }

                $orderIds = $orders->pluck('id');
                $updated = OrderDriverSalary::whereIn('order_id', $orderIds)
                    ->update([
                        'status'           => '1',
                        'driver_salary_id' => $ds->id,
                    ]);

                $totalUpdated += $updated;
                $totalOrders += $orders->count();
            }

            DB::commit();

            $message = "Berhasil mensinkronisasi status '1' pada {$totalUpdated} data komponen gaji supir dari {$salaries->count()} data rekap gaji supir ({$totalOrders} order terkait).";

            if ($request->ajax()) {
                return response()->json([
                    'success'       => true,
                    'message'       => $message,
                    'total_slips'   => $salaries->count(),
                    'total_updated' => $totalUpdated,
                    'total_orders'  => $totalOrders,
                ]);
            }

            return redirect()->route('report.driver-salary.index')->with('success', $message);
        } catch (\Throwable $th) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal sinkronisasi: ' . $th->getMessage(),
                ], 500);
            }

            return redirect()->route('report.driver-salary.index')->with('fail', 'Gagal sinkronisasi: ' . $th->getMessage());
        }
    }
}
