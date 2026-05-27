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
        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderCodes' => 'required|array|min:1',
            'orderCodes.*' => 'required|string',
            'date' => 'required|date',
            'userBankCode' => 'required',
            'paymentAmount' => 'nullable|numeric|min:1',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $result = $this->service->store($request, $this->title);
            DB::commit();

            $message = $result['processed_count'] . ' order vendor berhasil dibayar lunas.';
            if (($result['skipped_count'] ?? 0) > 0) {
                $message .= ' ' . $result['skipped_count'] . ' order dilewati karena sudah lunas.';
            }
            if (! empty($result['batch_code'])) {
                $message .= ' Kode pembayaran: ' . $result['batch_code'] . '.';
            }

            return redirect()->route($this->view . 'index')->with('success', $message);
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('select', function ($row) {
                    $vendorPayment = $row->vendorPayments->sortByDesc('created_at')->first();
                    $billingAmount = (float) ($row->vendorPrice ?? 0);
                    $paidAmount = $vendorPayment ? (float) ($vendorPayment->paid_amount ?? 0) : 0;
                    $remainingAmount = $vendorPayment ? (float) ($vendorPayment->remaining_amount ?? 0) : $billingAmount;
                    $paymentStatus = $vendorPayment ? ($vendorPayment->payment_status ?? 'pending') : 'pending';
                    $notaNumber = $vendorPayment ? ($vendorPayment->nota_number ?? null) : null;
                    $orderFormat = strtoupper(trim((string) ($row->customer->company->format ?? '')));

                    $isExternalFleet = isset($row->fleet->company->type) && strcasecmp((string) $row->fleet->company->type, 'external') === 0;

                    // Order yang sudah punya nota bisa dipilih untuk bayar/cetak
                    // Order yang belum punya nota wajib di-generate nota terlebih dahulu
                    $canBePaid = $isExternalFleet && $notaNumber;
                    $canBeNota = $isExternalFleet && ! $notaNumber;

                    if (! $canBePaid && ! $canBeNota) {
                        return '<span class="text-muted">-</span>';
                    }

                    $checkboxType = $canBePaid ? 'payment' : 'nota';
                    $customerCode = $row->customerCode ?? ($row->customer->code ?? '');

                    return '<div class="form-check d-flex justify-content-center"><input type="checkbox" class="form-check-input row-payment-checkbox" data-order-code="' . $row->code . '" data-customer-code="' . e($customerCode) . '" data-order-format="' . e($orderFormat) . '" data-billing-amount="' . $billingAmount . '" data-paid-amount="' . $paidAmount . '" data-remaining-amount="' . $remainingAmount . '" data-checkbox-type="' . $checkboxType . '" data-nota-number="' . ($notaNumber ?? '') . '"></div>';
                })
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
                    $amount = $row->vendorPrice ?? 0;

                    return $amount > 0 ? number_format($amount, 0, ',', '.') : '0';
                })
                ->addColumn('paidAmount', function ($row) {
                    $vendorPayment = $row->vendorPayments->sortByDesc('created_at')->first();
                    $amount = $vendorPayment ? ($vendorPayment->paid_amount ?? 0) : 0;

                    return $amount > 0 ? number_format($amount, 0, ',', '.') : '0';
                })
                ->addColumn('remainingAmount', function ($row) {
                    $vendorPayment = $row->vendorPayments->sortByDesc('created_at')->first();
                    if ($vendorPayment) {
                        $amount = $vendorPayment->remaining_amount ?? 0;
                    } else {
                        // Jika belum ada vendor payment, sisa = tagihan penuh
                        $amount = $row->vendorPrice ?? 0;
                    }

                    return $amount > 0 ? number_format($amount, 0, ',', '.') : '0';
                })
                ->addColumn('paymentStatus', function ($row) {
                    $vendorPayment = $row->vendorPayments->sortByDesc('created_at')->first();
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

                    return '<span class="badge rounded-pill text-bg-' . $badgeClass . '">' . $statusText . '</span>';
                })
                ->addColumn('notaNumber', function ($row) {
                    $vendorPayment = $row->vendorPayments->sortByDesc('created_at')->first();
                    $notaNumber = $vendorPayment ? ($vendorPayment->nota_number ?? null) : null;

                    if ($notaNumber) {
                        return '<span class="badge rounded-pill text-bg-primary">' . $notaNumber . '</span>';
                    }

                    return '<span class="text-muted">-</span>';
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

                    return '<span class="badge rounded-pill text-bg-' . $badgeClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $vendorPayment = $row->vendorPayments->sortByDesc('created_at')->first();

                    // Build button group for cleaner UI
                    $buttons = [];

                    // Detail (if payment history exists)
                    if ($vendorPayment && $vendorPayment->paymentHistory->isNotEmpty()) {
                        $buttons[] = '<button type="button" onclick="showDetailModal(\'' . $row->code . '\')" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Detail"><i class="mdi mdi-eye fs-14"></i></button>';
                    }

                    // Batal Nota (hanya tampil jika sudah bernota tetapi belum ada pembayaran sama sekali)
                    $hasNotaOnly = $vendorPayment && $vendorPayment->nota_number && $vendorPayment->paid_amount == 0 && $vendorPayment->payment_status === 'pending';
                    if ($hasNotaOnly) {
                        $buttons[] = '<button type="button" onclick="confirmCancelNota(\'' . $row->code . '\')" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Batal Nota"><i class="mdi mdi-file-remove-outline fs-14"></i></button>';
                    }

                    // Batal Pembayaran (hanya tampil jika sudah pernah ada pembayaran nyata)
                    $hasPaymentMade = $vendorPayment && ($vendorPayment->paid_amount > 0 || $vendorPayment->payment_status !== 'pending');
                    if ($hasPaymentMade) {
                        $buttons[] = '<button type="button" onclick="confirmCancelPayment(\'' . $row->code . '\')" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Batal Pembayaran"><i class="mdi mdi-close-circle fs-14"></i></button>';
                    }

                    // Wrap buttons in a group
                    $html = '<div class="btn-group" role="group" aria-label="Actions">' . implode('', $buttons) . '</div>';

                    return $html;
                })
                ->rawColumns(['select', 'action', 'fleet.plateNumber', 'customer.name', 'route.originLocation.name', 'route.destinationLocation.name', 'status', 'paymentStatus', 'notaNumber'])
                ->toJson();
        }
    }

    public function getDetail($orderCode)
    {
        $vendorPayment = VendorPayment::with(['order.fleet', 'order.driver', 'order.customer', 'paymentHistory.userBank.bank'])
            ->where('orderCode', $orderCode)
            ->first();

        if ($vendorPayment) {
            // Get mutation record for bank information
            $mutation = \App\Models\Mutation::where('description', 'like', '%' . $vendorPayment->order->code . '%')
                ->where('type', 'Out')
                ->with('userBank.bank')
                ->orderByDesc('created_at')
                ->first();

            $vendorPayment->batch_code = $mutation->transactionCode ?? null;
            $vendorPayment->shipmentNumber = $vendorPayment->order->shipmentNumber ?? null;
            $vendorPayment->shipment_number = $vendorPayment->order->shipmentNumber ?? null;

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
                    $bankName = $history->userBank->bank->name ?? null;
                    $accountNumber = $history->userBank->accountNumber ?? null;
                    $accountName = $history->userBank->accountName ?? null;

                    return [
                        'amount' => $history->amount,
                        'payment_date' => $history->payment_date,
                        'user_bank_code' => $history->user_bank_code,
                        'bank_info' => $bankName && $accountNumber && $accountName
                            ? $bankName . ' - ' . $accountNumber . ' (' . $accountName . ')'
                            : $history->user_bank_code,
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
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $company = CompanySetting::first();
        $customer = $order->customer;

        // Cari vendor payment jika ada
        $vendorPayment = \App\Models\Finance\VendorPayment::with(['paymentHistory.userBank.bank'])
            ->where('orderCode', $orderCode)
            ->first();

        if (! $vendorPayment || ! $vendorPayment->nota_number) {
            return redirect()->route($this->view . 'index')->with('fail', 'Nomor nota belum di-generate untuk order ini. Silakan generate nota terlebih dahulu.');
        }

        $paymentHistories = collect($vendorPayment?->paymentHistory ?? []);
        $paymentHistoryTotal = $paymentHistories->sum('amount');

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
                ->with('paymentHistories', $paymentHistories)
                ->with('paymentHistoryTotal', $paymentHistoryTotal)
                ->with('order', $order)
                ->with('customer', $customer)
                ->with('company', $company)
        );

        return $mpdf->Output('Nota-Pembayaran-' . $order->code . '.pdf', 'I');
    }

    public function pdfVendorPaymentMulti(Request $request)
    {
        $orderCodes = $request->input('orderCodes', []);

        // Jika parameter adalah string berisi koma, pisahkan
        if (is_string($orderCodes)) {
            $orderCodes = array_filter(array_map('trim', explode(',', $orderCodes)));
        }

        if (empty($orderCodes)) {
            return redirect()->route($this->view . 'index')->with('fail', 'Tidak ada order yang dipilih');
        }

        // Ambil semua nomor nota yang terkait dengan order terpilih
        $selectedNotaNumbers = \App\Models\Finance\VendorPayment::whereIn('orderCode', $orderCodes)
            ->whereNotNull('nota_number')
            ->pluck('nota_number')
            ->unique();

        if ($selectedNotaNumbers->isNotEmpty()) {
            // Ambil semua orderCode yang memiliki salah satu dari nomor nota di atas
            $allOrderCodesWithSameNotas = \App\Models\Finance\VendorPayment::whereIn('nota_number', $selectedNotaNumbers)
                ->pluck('orderCode')
                ->toArray();

            // Gabungkan order codes asal dengan order codes hasil pencarian nota
            $orderCodes = array_values(array_unique(array_merge($orderCodes, $allOrderCodesWithSameNotas)));
        }

        // Validasi: semua order terpilih harus memiliki nota_number
        $vendorPayments = \App\Models\Finance\VendorPayment::whereIn('orderCode', $orderCodes)->get();
        if ($vendorPayments->count() < count($orderCodes) || $vendorPayments->contains(fn($vp) => !$vp->nota_number)) {
            return redirect()->route($this->view . 'index')->with('fail', 'Beberapa order terpilih belum memiliki nomor nota. Silakan generate nota terlebih dahulu.');
        }

        // Fetch semua orders dengan relasi
        $orders = \App\Models\Operational\Order::with([
            'fleet.company',
            'driver',
            'customer.company',
            'route.originLocation',
            'route.destinationLocation',
            'orderMaterial.material',
            'cost',
        ])->whereIn('code', $orderCodes)->get();

        if ($orders->isEmpty()) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data order tidak ditemukan');
        }

        // Group orders by customer company format (untuk menentukan template)
        $groupedByFormat = $orders->groupBy(function ($order) {
            return $order->customer->company->format ?? 'P';
        });

        // Jika hanya 1 format, gunakan template sesuai format
        // Jika multiple format, gunakan template umum (general-phl)
        $firstFormat = $groupedByFormat->keys()->first();
        $useGeneralTemplate = count($groupedByFormat) > 1;

        // Tentukan template PDF berdasarkan format
        $pdfTemplate = 'finance.vendor-payment.pdf.general-phl'; // Default

        if (!$useGeneralTemplate) {
            if ($firstFormat === 'P') {
                $pdfTemplate = 'finance.vendor-payment.pdf.pribadi';
            } elseif (in_array($firstFormat, ['WTMS', 'WT'])) {
                $pdfTemplate = 'finance.vendor-payment.pdf.general-wt';
            }
        }

        // Hitung totals
        $totalSubtotal = 0;
        $totalAdditionalCost = 0;
        $totalPphAmount = 0;
        $totalGrandTotal = 0;

        foreach ($orders as $order) {
            $subtotal = ($order->qty ?? 0) * ($order->route->personalVendorPrice ?? 0);
            $additionalCost = $order->cost ? $order->cost->sum('nominal') : 0;
            $totalBefore = $subtotal + $additionalCost;
            $pph = $order->fleet->company->pph ?? 0;
            $pphAmount = ($totalBefore * $pph) / 100;
            $grandTotal = $totalBefore - $pphAmount;

            $totalSubtotal += $subtotal;
            $totalAdditionalCost += $additionalCost;
            $totalPphAmount += $pphAmount;
            $totalGrandTotal += $grandTotal;
        }

        $company = CompanySetting::first();
        $customerFirst = $orders->first()->customer;

        // Ambil nomor nota dan bank yang dipilih
        $vendorPayment = \App\Models\Finance\VendorPayment::whereIn('orderCode', $orderCodes)
            ->whereNotNull('nota_number')
            ->first();

        $notaNumber = $vendorPayment ? $vendorPayment->nota_number : null;
        $userBankCode = $vendorPayment ? $vendorPayment->user_bank_code : null;

        $userBank = null;
        if ($userBankCode) {
            $userBank = \App\Models\Bank\UserBank::with('bank')->where('code', $userBankCode)->first();
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
            view($pdfTemplate . '-multi')
                ->with('orders', $orders)
                ->with('customer', $customerFirst)
                ->with('company', $company)
                ->with('totalSubtotal', $totalSubtotal)
                ->with('totalAdditionalCost', $totalAdditionalCost)
                ->with('totalPphAmount', $totalPphAmount)
                ->with('totalGrandTotal', $totalGrandTotal)
                ->with('notaNumber', $notaNumber)
                ->with('userBank', $userBank)
        );

        return $mpdf->Output('Nota-Pembayaran-Multi-' . now()->format('YmdHis') . '.pdf', 'I');
    }

    /**
     * Membatalkan pembayaran vendor (hard delete).
     *
     * @param string $orderCode
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($orderCode)
    {
        try {
            DB::beginTransaction();

            $this->service->cancelPayment($orderCode, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')
                ->with('success', 'Pembayaran vendor untuk order ' . $orderCode . ' berhasil dibatalkan secara permanen.');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')
                ->with('fail', 'Gagal membatalkan pembayaran: ' . $th->getMessage());
        }
    }

    /**
     * Generate nomor nota untuk order-order yang dipilih.
     */
    public function generateNota(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderCodes' => 'required|array|min:1',
            'orderCodes.*' => 'required|string',
            'userBankCode' => 'required|string|exists:user_bank,code',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')
                ->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $notaNumber = $this->service->assignNota($request->orderCodes, $request->userBankCode, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')
                ->with('success', 'Nota pembayaran berhasil di-generate dengan nomor: ' . $notaNumber);
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')
                ->with('fail', 'Gagal generate nota: ' . $th->getMessage());
        }
    }

    /**
     * Membatalkan nomor nota pembayaran (jika belum dibayar).
     */
    public function cancelNota($orderCode)
    {
        try {
            DB::beginTransaction();

            $this->service->cancelNota($orderCode, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')
                ->with('success', 'Nota pembayaran untuk order ' . $orderCode . ' berhasil dibatalkan.');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')
                ->with('fail', 'Gagal membatalkan nota: ' . $th->getMessage());
        }
    }
}
