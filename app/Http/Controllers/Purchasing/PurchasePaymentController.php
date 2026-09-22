<?php

namespace App\Http\Controllers\Purchasing;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Services\Bank\UserBankService;
use App\Services\Inventory\SupplierService;
use App\Services\MenuService;
use App\Services\Purchasing\PurchasePaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Yajra\DataTables\DataTables;

class PurchasePaymentController extends Controller
{
    protected $service;

    protected $supplierSvc;

    protected $userBankSvc;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(
        PurchasePaymentService $purchasePaymentSvc,
        SupplierService $supplierSvc,
        UserBankService $userBankSvc,
        MenuService $menuSvc
    ) {
        $this->service = $purchasePaymentSvc;
        $this->supplierSvc = $supplierSvc;
        $this->userBankSvc = $userBankSvc;
        $this->menuSvc = $menuSvc;
        $this->view = 'purchasing.purchase-payment.';
        $this->title = 'Purchase Payment';
    }

    /**
     * Judul halaman dari data menu (aman dari bentrok nama).
     */
    private function pageTitle(string $menuCode, string $fallback): string
    {
        $menu = $this->menuSvc->getByCode($menuCode);

        if (! $menu) {
            return $fallback;
        }

        return Auth::user()->languange == 'id' ? $menu->nama : $menu->name;
    }

    /* ------------------------------------------------------------------ */
    /* Halaman                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Halaman "Hutang Supplier Belum Lunas" — bayar satu / banyak pembelian.
     */
    public function index()
    {
        $supplier = $this->supplierSvc->findAll();
        $userBank = $this->userBankSvc->findCompany();
        $stats = $this->service->statsUnpaid();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('userBank', $userBank)
            ->with('stats', $stats)
            ->with('title', $this->pageTitle('PURCHASE_PAYMENT', 'Hutang Supplier Belum Lunas'));
    }

    /**
     * Halaman "Hutang Supplier Lunas" — arsip + cetak + batal pembayaran.
     */
    public function paid()
    {
        $supplier = $this->supplierSvc->findAll();
        $stats = $this->service->statsPaid();

        return view($this->view.'paid')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('stats', $stats)
            ->with('title', $this->pageTitle('PURCHASE_PAID', 'Hutang Supplier Lunas'));
    }

