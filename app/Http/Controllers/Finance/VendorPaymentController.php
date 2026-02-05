<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Finance\VendorPayment;
use App\Services\Finance\VendorPaymentService;
use App\Services\Master\MenuService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class VendorPaymentController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(VendorPaymentService $vendorPaymentSvc, MenuService $menuSvc)
    {
        $this->service = $vendorPaymentSvc;
        $this->title = 'Vendor Payment';
        $this->menuSvc = $menuSvc->getByName('Vendor Payment');
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = 'finance.vendor-payment.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'date' => 'required',
            'userBankCode' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view.'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);
            DB::commit();

            return redirect()->route($this->view.'index')->with('success', $this->title.' '.__('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = '';

                    if (isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })

                ->editColumn('customer.name', function ($row) {
                    $customer = '';

                    if (isset($row->customer->name)) {
                        $customer = $row->customer->name;
                    }

                    return $customer;
                })

                ->editColumn('driver.name', function ($row) {
                    $driver = '';

                    if (isset($row->driver->name)) {
                        $driver = $row->driver->name;
                    }

                    return $driver;
                })
                ->editColumn('route.originLocation.name', function ($row) {
                    $origin = '';

                    if (isset($row->route->originLocation->name)) {
                        $origin = $row->route->originLocation->name;
                    }

                    return $origin;
                })
                ->editColumn('route.destinationLocation.name', function ($row) {
                    $destination = '';

                    if (isset($row->route->destinationLocation->name)) {
                        $destination = $row->route->destinationLocation->name;
                    }

                    return $destination;
                })
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->editColumn('personalVendorPrice', function ($row) {
                    $amount = $row->personalVendorPrice ?? 0;

                    return $amount > 0 ? number_format($amount, 0, ',', '.') : '0';
                })
                ->addColumn('billingAmount', function ($row) {
                    $vendorPayment = \App\Models\Finance\VendorPayment::where('orderCode', $row->code)->first();
                    $amount = $vendorPayment ? ($vendorPayment->amount ?? 0) : ($row->personalVendorPrice ?? 0);

                    return $amount > 0 ? number_format($amount, 0, ',', '.') : '0';
                })
                ->addColumn('paidAmount', function ($row) {
                    $vendorPayment = \App\Models\Finance\VendorPayment::where('orderCode', $row->code)->first();
                    $amount = $vendorPayment ? ($vendorPayment->paid_amount ?? 0) : 0;

                    return $amount > 0 ? number_format($amount, 0, ',', '.') : '0';
                })
                ->addColumn('remainingAmount', function ($row) {
                    $vendorPayment = \App\Models\Finance\VendorPayment::where('orderCode', $row->code)->first();
                    if ($vendorPayment) {
                        $amount = $vendorPayment->remaining_amount ?? 0;
                    } else {
                        // Jika belum ada vendor payment, sisa = tagihan penuh
                        $amount = $row->personalVendorPrice ?? 0;
                    }

                    return $amount > 0 ? number_format($amount, 0, ',', '.') : '0';
                })
                ->addColumn('paymentStatus', function ($row) {
                    $vendorPayment = \App\Models\Finance\VendorPayment::where('orderCode', $row->code)->first();
                    $status = $vendorPayment ? ($vendorPayment->payment_status ?? 'pending') : 'pending';

                    $statusText = '';
                    $badgeClass = 'secondary';

                    if ($status === 'pending') {
                        $statusText = 'Pending';
                        $badgeClass = 'warning';
                    } elseif ($status === 'partial') {
                        $statusText = 'Partial';
                        $badgeClass = 'info';
                    } elseif ($status === 'paid') {
                        $statusText = 'Paid';
                        $badgeClass = 'success';
                    }

                    return '<span class="badge rounded-pill text-bg-'.$badgeClass.'">'.$statusText.'</span>';
                })
                ->editColumn('status', function ($row) {
                    $statusText = '';
                    $badgeClass = 'primary';

                    if (isset($row->orderStatus->name)) {
                        $statusText = Auth::user()->languange == 'id' ? $row->orderStatus->nama : $row->orderStatus->name;
                    }

                    if ($row->status == 4) {
                        $badgeClass = 'warning';
                    } elseif ($row->status == 6) {
                        $badgeClass = 'success';
                    } elseif ($row->status == 3) {
                        $badgeClass = 'primary';
                    }

                    return '<span class="badge rounded-pill text-bg-'.$badgeClass.'">'.$statusText.'</span>';
                })
                ->addColumn('action', function ($row) {
                    $vendorPayment = \App\Models\Finance\VendorPayment::where('orderCode', $row->code)->first();
                    $remainingAmount = $vendorPayment ? ($vendorPayment->remaining_amount ?? 0) : ($row->personalVendorPrice ?? 0);
                    $paymentStatus = $vendorPayment ? ($vendorPayment->payment_status ?? 'pending') : 'pending';

                    // Build button group for cleaner UI
                    $buttons = [];

                    // PDF (always show)
                    $buttons[] = '<a href="'.route('finance.vendor-payment.pdf', $row->code).'" target="_blank" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Print PDF"><i class="mdi mdi-file fs-14"></i></a>';

                    // Payment (only for external fleets and not fully paid)
                    if (isset($row->fleet->company) && $row->fleet->company->type == 'External' && $paymentStatus !== 'paid') {
                        $billingAmount = $vendorPayment ? ($vendorPayment->amount ?? 0) : ($row->personalVendorPrice ?? 0);
                        $buttons[] = '<button type="button" onclick="showModal(\''.$row->code.'\','.$billingAmount.','.$remainingAmount.')" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Payment"><i class="mdi mdi-cash fs-14"></i></button>';
                    }

                    // Detail (if payment history exists)
                    if ($vendorPayment && $vendorPayment->paymentHistory->isNotEmpty()) {
                        $buttons[] = '<button type="button" onclick="showDetailModal(\''.$row->code.'\')" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Detail"><i class="mdi mdi-eye fs-14"></i></button>';
                    }

                    // Wrap buttons in a group
                    $html = '<div class="btn-group" role="group" aria-label="Actions">'.implode('', $buttons).'</div>';

                    return $html;
                })
                ->rawColumns(['action', 'fleet.plateNumber', 'customer.name', 'route.originLocation.name', 'route.destinationLocation.name', 'status', 'paymentStatus'])
                ->toJson();
        }
    }

    public function getDetail($orderCode)
    {
        $vendorPayment = VendorPayment::with(['order.fleet', 'order.driver', 'order.customer', 'paymentHistory'])
            ->where('orderCode', $orderCode)
            ->first();

        if ($vendorPayment) {
            // Get mutation record for bank information
            $mutation = \App\Models\Mutation::where('description', 'like', '%'.$vendorPayment->order->code.'%')
                ->where('type', 'Out')
                ->with('userBank.bank')
                ->first();

            $vendorPayment->bankInfo = $mutation && $mutation->userBank ? [
                'bank_name' => $mutation->userBank->bank->name ?? 'N/A',
                'account_number' => $mutation->userBank->accountNumber ?? 'N/A',
                'account_name' => $mutation->userBank->accountName ?? 'N/A',
            ] : null;

            // Add transaction date from created_at
            $vendorPayment->transaction_date = $vendorPayment->created_at;

            // Format payment history
            if ($vendorPayment->paymentHistory) {
                $vendorPayment->payment_histories = $vendorPayment->paymentHistory->map(function ($history) {
                    return [
                        'amount' => $history->amount,
                        'payment_date' => $history->payment_date,
                        'user_bank_code' => $history->user_bank_code,
                        'description' => $history->description,
                        'created_at' => $history->created_at,
                    ];
                });
            }
        }

        return response()->json($vendorPayment);
    }

    public function pdfVendorPayment($orderCode)
    {
        // Cari order berdasarkan code
        $order = \App\Models\Operational\Order::with([
            'fleet',
            'driver',
            'customer',
            'route.originLocation',
            'route.destinationLocation',
            'orderMaterial.material',
            'cost',
        ])->where('code', $orderCode)->first();

        if (! $order) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $company = CompanySetting::first();
        $customer = $order->customer;

        // Cari vendor payment jika ada
        $vendorPayment = \App\Models\Finance\VendorPayment::where('orderCode', $orderCode)->first();

        // Tentukan template PDF berdasarkan customer company format
        $pdfTemplate = 'finance.vendor-payment.pdf.general-phl'; // Default template

        // pribadi
        if ($customer->company->format == 'P') {
            $pdfTemplate = 'finance.vendor-payment.pdf.pribadi';
        }

        // wijaya trans
        if ($customer->company->format == 'WTMS' || $customer->company->format == 'WT') {
            $pdfTemplate = 'finance.vendor-payment.pdf.general-wt';
        }

        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
                'tempDir' => storage_path('app/mpdf-temp'),
            ]
        );

        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $mpdf->WriteHTML(
            view($pdfTemplate)
                ->with('vendorPayment', $vendorPayment)
                ->with('order', $order)
                ->with('customer', $customer)
                ->with('company', $company)
        );

        return $mpdf->Output('Nota-Pembayaran-'.$order->code.'.pdf', 'I');
    }
}
