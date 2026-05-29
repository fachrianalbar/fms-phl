<?php

namespace App\Http\Controllers\Report;

use App\Helpers\FilterHelper;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Models\Report\DriverSalary;
use App\Models\Report\DriverSalaryDetail;
use App\Services\Master\EmployeeService;
use App\Services\Master\FleetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
     * Build the base query for driver salary report.
     * Returns orders that have at least one OrderCost whose CostComponent.type = 'salary'.
     */
    private function buildSalaryQuery(Request $request)
    {
        $query = Order::with(['fleet', 'driver', 'route.originLocation', 'route.destinationLocation'])
            ->whereHas('cost', function ($q) {
                $q->whereHas('costComponent', function ($q2) {
                    $q2->where('type', 'salary');
                });
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
     * Get salary total for a single order (sum of OrderCost where costComponent.type = 'salary').
     */
    private function getSalaryTotal($orderCode)
    {
        return OrderCost::where('orderCode', $orderCode)
            ->whereHas('costComponent', function ($q) {
                $q->where('type', 'salary');
            })
            ->sum('nominal');
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

            // Calculate total salary from orders
            $orders = Order::whereHas('cost', function ($q) {
                    $q->whereHas('costComponent', function ($q2) {
                        $q2->where('type', 'salary');
                    });
                })
                ->where('driverCode', $request->driverCode)
                ->whereDate('orderDate', '>=', $request->startDate)
                ->whereDate('orderDate', '<=', $request->endDate)
                ->whereNull('deleted_at')
                ->get();

            $totalSalary = 0;
            foreach ($orders as $order) {
                $totalSalary += $this->getSalaryTotal($order->code);
            }

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

        // Get orders for this driver and period
        $orders = Order::with(['fleet', 'route.originLocation', 'route.destinationLocation'])
            ->whereHas('cost', function ($q) {
                $q->whereHas('costComponent', function ($q2) {
                    $q2->where('type', 'salary');
                });
            })
            ->where('driverCode', $salary->driverCode)
            ->whereDate('orderDate', '>=', $salary->startDate)
            ->whereDate('orderDate', '<=', $salary->endDate)
            ->whereNull('deleted_at')
            ->orderBy('orderDate', 'asc')
            ->get();

        // Attach salary total to each order
        foreach ($orders as $order) {
            $order->salaryAmount = $this->getSalaryTotal($order->code);
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
        
        // Also fetch orders for this driver and period to populate the modal's order list
        $orders = Order::with(['fleet', 'route.originLocation', 'route.destinationLocation'])
            ->whereHas('cost', function ($q) {
                $q->whereHas('costComponent', function ($q2) {
                    $q2->where('type', 'salary');
                });
            })
            ->where('driverCode', $salary->driverCode)
            ->whereDate('orderDate', '>=', $salary->startDate)
            ->whereDate('orderDate', '<=', $salary->endDate)
            ->whereNull('deleted_at')
            ->orderBy('orderDate', 'asc')
            ->get();

        $orderList = [];
        foreach ($orders as $order) {
            $salaryAmount = $this->getSalaryTotal($order->code);
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

            $driverSalary = DriverSalary::findOrFail($id);

            // Calculate total salary from orders for new driver/period
            $orders = Order::whereHas('cost', function ($q) {
                    $q->whereHas('costComponent', function ($q2) {
                        $q2->where('type', 'salary');
                    });
                })
                ->where('driverCode', $request->driverCode)
                ->whereDate('orderDate', '>=', $request->startDate)
                ->whereDate('orderDate', '<=', $request->endDate)
                ->whereNull('deleted_at')
                ->get();

            $totalSalary = 0;
            foreach ($orders as $order) {
                $totalSalary += $this->getSalaryTotal($order->code);
            }

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
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gaji driver berhasil diperbarui.'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX datatable for processed salaries.
     */
    public function datatableProcessed(Request $request)
    {
        if ($request->ajax()) {
            $query = DriverSalary::with('driver')->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('driverName', function ($row) {
                    return $row->driver->name ?? '-';
                })
                ->editColumn('startDate', function ($row) {
                    return Carbon::parse($row->startDate)->format('d-m-Y');
                })
                ->editColumn('endDate', function ($row) {
                    return Carbon::parse($row->endDate)->format('d-m-Y');
                })
                ->addColumn('totalSalaryFormatted', function ($row) {
                    return number_format($row->totalSalary, 0, ',', '.');
                })
                ->addColumn('totalAdjustmentFormatted', function ($row) {
                    $prefix = $row->totalAdjustment >= 0 ? '+' : '';
                    return $prefix . number_format($row->totalAdjustment, 0, ',', '.');
                })
                ->addColumn('grandTotalFormatted', function ($row) {
                    return number_format($row->grandTotal, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('report.driver-salary.show', $row->id);
                    $pdfUrl = route('report.driver-salary.pdf-processed', $row->id);
                    $buttons = '<div class="btn-group" role="group">';
                    $buttons .= '<a href="' . $showUrl . '" class="btn btn-sm btn-outline-primary" title="Detail"><i class="mdi mdi-eye fs-14"></i></a>';
                    $buttons .= '<a href="' . $pdfUrl . '" target="_blank" class="btn btn-sm btn-outline-danger" title="PDF"><i class="mdi mdi-file-pdf-box fs-14"></i></a>';
                    $buttons .= '<button type="button" onclick="editSalary(\'' . $row->id . '\')" class="btn btn-sm btn-outline-success" title="Edit"><i class="mdi mdi-pencil fs-14"></i></button>';
                    $buttons .= '<button type="button" onclick="deleteSalary(\'' . $row->id . '\')" class="btn btn-sm btn-outline-warning" title="Hapus"><i class="mdi mdi-delete fs-14"></i></button>';
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['action'])
                ->toJson();
        }
    }

    /**
     * AJAX - Get order salary data for preview before processing.
     */
    public function getOrderSalary(Request $request)
    {
        $driverCode = $request->driverCode;
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        if (!$driverCode || !$startDate || !$endDate) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 422);
        }

        $orders = Order::with(['fleet', 'route.originLocation', 'route.destinationLocation'])
            ->whereHas('cost', function ($q) {
                $q->whereHas('costComponent', function ($q2) {
                    $q2->where('type', 'salary');
                });
            })
            ->where('driverCode', $driverCode)
            ->whereDate('orderDate', '>=', $startDate)
            ->whereDate('orderDate', '<=', $endDate)
            ->whereNull('deleted_at')
            ->orderBy('orderDate', 'asc')
            ->get();

        $result = [];
        $totalSalary = 0;

        foreach ($orders as $order) {
            $salary = $this->getSalaryTotal($order->code);
            $totalSalary += $salary;

            $routeName = '-';
            if ($order->route) {
                $origin = $order->route->originLocation->name ?? '';
                $dest = $order->route->destinationLocation->name ?? '';
                $routeName = $order->route->name . ' (' . $origin . ' - ' . $dest . ')';
            }

            $result[] = [
                'orderCode' => $order->code,
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
                    'no'        => $index + 1,
                    'date'      => Carbon::parse($order->orderDate)->format('d-m-Y'),
                    'route'     => $routeName,
                    'salary'    => $salaryTotal,
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

        // Get orders for this driver and period
        $orders = Order::with(['fleet', 'route.originLocation', 'route.destinationLocation'])
            ->whereHas('cost', function ($q) {
                $q->whereHas('costComponent', function ($q2) {
                    $q2->where('type', 'salary');
                });
            })
            ->where('driverCode', $salary->driverCode)
            ->whereDate('orderDate', '>=', $salary->startDate)
            ->whereDate('orderDate', '<=', $salary->endDate)
            ->whereNull('deleted_at')
            ->orderBy('orderDate', 'asc')
            ->get();

        $rows = [];
        $grandTotal = 0;
        $fleet = null;

        foreach ($orders as $index => $order) {
            $salaryTotal = $this->getSalaryTotal($order->code);
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
                'no'     => $index + 1,
                'date'   => Carbon::parse($order->orderDate)->format('d-m-Y'),
                'route'  => $routeName,
                'salary' => $salaryTotal,
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
}