    /* ------------------------------------------------------------------ */
    /* Aksi pembayaran                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Simpan pembayaran banyak pembelian sekaligus (DP / cicilan / lunas).
     */
    public function storeBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requestKey' => ['required', 'uuid'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.purchase_code' => ['required', 'string', 'distinct'],
            'payments.*.amount' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'payments.*.expected_remaining' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'date' => ['required', 'date'],
            'userBankCode' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->route($this->view.'index')->with('fail', $message);
        }

        $request->merge($validator->validated());

        try {
            $result = DB::transaction(fn () => $this->service->storeBatch($request, $this->title));

            $message = $result['idempotent']
                ? 'Pembayaran sebelumnya berhasil ditemukan.'
                : $result['purchase_count'].' pembelian berhasil dibayar.';
            $message .= ' Kode pembayaran: '.$result['batch_code'].'.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message, 'result' => $result]);
            }

            return redirect()->route($this->view.'index')->with('success', $message);
        } catch (Throwable $th) {
            $result = null;

            try {
                $result = $this->service->findBatchResultByRequest($request);
            } catch (Throwable $lookupException) {
                report($lookupException);
            }

            if ($result) {
                $message = 'Pembayaran sebelumnya berhasil ditemukan. Kode pembayaran: '.$result['batch_code'].'.';

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => true, 'message' => $message, 'result' => $result]);
                }

                return redirect()->route($this->view.'index')->with('success', $message);
            }

            if ($th instanceof \DomainException) {
                $status = in_array((int) $th->getCode(), [409, 422], true) ? (int) $th->getCode() : 422;

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $th->getMessage()], $status);
                }

                return redirect()->route($this->view.'index')->with('fail', $th->getMessage());
            }

            report($th);
            $message = 'Pembayaran gagal diproses. Silakan coba lagi.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }

            return redirect()->route($this->view.'index')->with('fail', $message);
        }
    }

    /**
     * Batalkan satu batch pembayaran (kembalikan saldo + status pembelian).
     */
    public function cancelPayment(Request $request, string $batchCode)
    {
        try {
            $result = DB::transaction(fn () => $this->service->cancelPayment($batchCode, $this->title));

            $message = 'Pembayaran '.$result['batch_code'].' berhasil dibatalkan. '
                .'Saldo dikembalikan Rp '.number_format($result['reversed_amount'], 0, ',', '.').'.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route($this->view.'paid')->with('success', $message);
        } catch (Throwable $th) {
            if ($th instanceof \DomainException) {
                $status = in_array((int) $th->getCode(), [409, 422], true) ? (int) $th->getCode() : 422;

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $th->getMessage()], $status);
                }

                return redirect()->route($this->view.'paid')->with('fail', $th->getMessage());
            }

            report($th);
            $message = 'Pembatalan gagal diproses. Silakan coba lagi.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }

            return redirect()->route($this->view.'paid')->with('fail', $message);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Datatable & detail                                                 */
    /* ------------------------------------------------------------------ */

    public function datatableUnpaid(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['data' => []]);
        }

        $data = $this->service->datatableUnpaid();

        if (in_array($request->paymentStatus, ['Unpaid', 'Partial'], true)) {
            $data->where('paymentStatus', $request->paymentStatus);
        }

        $filters = [
            'code' => $request->code,
            'supplierCode' => $request->supplierCode,
        ];

        $dateFilters = [
            'date' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $data = FilterHelper::applyFilters($data, $filters, [], $dateFilters);

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('purchaseDate', fn ($row) => $row->date ? Carbon::parse($row->date)->format('d-M-Y') : '')
            ->addColumn('dueDateHtml', function ($row) {
                if (! $row->dueDate) {
                    return '<span class="text-muted">-</span>';
                }

                $due = Carbon::parse($row->dueDate);
                $diff = now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false);
                $formatted = $due->format('d-M-Y');

                if ($row->paymentStatus != 'Paid' && $diff < 0) {
                    return '<span class="badge bg-danger-subtle text-danger">'.$formatted.'</span>';
                }

                if ($row->paymentStatus != 'Paid' && $diff <= 7) {
                    return '<span class="badge bg-warning-subtle text-warning-emphasis">'.$formatted.'</span>';
                }

                return $formatted;
            })
            ->addColumn('totalPrice', fn ($row) => number_format($this->service->billingOf($row), 0, ',', '.'))
            ->addColumn('paidAmount', fn ($row) => number_format((float) $row->paidAmount, 0, ',', '.'))
            ->addColumn('remaining', function ($row) {
                $remaining = max(0, $this->service->billingOf($row) - (float) $row->paidAmount);

                return number_format($remaining, 0, ',', '.');
            })
            ->addColumn('remainingRaw', function ($row) {
                return (int) round(max(0, $this->service->billingOf($row) - (float) $row->paidAmount));
            })
            ->addColumn('billingRaw', fn ($row) => $this->service->billingOf($row))
            ->editColumn('supplier.name', fn ($row) => $row->supplier->name ?? '')
            ->addColumn('paymentStatusHtml', function ($row) {
                if ($row->paymentStatus == 'Paid') {
                    return '<span class="badge bg-success">Paid</span>';
                }

                if ($row->paymentStatus == 'Partial') {
                    return '<span class="badge bg-warning text-dark">Partial</span>';
                }

                return '<span class="badge bg-secondary">Unpaid</span>';
            })
            ->rawColumns(['dueDateHtml', 'paymentStatusHtml'])
            ->setRowClass(function ($row) {
                if ($row->dueDate && $row->paymentStatus != 'Paid') {
                    $diff = now()->startOfDay()->diffInDays(Carbon::parse($row->dueDate)->startOfDay(), false);

                    if ($diff < 0) {
                        return 'table-danger';
                    }

                    if ($diff <= 7) {
                        return 'table-warning';
                    }
                }

                return '';
            })
            ->toJson();
    }

    public function datatablePaid(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['data' => []]);
        }

        $data = $this->service->datatablePaid();

        $filters = [
            'code' => $request->code,
            'supplierCode' => $request->supplierCode,
        ];

        $dateFilters = [
            'date' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $data = FilterHelper::applyFilters($data, $filters, [], $dateFilters);

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('purchaseDate', fn ($row) => $row->date ? Carbon::parse($row->date)->format('d-M-Y') : '')
            ->addColumn('paymentDateHtml', fn ($row) => $row->paymentDate ? Carbon::parse($row->paymentDate)->format('d-M-Y') : '')
            ->addColumn('totalPrice', fn ($row) => number_format($this->service->billingOf($row), 0, ',', '.'))
            ->addColumn('paidAmount', fn ($row) => number_format((float) $row->paidAmount, 0, ',', '.'))
            ->editColumn('supplier.name', fn ($row) => $row->supplier->name ?? '')
            ->addColumn('paymentStatusHtml', fn ($row) => '<span class="badge bg-success">Paid</span>')
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-icon btn-sm bg-primary-subtle me-1 btn-detail-row" '
                    .'data-code="'.$row->code.'" data-bs-toggle="tooltip" title="Detail">'
                    .'<i class="mdi mdi-eye fs-14 text-primary"></i></button>';

                if ($row->paymentCode && $row->paymentBatch && $row->paymentBatch->status === 'active') {
                    $btn .= '<button type="button" class="btn btn-icon btn-sm bg-danger-subtle btn-cancel-row" '
                        .'data-code="'.$row->code.'" data-batch="'.$row->paymentCode.'" data-bs-toggle="tooltip" title="Batal Pembayaran">'
                        .'<i class="mdi mdi-close-circle-outline fs-14 text-danger"></i></button>';
                }

                return '<td>'.$btn.'</td>';
            })
            ->rawColumns(['paymentStatusHtml', 'action'])
            ->toJson();
    }

    /**
     * Detail pembelian + riwayat pembayaran (untuk modal detail).
     */
    public function detail(string $purchaseCode)
    {
        $data = $this->service->findPaymentDetail($purchaseCode);

        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        return response()->json($data);
    }
}
