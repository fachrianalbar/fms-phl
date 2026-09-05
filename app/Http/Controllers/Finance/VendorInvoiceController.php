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

/**
 * Controller menu Vendor: Invoice (nota) ke vendor eksternal.
 *
 * - Order Menunggu Nota  : order fleet external yang belum punya nota
 *                          (di sini nota/invoice digenerate)
 * - Invoice Belum Lunas : nota pending/partial (bayar DP/cicilan/lunas)
 * - Invoice Lunas       : nota yang semua ordernya sudah paid
 * - Operasi             : generate nota, bayar (lunas/DP), batal nota,
 *                         batal pembayaran, cetak PDF, detail
 */
class VendorInvoiceController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(VendorPaymentService $vendorPaymentSvc, MenuService $menuSvc)
    {
        $this->service = $vendorPaymentSvc;
        $this->title = 'Vendor Payment';
        $this->menuSvc = $menuSvc;
        $this->view = 'vendor.invoice.';
    }

    /**
     * Ambil judul halaman dari data menu berdasarkan kode (aman dari bentrok nama).
     */
    private function pageTitle(string $menuCode, string $fallback): string
    {
        $menu = $this->menuSvc->getByCode($menuCode);

        if (! $menu) {
            return $fallback;
        }

        return Auth::user()->languange == 'id' ? $menu->nama : $menu->name;
    }

    /**
     * Halaman Order Menunggu Nota (Vendor) — order belum dibuat invoice.
     */
    public function indexWaiting()
    {
        $stats = $this->service->statsWaiting();

        return view('vendor.order.waiting')
            ->with('view', $this->view)
            ->with('title', $this->pageTitle('VENDOR_ORDER_WAITING', 'Order Menunggu Nota'))
            ->with('stats', $stats);
    }

    /**
     * Halaman Invoice Belum Lunas (Vendor).
     */
    public function indexUnpaid()
    {
        $stats = $this->service->statsUnpaid();

        return view($this->view.'unpaid')
            ->with('view', $this->view)
            ->with('title', $this->pageTitle('VENDOR_INV_UNPAID', 'Invoice Belum Lunas'))
            ->with('stats', $stats);
    }

    /**
     * Halaman Invoice Lunas (Vendor).
     */
    public function indexPaid()
    {
        $stats = $this->service->statsPaid();

        return view($this->view.'paid')
            ->with('view', $this->view)
            ->with('title', $this->pageTitle('VENDOR_INV_PAID', 'Invoice Lunas'))
            ->with('stats', $stats);
    }

    /**
     * Datatable: order fleet external yang belum punya nota (menunggu dibuat nota).
     */
    public function datatableWaiting(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findWaitingOrders();

            return DataTables::of($data)
                ->addIndexColumn()
                ->filter(function ($dataTable) use ($request) {
                    $keyword = trim((string) $request->input('search.value', ''));

                    if ($keyword === '') {
                        return;
                    }

                    $normalize = static function ($value): string {
                        $text = html_entity_decode(strip_tags((string) ($value ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $text = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text) ?? $text));
                        $compact = preg_replace('/[^\pL\pN]+/u', '', $text) ?? '';

                        return $text . ' ' . $compact;
                    };

                    $terms = preg_split('/\s+/u', mb_strtolower($keyword), -1, PREG_SPLIT_NO_EMPTY) ?: [];

                    $dataTable->collection = $dataTable->collection->filter(function ($row) use ($normalize, $terms) {
                        $searchableValues = [
                            data_get($row, 'orderDate'),
                            data_get($row, 'code'),
                            data_get($row, 'shipmentNumber'),
                            data_get($row, 'fleet.plateNumber'),
                            data_get($row, 'fleet.company.name'),
                            data_get($row, 'driver.name'),
                            data_get($row, 'customer.name'),
                            data_get($row, 'companyFormat'),
                            data_get($row, 'route.originLocation.name'),
                            data_get($row, 'route.destinationLocation.name'),
                            data_get($row, 'billingAmount'),
                            data_get($row, 'paidAmount'),
                            data_get($row, 'remainingAmount'),
                            data_get($row, 'status'),
                        ];

                        $haystack = $normalize(implode(' ', $searchableValues));

                        foreach ($terms as $term) {
                            $normalizedTerm = $normalize($term);
                            $termParts = array_values(array_filter(explode(' ', $normalizedTerm)));

                            if (! collect($termParts)->contains(fn ($part) => str_contains($haystack, $part))) {
                                return false;
                            }
                        }

                        return true;
                    });
                })
                ->addColumn('select', function ($row) {
                    $vendorPayment = $row->vendorPayments->sortByDesc('created_at')->first();
                    $billingAmount = (float) ($vendorPayment->amount ?? $row->vendorPrice ?? 0);
                    $paidAmount = (float) ($vendorPayment->paid_amount ?? 0);
                    $remainingAmount = max(0, $billingAmount - $paidAmount);
                    $orderFormat = strtoupper(trim((string) ($row->customer->company->format ?? '')));
                    $customerCode = $row->customerCode ?? ($row->customer->code ?? '');
                    $fleetCompanyCode = $row->fleet->fleetCompanyCode ?? '';
                    $fleetCompanyName = $row->fleet->company->name ?? '';

                    return '<div class="form-check d-flex justify-content-center"><input type="checkbox" class="form-check-input row-payment-checkbox" data-order-code="' . e($row->code) . '" data-customer-code="' . e($customerCode) . '" data-fleet-company-code="' . e($fleetCompanyCode) . '" data-fleet-company-name="' . e($fleetCompanyName) . '" data-order-format="' . e($orderFormat) . '" data-billing-amount="' . $billingAmount . '" data-paid-amount="' . $paidAmount . '" data-remaining-amount="' . $remainingAmount . '" data-checkbox-type="nota" data-nota-number=""></div>';
                })
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->editColumn('shipmentNumber', function ($row) {
                    return e($row->shipmentNumber ?: '-');
                })
                ->editColumn('code', function ($row) {
                    return e($row->code ?: '-');
                })
                ->editColumn('fleet.plateNumber', function ($row) {
                    return $row->fleet->plateNumber ?? '';
                })
                ->addColumn('fleet.company.name', function ($row) {
                    return $row->fleet->company->name ?? '';
                })
                ->editColumn('customer.name', function ($row) {
                    return $row->customer->name ?? '';
                })
                ->editColumn('driver.name', function ($row) {
                    return $row->driver->name ?? '';
                })
                ->editColumn('route.originLocation.name', function ($row) {
                    return $row->route->originLocation->name ?? '';
                })
                ->editColumn('route.destinationLocation.name', function ($row) {
                    return $row->route->destinationLocation->name ?? '';
                })
                ->addColumn('companyFormat', function ($row) {
                    $format = strtoupper(trim((string) ($row->customer->company->format ?? '')));

                    if ($format === 'P') {
                        return 'Pribadi (P)';
                    }

                    if ($format === 'WTMS' || $format === 'WT') {
                        return 'WTMS';
                    }

                    if ($format === 'PHL') {
                        return 'PHL';
                    }

                    return '-';
                })
                ->addColumn('billingAmount', function ($row) {
                    $vendorPayment = $row->vendorPayments->sortByDesc('created_at')->first();
                    $amount = (float) ($vendorPayment->amount ?? $row->vendorPrice ?? 0);

                    return $amount > 0 ? number_format($amount, 0, ',', '.') : '0';
                })
                ->addColumn('paidAmount', function ($row) {
                    $amount = (float) ($row->vendorPayments->sortByDesc('created_at')->first()?->paid_amount ?? 0);

                    return $amount > 0 ? number_format($amount, 0, ',', '.') : '0';
                })
                ->addColumn('remainingAmount', function ($row) {
                    $vendorPayment = $row->vendorPayments->sortByDesc('created_at')->first();
                    $billing = (float) ($vendorPayment->amount ?? $row->vendorPrice ?? 0);
                    $amount = max(0, $billing - (float) ($vendorPayment->paid_amount ?? 0));

                    return $amount > 0 ? number_format($amount, 0, ',', '.') : '0';
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
                ->rawColumns(['select', 'status', 'fleet.plateNumber', 'customer.name', 'route.originLocation.name', 'route.destinationLocation.name'])
                ->toJson();
        }
    }

    /**
     * Datatable: nota (invoice) vendor yang belum lunas — 1 baris = 1 nota.
     */
    public function datatableUnpaid(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findUnpaidNotas();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('select', function ($row) {
                    $orderCodes = $row->order_codes->implode(',');

                    return '<div class="form-check d-flex justify-content-center"><input type="checkbox" class="form-check-input row-payment-checkbox" data-order-codes="' . e($orderCodes) . '" data-nota-number="' . e($row->nota_number) . '" data-customer-code="" data-fleet-company-code="' . e($row->fleetCompanyCode ?? '') . '" data-order-format="' . e($row->order_format ?? '') . '" data-billing-amount="' . $row->amount . '" data-paid-amount="' . $row->paid_amount . '" data-remaining-amount="' . $row->remaining_amount . '" data-ppn-amount="' . ($row->ppn_amount ?? 0) . '" data-pph-amount="' . ($row->pph_amount ?? 0) . '" data-checkbox-type="payment"></div>';
                })
                ->addColumn('action', function ($row) {
                    $firstOrderCode = $row->order_codes->first();

                    $buttons = [];
                    if ($row->paid_amount > 0 || $row->payment_status !== 'pending') {
                        $buttons[] = '<button type="button" onclick="showDetailModal(\'' . $firstOrderCode . '\')" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Detail"><i class="mdi mdi-eye fs-14"></i></button>';
                        $buttons[] = '<button type="button" onclick="confirmCancelPayment(\'' . $firstOrderCode . '\')" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Batal Pembayaran"><i class="mdi mdi-close-circle fs-14"></i></button>';
                    } else {
                        $buttons[] = '<button type="button" onclick="confirmCancelNota(\'' . $firstOrderCode . '\')" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Batal Nota"><i class="mdi mdi-file-remove-outline fs-14"></i></button>';
                    }

                    return '<div class="btn-group" role="group" aria-label="Actions">' . implode('', $buttons) . '</div>';
                })
                ->editColumn('nota_number', function ($row) {
                    return '<span class="badge rounded-pill text-bg-primary">' . e($row->nota_number) . '</span>';
                })
                ->editColumn('nota_date', function ($row) {
                    return $row->nota_date ? Carbon::parse($row->nota_date)->format('d-m-Y') : '-';
                })
                ->editColumn('fleet_company_name', function ($row) {
                    return e($row->fleet_company_name ?? '-');
                })
                ->editColumn('order_count', function ($row) {
                    return '<span class="badge rounded-pill text-bg-secondary">' . $row->order_count . ' order</span>';
                })
                ->editColumn('plate_numbers', function ($row) {
                    $plates = $row->plate_numbers->take(3)->implode(', ');
                    $more = $row->plate_numbers->count() > 3 ? ' +' . ($row->plate_numbers->count() - 3) : '';

                    return '<span class="text-nowrap" title="' . e($row->plate_numbers->implode(', ')) . '">' . e($plates) . $more . '</span>';
                })
                ->editColumn('amount', function ($row) {
                    return $row->amount > 0 ? number_format($row->amount, 0, ',', '.') : '0';
                })
                ->addColumn('ppn_amount', function ($row) {
                    $ppn = (float) ($row->ppn_amount ?? 0);

                    return $ppn > 0
                        ? '<span class="text-primary fw-semibold">' . number_format($ppn, 0, ',', '.') . '</span>'
                        : '<span class="text-muted">0</span>';
                })
                ->addColumn('pph_amount', function ($row) {
                    $pph = (float) ($row->pph_amount ?? 0);

                    return $pph > 0
                        ? '<span class="text-danger fw-semibold">' . number_format($pph, 0, ',', '.') . '</span>'
                        : '<span class="text-muted">0</span>';
                })
                ->editColumn('paid_amount', function ($row) {
                    return $row->paid_amount > 0 ? number_format($row->paid_amount, 0, ',', '.') : '0';
                })
                ->editColumn('remaining_amount', function ($row) {
                    return $row->remaining_amount > 0 ? number_format($row->remaining_amount, 0, ',', '.') : '0';
                })
                ->editColumn('payment_status', function ($row) {
                    $statusText = 'Pending';
                    $badgeClass = 'warning';

                    if ($row->payment_status === 'partial') {
                        $statusText = 'Partial';
                        $badgeClass = 'info';
                    } elseif ($row->payment_status === 'paid') {
                        $statusText = 'Paid';
                        $badgeClass = 'success';
                    }

                    return '<span class="badge rounded-pill text-bg-' . $badgeClass . '">' . $statusText . '</span>';
                })
                ->rawColumns(['select', 'action', 'nota_number', 'order_count', 'plate_numbers', 'ppn_amount', 'pph_amount', 'payment_status'])
                ->toJson();
        }
    }

    /**
     * Datatable: nota (invoice) vendor yang sudah lunas — 1 baris = 1 nota.
     */
    public function datatablePaid(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findPaidNotas();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('select', function ($row) {
                    $orderCodes = $row->order_codes->implode(',');

                    return '<div class="form-check d-flex justify-content-center"><input type="checkbox" class="form-check-input row-payment-checkbox" data-order-codes="' . e($orderCodes) . '" data-nota-number="' . e($row->nota_number) . '" data-customer-code="" data-fleet-company-code="' . e($row->fleetCompanyCode ?? '') . '" data-order-format="' . e($row->order_format ?? '') . '" data-billing-amount="' . $row->amount . '" data-paid-amount="' . $row->paid_amount . '" data-remaining-amount="' . $row->remaining_amount . '" data-checkbox-type="payment"></div>';
                })
                ->addColumn('action', function ($row) {
                    $firstOrderCode = $row->order_codes->first();

                    $buttons = [];
                    $buttons[] = '<button type="button" onclick="showDetailModal(\'' . $firstOrderCode . '\')" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Detail"><i class="mdi mdi-eye fs-14"></i></button>';
                    $buttons[] = '<button type="button" onclick="confirmCancelPayment(\'' . $firstOrderCode . '\')" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Batal Pembayaran"><i class="mdi mdi-close-circle fs-14"></i></button>';

                    return '<div class="btn-group" role="group" aria-label="Actions">' . implode('', $buttons) . '</div>';
                })
                ->editColumn('nota_number', function ($row) {
                    return '<span class="badge rounded-pill text-bg-primary">' . e($row->nota_number) . '</span>';
                })
                ->editColumn('nota_date', function ($row) {
                    return $row->nota_date ? Carbon::parse($row->nota_date)->format('d-m-Y') : '-';
                })
                ->editColumn('fleet_company_name', function ($row) {
                    return e($row->fleet_company_name ?? '-');
                })
                ->editColumn('order_count', function ($row) {
                    return '<span class="badge rounded-pill text-bg-secondary">' . $row->order_count . ' order</span>';
                })
                ->editColumn('plate_numbers', function ($row) {
                    $plates = $row->plate_numbers->take(3)->implode(', ');
                    $more = $row->plate_numbers->count() > 3 ? ' +' . ($row->plate_numbers->count() - 3) : '';

                    return '<span class="text-nowrap" title="' . e($row->plate_numbers->implode(', ')) . '">' . e($plates) . $more . '</span>';
                })
                ->editColumn('amount', function ($row) {
                    return $row->amount > 0 ? number_format($row->amount, 0, ',', '.') : '0';
                })
                ->editColumn('paid_amount', function ($row) {
                    return $row->paid_amount > 0 ? number_format($row->paid_amount, 0, ',', '.') : '0';
                })
                ->editColumn('payment_status', function ($row) {
                    return '<span class="badge rounded-pill text-bg-success">Paid</span>';
                })
                ->rawColumns(['select', 'action', 'nota_number', 'order_count', 'plate_numbers', 'payment_status'])
                ->toJson();
        }
    }

    /**
     * Simpan pembayaran (bisa banyak nota/order sekaligus, lunas atau DP/cicilan).
     */
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
            return redirect()->route('vendor.invoice.unpaid')->with('fail', $validator->errors()->all()[0]);
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

            return redirect()->route('vendor.invoice.unpaid')->with('success', $message);
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route('vendor.invoice.unpaid')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Ambil detail pembayaran vendor per order (digabung per nota).
     */
    public function getDetail($orderCode)
    {
        $vendorPayment = VendorPayment::with(['order.fleet.company', 'order.driver', 'order.customer.company', 'paymentHistory.userBank.bank'])
            ->where('orderCode', $orderCode)
            ->first();

        if ($vendorPayment) {
            $query = VendorPayment::with(['order.fleet.company', 'order.driver', 'order.customer.company', 'paymentHistory.userBank.bank']);
            if ($vendorPayment->nota_number) {
                $query->where('nota_number', $vendorPayment->nota_number);
            } else {
                $query->where('code', $vendorPayment->code);
            }
            $allAssociated = $query->get();

            $vendorPayment->associated_payments = $allAssociated;
            $vendorPayment->total_billing = $allAssociated->sum('amount');
            $vendorPayment->total_paid = $allAssociated->sum('paid_amount');
            $vendorPayment->total_remaining = $allAssociated->sum('remaining_amount');

            // PPN & PPh manual dari nota (nilai sama di semua baris → MAX agar tidak ganda)
            $vendorPayment->nota_ppn = (float) $allAssociated->max('ppn_amount');
            $vendorPayment->nota_pph = (float) $allAssociated->max('pph_amount');

            $mutation = \App\Models\Mutation::where('description', 'like', '%' . $vendorPayment->order->code . '%')
                ->where('type', 'Out')
                ->with('userBank.bank')
                ->orderByDesc('created_at')
                ->first();

            $vendorPayment->batch_code = $vendorPayment->code;
            $vendorPayment->shipmentNumber = $vendorPayment->order->shipmentNumber ?? null;
            $vendorPayment->shipment_number = $vendorPayment->order->shipmentNumber ?? null;

            $vendorPayment->bankInfo = $mutation && $mutation->userBank ? [
                'bank_name' => $mutation->userBank->bank->name ?? 'N/A',
                'account_number' => $mutation->userBank->accountNumber ?? 'N/A',
                'account_name' => $mutation->userBank->accountName ?? 'N/A',
            ] : null;

            $vendorPayment->transaction_date = $vendorPayment->created_at;

            $allHistories = collect();
            foreach ($allAssociated as $assoc) {
                if ($assoc->paymentHistory) {
                    foreach ($assoc->paymentHistory as $history) {
                        $allHistories->push($history);
                    }
                }
            }

            $groupedHistories = $allHistories->groupBy(function ($history) {
                return $history->payment_date . '_' . $history->description . '_' . $history->user_bank_code;
            })->map(function ($group) {
                $first = $group->first();
                $bankName = $first->userBank->bank->name ?? null;
                $accountNumber = $first->userBank->accountNumber ?? null;
                $accountName = $first->userBank->accountName ?? null;

                return [
                    'amount' => $group->sum('amount'),
                    'payment_date' => $first->payment_date,
                    'user_bank_code' => $first->user_bank_code,
                    'bank_info' => $bankName && $accountNumber && $accountName
                        ? $bankName . ' - ' . $accountNumber . ' (' . $accountName . ')'
                        : $first->user_bank_code,
                    'description' => $first->description,
                    'created_at' => $first->created_at,
                ];
            })->values();

            $vendorPayment->payment_histories = $groupedHistories;
        }

        return response()->json($vendorPayment);
    }

    /**
     * Cetak nota PDF untuk satu order (mengikuti grup nota-nya).
     */
    public function pdf($orderCode)
    {
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
            return redirect()->route('vendor.invoice.unpaid')->with('fail', 'Data not found');
        }

        $company = CompanySetting::first();
        $customer = $order->customer;

        $vendorPayment = VendorPayment::with(['paymentHistory.userBank.bank'])
            ->where('orderCode', $orderCode)
            ->first();

        if (! $vendorPayment || ! $vendorPayment->nota_number) {
            return redirect()->route('vendor.invoice.unpaid')->with('fail', 'Nomor nota belum di-generate untuk order ini. Silakan generate nota terlebih dahulu.');
        }

        $paymentHistories = collect($vendorPayment?->paymentHistory ?? []);
        $paymentHistoryTotal = $paymentHistories->sum('amount');

        $userBankCode = $vendorPayment?->user_bank_code;
        $userBank = null;
        if ($userBankCode) {
            $userBank = \App\Models\Bank\UserBank::with('bank')->where('code', $userBankCode)->first();
        }

        $pdfTemplate = 'finance.vendor-payment.pdf.general-phl';

        if ($customer->company->format == 'P') {
            $pdfTemplate = 'finance.vendor-payment.pdf.pribadi';
        }

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
                ->with('userBank', $userBank)
        );

        return $mpdf->Output('Nota-Pembayaran-' . $order->code . '.pdf', 'I');
    }

    /**
     * Cetak nota PDF untuk beberapa nota/order terpilih.
     */
    public function pdfMulti(Request $request)
    {
        $orderCodes = $request->input('orderCodes', []);

        if (is_string($orderCodes)) {
            $orderCodes = array_filter(array_map('trim', explode(',', $orderCodes)));
        }

        if (empty($orderCodes)) {
            return redirect()->route('vendor.invoice.unpaid')->with('fail', 'Tidak ada order yang dipilih');
        }

        $selectedNotaNumbers = VendorPayment::whereIn('orderCode', $orderCodes)
            ->whereNotNull('nota_number')
            ->pluck('nota_number')
            ->unique();

        if ($selectedNotaNumbers->isNotEmpty()) {
            $allOrderCodesWithSameNotas = VendorPayment::whereIn('nota_number', $selectedNotaNumbers)
                ->pluck('orderCode')
                ->toArray();

            $orderCodes = array_values(array_unique(array_merge($orderCodes, $allOrderCodesWithSameNotas)));
        }

        $vendorPayments = VendorPayment::whereIn('orderCode', $orderCodes)->get();
        if ($vendorPayments->count() < count($orderCodes) || $vendorPayments->contains(fn ($vp) => ! $vp->nota_number)) {
            return redirect()->route('vendor.invoice.unpaid')->with('fail', 'Beberapa order terpilih belum memiliki nomor nota. Silakan generate nota terlebih dahulu.');
        }

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
            return redirect()->route('vendor.invoice.unpaid')->with('fail', 'Data order tidak ditemukan');
        }

        $groupedByFormat = $orders->groupBy(function ($order) {
            return $order->customer->company->format ?? 'P';
        });

        $firstFormat = $groupedByFormat->keys()->first();
        $useGeneralTemplate = count($groupedByFormat) > 1;

        $pdfTemplate = 'finance.vendor-payment.pdf.general-phl';

        if (! $useGeneralTemplate) {
            if ($firstFormat === 'P') {
                $pdfTemplate = 'finance.vendor-payment.pdf.pribadi';
            } elseif (in_array($firstFormat, ['WTMS', 'WT'])) {
                $pdfTemplate = 'finance.vendor-payment.pdf.general-wt';
            }
        }

        $totalSubtotal = 0;
        $totalAdditionalCost = 0;
        $totalPphAmount = 0;
        $totalPpnAmount = 0;
        $totalGrandTotal = 0;

        foreach ($orders as $order) {
            $qty = (float) ($order->qty ?? 0);
            $unitPrice = (float) ($order->vendorPriceSingle ?? ($qty > 0 ? ($order->vendorPrice ?? 0) / $qty : ($order->vendorPrice ?? 0)));
            if ($unitPrice <= 0) {
                $unitPrice = (float) ($order->route->vendorPrice ?? $order->route->personalVendorPrice ?? 0);
            }
            $subtotal = (float) ($order->vendorPrice ?? ($qty * $unitPrice));
            if ($subtotal <= 0 && $qty > 0) {
                $subtotal = $qty * $unitPrice;
            }
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

        // PPN & PPh manual per nota (nilai sama di semua baris satu nota →
        // ambil MAX per nota agar tidak terhitung ganda).
        // Catatan: kolom ppn/pph ini hasil input manual saat generate nota
        // (menu Order Menunggu Nota).
        $notaTaxTotals = $vendorPayments
            ->whereNotNull('nota_number')
            ->groupBy('nota_number')
            ->map(function ($group) {
                return [
                    'ppn' => (float) $group->max('ppn_amount'),
                    'pph' => (float) $group->max('pph_amount'),
                ];
            });

        $totalPpnAmount = (float) $notaTaxTotals->sum('ppn');
        $totalPphAmount += (float) $notaTaxTotals->sum('pph');
        $totalGrandTotal += $totalPpnAmount - (float) $notaTaxTotals->sum('pph');

        $company = CompanySetting::first();
        $customerFirst = $orders->first()->customer;

        $vendorPayment = VendorPayment::whereIn('orderCode', $orderCodes)
            ->whereNotNull('nota_number')
            ->first();

        $notaNumber = $vendorPayment ? $vendorPayment->nota_number : null;
        $userBankCode = $vendorPayment ? $vendorPayment->user_bank_code : null;

        $userBank = null;
        if ($userBankCode) {
            $userBank = \App\Models\Bank\UserBank::with('bank')->where('code', $userBankCode)->first();
        }

        $vendorPayments = VendorPayment::with(['paymentHistory.userBank.bank'])
            ->whereIn('orderCode', $orderCodes)
            ->get();

        $allHistories = collect();
        foreach ($vendorPayments as $vp) {
            if ($vp->paymentHistory) {
                foreach ($vp->paymentHistory as $ph) {
                    $allHistories->push($ph);
                }
            }
        }

        $groupedHistories = $allHistories->groupBy(function ($history) {
            return $history->payment_date . '_' . $history->description . '_' . $history->user_bank_code;
        })->map(function ($group) {
            $first = $group->first();

            return (object) [
                'payment_date' => $first->payment_date,
                'description' => $first->description,
                'amount' => $group->sum('amount'),
            ];
        })->values();

        $paymentHistoryTotal = $groupedHistories->sum('amount');

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
                ->with('totalPpnAmount', $totalPpnAmount)
                ->with('totalPphAmount', $totalPphAmount)
                ->with('totalGrandTotal', $totalGrandTotal)
                ->with('notaNumber', $notaNumber)
                ->with('userBank', $userBank)
                ->with('paymentHistories', $groupedHistories)
                ->with('paymentHistoryTotal', $paymentHistoryTotal)
        );

        return $mpdf->Output('Nota-Pembayaran-Multi-' . now()->format('YmdHis') . '.pdf', 'I');
    }

    /**
     * Membatalkan pembayaran vendor (mengembalikan saldo bank, reset status).
     */
    public function destroy($orderCode)
    {
        try {
            DB::beginTransaction();

            $this->service->cancelPayment($orderCode, $this->title);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Pembayaran vendor untuk order ' . $orderCode . ' berhasil dibatalkan secara permanen.');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()
                ->with('fail', 'Gagal membatalkan pembayaran: ' . $th->getMessage());
        }
    }

    /**
     * Generate nomor nota (invoice) untuk order-order yang dipilih.
     * PPN & PPh diinput manual (nominal rupiah) dari modal generate nota.
     */
    public function generateNota(Request $request)
    {
        $data = $request->all();

        // Normalisasi input PPN/PPh: buang pemisah ribuan ("1.650.000" → 1650000)
        // agar validasi numeric tetap lolos bila nilai berformat terkirim.
        foreach (['ppnAmount', 'pphAmount'] as $taxField) {
            if (isset($data[$taxField]) && is_string($data[$taxField])) {
                $clean = str_replace(['.', ' ', ','], '', trim($data[$taxField]));

                $data[$taxField] = $clean === '' ? 0 : (float) $clean;
            }
        }

        $validator = Validator::make($data, [
            'orderCodes' => 'required|array|min:1',
            'orderCodes.*' => 'required|string',
            'userBankCode' => 'required|string|exists:user_bank,code',
            'ppnAmount' => ['nullable', 'numeric', 'min:0'],
            'pphAmount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('vendor.order.waiting')
                ->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $notaNumber = $this->service->assignNota(
                $request->orderCodes,
                $request->userBankCode,
                $this->title,
                (float) ($data['ppnAmount'] ?? 0),
                (float) ($data['pphAmount'] ?? 0)
            );

            DB::commit();

            $ppnAmount = (float) ($data['ppnAmount'] ?? 0);
            $pphAmount = (float) ($data['pphAmount'] ?? 0);
            $ppnInfo = $ppnAmount > 0 || $pphAmount > 0
                ? ' (PPN: Rp ' . number_format($ppnAmount, 0, ',', '.') . ', PPh: Rp ' . number_format($pphAmount, 0, ',', '.') . ')'
                : '';

            return redirect()->route('vendor.order.waiting')
                ->with('success', 'Nota pembayaran berhasil di-generate dengan nomor: ' . $notaNumber . $ppnInfo);
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route('vendor.order.waiting')
                ->with('fail', 'Gagal generate nota: ' . $th->getMessage());
        }
    }

    /**
     * Membatalkan nomor nota pembayaran (jika belum ada pembayaran sama sekali).
     */
    public function cancelNota($orderCode)
    {
        try {
            DB::beginTransaction();

            $this->service->cancelNota($orderCode, $this->title);

            DB::commit();

            return redirect()->route('vendor.invoice.unpaid')
                ->with('success', 'Nota pembayaran untuk order ' . $orderCode . ' berhasil dibatalkan.');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route('vendor.invoice.unpaid')
                ->with('fail', 'Gagal membatalkan nota: ' . $th->getMessage());
        }
    }
}
