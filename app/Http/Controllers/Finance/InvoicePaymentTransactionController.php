<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Invoice as InvoiceModel;
use App\Services\Bank\UserBankService;
use App\Services\Finance\InvoicePaymentTransactionService;
use App\Services\Master\CustomerService;
use App\Services\MenuService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class InvoicePaymentTransactionController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $customerSvc;

    protected $userBankSvc;

    public function __construct(InvoicePaymentTransactionService $service, CustomerService $customerSvc, UserBankService $userBankSvc, MenuService $menuSvc)
    {
        $this->service = $service;
        $this->title = 'Payment Transaction';
        $this->view = 'invoice.payment-transaction.';
        $this->customerSvc = $customerSvc;
        $this->userBankSvc = $userBankSvc;
        $this->menuSvc = $menuSvc;
    }

    /**
     * Daftar transaksi pembayaran (1 baris = 1 transaksi transfer).
     */
    public function index()
    {
        $transactionCount = DB::table('invoice_payment_transaction')->whereNull('deleted_at')->count();
        $totalReceived = (float) DB::table('invoice_payment_transaction')->whereNull('deleted_at')->sum('amount');
        $totalClaim = (float) DB::table('invoice_payment_transaction')->whereNull('deleted_at')->sum('totalClaim');

        $stats = [
            'transactionCount' => $transactionCount,
            'totalReceived' => $totalReceived,
            'totalClaim' => $totalClaim,
        ];

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('stats', $stats);
    }

    /**
     * Form transaksi pembayaran baru: pilih customer -> centang faktur -> nominal & claim.
     */
    public function create()
    {
        $customer = $this->customerSvc->findAll();
        $userBank = $this->userBankSvc->findCompany();

        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('customer', $customer)
            ->with('userBank', $userBank);
    }

    /**
     * Simpan transaksi pembayaran.
     */
    public function store(Request $request)
    {
        // Normalisasi format nominal ribuan (titik/koma) sebelum validasi
        $invoices = $request->input('invoices');
        if (is_array($invoices)) {
            foreach ($invoices as $idx => $item) {
                if (isset($item['amount']) && is_string($item['amount'])) {
                    $cleanAmount = str_replace('.', '', $item['amount']);
                    $cleanAmount = str_replace(',', '.', $cleanAmount);
                    $invoices[$idx]['amount'] = $cleanAmount !== '' ? (float) $cleanAmount : 0;
                }
                if (isset($item['claim']) && is_string($item['claim'])) {
                    $cleanClaim = str_replace('.', '', $item['claim']);
                    $cleanClaim = str_replace(',', '.', $cleanClaim);
                    $invoices[$idx]['claim'] = $cleanClaim !== '' ? (float) $cleanClaim : 0;
                }
            }
            $request->merge(['invoices' => $invoices]);
        }

        $validator = Validator::make($request->all(), [
            'customerCode' => ['required'],
            'paymentDate' => ['required', 'date'],
            'userBankCode' => ['required'],
            'invoices' => ['required', 'array', 'min:1'],
            'invoices.*.code' => ['required'],
            'invoices.*.amount' => ['nullable', 'numeric', 'min:0'],
            'invoices.*.claim' => ['nullable', 'numeric', 'min:0'],
            'invoices.*.claimDescription' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'paymentReceipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], [
            'customerCode.required' => 'Customer harus dipilih',
            'paymentDate.required' => 'Tanggal pembayaran harus diisi',
            'userBankCode.required' => 'Rekening bank penerima harus dipilih',
            'invoices.required' => 'Pilih minimal satu faktur',
        ]);

        if ($validator->fails()) {
            return redirect()->route('invoice.payment-transaction.create')
                ->withInput()
                ->with('fail', $validator->errors()->all()[0]);
        }

        try {
            $transaction = $this->service->store($request);

            return redirect()->route('invoice.payment-transaction.show', $transaction->id)
                ->with('success', 'Transaksi pembayaran berhasil disimpan ('.number_format($transaction->amount, 0, ',', '.').')');
        } catch (\Throwable $th) {
            return redirect()->route('invoice.payment-transaction.create')
                ->withInput()
                ->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    /**
     * Detail transaksi pembayaran: daftar faktur + claim di dalamnya.
     */
    public function show(string $id)
    {
        $transaction = $this->service->getById($id);

        if (! $transaction) {
            return redirect()->route('invoice.payment-transaction.index')->with('fail', 'Data not found');
        }

        // Gabungkan invoice dari alokasi pembayaran + claim
        $invoiceCodes = $transaction->involvedInvoiceCodes();
        $invoices = InvoiceModel::with(['payments', 'claims'])
            ->whereIn('code', $invoiceCodes)
            ->get()
            ->keyBy('code');

        $rows = [];
        $sumBilling = 0;
        $sumPaidInTrx = 0;
        $sumClaimInTrx = 0;

        foreach ($invoiceCodes as $code) {
            $invoice = $invoices->get($code);
            if (! $invoice) {
                continue;
            }

            $billing = (float) (($invoice->invoiceAmount ?? 0) + ($invoice->ppnAmount ?? 0) - ($invoice->pphAmount ?? 0));
            $paidInTrx = (float) $transaction->payments->where('invoiceCode', $code)->sum('amount');
            $claimInTrx = (float) $transaction->claims->where('invoiceCode', $code)->sum('amount');
            $paidTotal = (float) $invoice->payments->sum('amount');
            $claimTotal = (float) $invoice->claims->sum('amount');
            $remaining = max($billing - $paidTotal - $claimTotal, 0);

            $sumBilling += $billing;
            $sumPaidInTrx += $paidInTrx;
            $sumClaimInTrx += $claimInTrx;

            $rows[] = [
                'invoiceNumber' => $invoice->invoiceNumber ?: $invoice->code,
                'invoiceDate' => $invoice->invoiceDate,
                'billing' => $billing,
                'paidInTrx' => $paidInTrx,
                'claimInTrx' => $claimInTrx,
                'paidTotal' => $paidTotal,
                'claimTotal' => $claimTotal,
                'remaining' => $remaining,
                'status' => (int) ($invoice->status ?? InvoiceModel::STATUS_CREATE),
            ];
        }

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('transaction', $transaction)
            ->with('rows', $rows)
            ->with('sumBilling', $sumBilling)
            ->with('sumPaidInTrx', $sumPaidInTrx)
            ->with('sumClaimInTrx', $sumClaimInTrx);
    }

    /**
     * Daftar faktur belum lunas milik customer (JSON, untuk form transaksi).
     */
    public function customerInvoices(Request $request, string $customerCode)
    {
        $invoices = $this->service->getOpenInvoicesByCustomer($customerCode);

        return response()->json(
            $invoices->map(function ($invoice) {
                $billing = (float) (($invoice->invoiceAmount ?? 0) + ($invoice->ppnAmount ?? 0) - ($invoice->pphAmount ?? 0));
                $totalPaid = (float) $invoice->payments->sum('amount');
                $totalClaim = (float) $invoice->claims->sum('amount');

                return [
                    'code' => $invoice->code,
                    'invoiceNumber' => $invoice->invoiceNumber ?: $invoice->code,
                    'invoiceDate' => $invoice->invoiceDate ? Carbon::parse($invoice->invoiceDate)->format('d M Y') : '-',
                    'totalBilling' => $billing,
                    'totalPaid' => $totalPaid,
                    'totalClaim' => $totalClaim,
                    'remaining' => max($billing - $totalPaid - $totalClaim, 0),
                    'status' => (int) ($invoice->status ?? InvoiceModel::STATUS_CREATE),
                ];
            })
        );
    }

    /**
     * Datatable daftar transaksi pembayaran.
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('DT_RowIndex', function ($row) {
                    return '<span class="text-muted fw-semibold fs-12">'.($row->DT_RowIndex ?? '').'</span>';
                })
                ->editColumn('code', function ($row) {
                    $url = route('invoice.payment-transaction.show', $row->id);
                    $claimBadge = '';
                    if ((float) ($row->totalClaim ?? 0) > 0) {
                        $claimBadge = ' <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill fs-10 px-2 py-0" title="Mengandung claim pengurang tagihan"><i class="mdi mdi-alert-circle-outline me-1"></i>Claim</span>';
                    }

                    return '<div class="text-start">'
                        .'<a href="'.$url.'" class="font-monospace fw-bold text-primary fs-13 text-nowrap text-decoration-none" title="Lihat detail transaksi">'.htmlspecialchars($row->code ?? '-').'</a>'
                        .$claimBadge
                        .'</div>';
                })
                ->editColumn('customer.name', function ($row) {
                    $customer = isset($row->customer->name) ? $row->customer->name : '-';
                    $code = $row->customerCode ? '<div class="text-muted font-monospace fs-11"><i class="mdi mdi-account-outline me-1"></i>'.htmlspecialchars($row->customerCode).'</div>' : '';

                    return '<div class="text-start"><span class="fw-semibold text-dark fs-13 d-block text-truncate" style="max-width: 220px;" title="'.htmlspecialchars($customer).'">'.htmlspecialchars($customer).'</span>'.$code.'</div>';
                })
                ->editColumn('paymentDate', function ($row) {
                    if (! $row->paymentDate) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="fw-medium text-dark fs-12 text-nowrap">'.Carbon::parse($row->paymentDate)->format('d M Y').'</span>';
                })
                ->addColumn('receivingBank', function ($row) {
                    if ($row->userBank) {
                        $bankName = $row->userBank->bank->name ?? 'Bank';
                        $acc = $row->userBank->accountNumber ?? '';

                        return '<div class="text-start"><span class="badge bg-light text-dark border font-monospace fs-12"><i class="mdi mdi-bank me-1 text-primary"></i>'.htmlspecialchars($bankName).'</span><div class="text-muted fs-11 font-monospace mt-1">'.htmlspecialchars($acc).'</div></div>';
                    }

                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('invoiceCount', function ($row) {
                    $count = $row->involvedInvoiceCodes()->count();

                    return '<span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-file-document-multiple-outline me-1"></i>'.$count.' Faktur</span>';
                })
                ->addColumn('totalClaim', function ($row) {
                    $claim = (float) ($row->totalClaim ?? 0);
                    if ($claim > 0) {
                        return '<div class="text-end fw-semibold text-warning-emphasis fs-13 font-monospace">- Rp '.number_format($claim, 0, ',', '.').'</div>';
                    }

                    return '<div class="text-end text-muted font-monospace fs-12 opacity-50">-</div>';
                })
                ->editColumn('amount', function ($row) {
                    return '<div class="text-end fw-bold text-success fs-13 font-monospace">Rp '.number_format((float) ($row->amount ?? 0), 0, ',', '.').'</div>';
                })
                ->addColumn('status', function ($row) {
                    $invoices = $row->involvedInvoices();

                    if ($invoices->isNotEmpty() && $invoices->every(fn ($inv) => (int) ($inv->status ?? InvoiceModel::STATUS_CREATE) === InvoiceModel::STATUS_FULL)) {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-check-circle me-1"></i>Selesai</span>';
                    }

                    if ($invoices->isNotEmpty()) {
                        return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-clock-check-outline me-1"></i>Sebagian</span>';
                    }

                    return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-file-document-outline me-1"></i>Draft</span>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('invoice.payment-transaction.show', $row->id);

                    return '<div class="d-inline-flex align-items-center gap-1">'
                        .'<a href="'.$url.'" class="btn btn-icon btn-sm bg-info-subtle text-info border border-info-subtle hover-scale" data-bs-toggle="tooltip" title="Detail Transaksi">'
                        .'<i class="mdi mdi-eye-outline fs-14"></i>'
                        .'</a>'
                        .'</div>';
                })
                ->rawColumns(['DT_RowIndex', 'code', 'customer.name', 'paymentDate', 'receivingBank', 'invoiceCount', 'totalClaim', 'amount', 'status', 'action'])
                ->toJson();
        }
    }
}
