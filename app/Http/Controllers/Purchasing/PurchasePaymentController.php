<?php

namespace App\Http\Controllers\Purchasing;

use App\Exports\PurchasePaymentExport;
use App\Exports\PurchaseUnpaidSummaryExport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Supplier;
use App\Services\Bank\UserBankService;
use App\Services\Inventory\SupplierService;
use App\Services\MenuService;
use App\Services\Purchasing\PurchasePaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
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

        return (Auth::user()?->languange ?? 'id') === 'id' ? $menu->nama : $menu->name;
    }

    /* ------------------------------------------------------------------ */
    /* Halaman */
    /* ------------------------------------------------------------------ */

    /**
     * Halaman utama "Hutang Supplier Belum Lunas" — rekap per supplier:
     * nama supplier + total utang. Klik supplier untuk melihat PO-nya.
     */
    public function index()
    {
        $stats = $this->service->statsUnpaid();
        $suppliers = $this->supplierSvc->findAll();

        return view($this->view.'supplier-summary')
            ->with('view', $this->view)
            ->with('suppliers', $suppliers)
            ->with('stats', $stats)
            ->with('title', $this->pageTitle('PURCHASE_PAYMENT', 'Hutang Supplier Belum Lunas'));
    }

    /**
     * Halaman daftar PO hutang milik satu supplier (level detail dari rekap).
     */
    public function supplierDetail(string $supplierCode)
    {
        $supplier = $this->supplierSvc->findAll()
            ->firstWhere('code', $supplierCode);

        if (! $supplier) {
            abort(404, 'Supplier tidak ditemukan.');
        }

        $userBank = $this->userBankSvc->findCompany();
        $stats = $this->service->statsUnpaid($supplierCode);

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('userBank', $userBank)
            ->with('stats', $stats)
            ->with('title', 'Hutang '.$supplier->name)
            ->with('pageTitle', 'Hutang Supplier Belum Lunas');
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
    /* Aksi pembayaran */
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
            'payments.*.amount' => ['required', 'integer', 'min:1', 'max:999999999999999999'],
            'payments.*.expected_remaining' => ['required', 'integer', 'min:1', 'max:999999999999999999'],
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
    /* Datatable & detail */
    /* ------------------------------------------------------------------ */

    public function datatableSupplierSummary(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['data' => []]);
        }

        $data = $this->service->filteredUnpaidBySupplier($request->all());
        if (empty($request->input('order'))) {
            $data->orderByRaw('MIN(purchase.date) asc');
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->filterColumn('supplierName', function ($query, $keyword) {
                // Builder kosong (tanpa join), jadi pakai subquery nama supplier.
                $query->whereRaw(
                    '(select s2.name from supplier s2 where s2.code = purchase.supplierCode and s2.deleted_at is null) LIKE ?',
                    ['%'.$keyword.'%']
                );
            })
            ->filterColumn('oldestPurchaseDateHtml', function ($query, $keyword) {
                $query->where('purchase.date', 'like', '%'.$keyword.'%');
            })
            ->orderColumn('oldestPurchaseDateHtml', 'oldestPurchaseDate $1')
            ->orderColumn('totalBillingHtml', 'totalBilling $1')
            ->orderColumn('totalPaidHtml', 'totalPaid $1')
            ->orderColumn('totalRemainingHtml', 'totalRemaining $1')
            ->addColumn('oldestPurchaseDateHtml', function ($row) {
                return $row->oldestPurchaseDate
                    ? Carbon::parse($row->oldestPurchaseDate)->format('d-M-Y')
                    : '-';
            })
            ->addColumn('totalBillingHtml', fn ($row) => number_format((float) $row->totalBilling, 0, ',', '.'))
            ->addColumn('totalPaidHtml', fn ($row) => number_format((float) $row->totalPaid, 0, ',', '.'))
            ->addColumn('totalRemainingHtml', function ($row) {
                $remaining = max(0, (float) $row->totalRemaining);

                return '<span class="fw-semibold text-danger">'.number_format($remaining, 0, ',', '.').'</span>';
            })
            ->addColumn('action', function ($row) {
                $url = route('purchasing.purchase-payment.supplier', $row->supplierCode);

                return '<a href="'.$url.'" class="btn btn-sm bg-primary-subtle text-primary rounded-pill px-3" '
                    .'title="Lihat daftar PO supplier ini">'
                    .'<i class="mdi mdi-file-document-multiple-outline me-1"></i>Detail</a>';
            })
            ->rawColumns(['totalRemainingHtml', 'action'])
            ->toJson();
    }

    public function datatableUnpaid(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['data' => []]);
        }

        $data = $this->service->filteredUnpaid($request->all());
        $pagePurchases = [];

        $response = DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) use (&$pagePurchases) {
                $pagePurchases[$row->code] = $row;

                return '';
            })
            ->addColumn('purchaseDate', fn ($row) => $row->date ? Carbon::parse($row->date)->format('d-M-Y') : '')
            ->addColumn('dueDateHtml', function ($row) {
                $due = $this->effectiveDueDate($row);
                $diff = now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false);
                $formatted = $due->format('d-M-Y');
                $note = $this->isDueDateEstimated($row)
                    ? '<small class="d-block text-muted fs-11">Estimasi dari tanggal PO</small>'
                    : '';

                if ($row->paymentStatus != 'Paid' && $diff < 0) {
                    return '<span class="badge bg-danger-subtle text-danger">'.$formatted.'</span>'.$note;
                }

                if ($row->paymentStatus != 'Paid' && $diff <= 7) {
                    return '<span class="badge bg-warning-subtle text-warning-emphasis">'.$formatted.'</span>'.$note;
                }

                return $formatted.$note;
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
            ->addColumn('fleetPlate', fn ($row) => $row->plate_number_display ?? '-')
            ->addColumn('transferBank', function ($row) {
                // Rekening internal yang dipilih pada Pembelian Langsung
                // (paymentType = Transfer); null untuk PO biasa.
                $bank = $row->transferUserBank;

                if (! $bank) {
                    return null;
                }

                return [
                    'code' => $bank->code,
                    'bank' => $bank->bank->name ?? 'Bank',
                    'accountNumber' => $bank->accountNumber,
                    'accountName' => $bank->accountName,
                ];
            })
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
            ->orderColumn('purchaseDate', 'purchase.date $1, purchase.time $1')
            ->orderColumn('dueDateHtml', PurchasePaymentService::effectiveDueDateSql().' $1')
            ->orderColumn('supplierName', self::supplierNameSql().' $1')
            ->orderColumn('totalPrice', 'COALESCE(NULLIF(purchase.nominal, 0), 0) $1')
            ->orderColumn('paidAmount', 'COALESCE(purchase.paidAmount, 0) $1')
            ->orderColumn('remaining', '(COALESCE(NULLIF(purchase.nominal, 0), 0) - COALESCE(purchase.paidAmount, 0)) $1')
            ->orderColumn('paymentStatusHtml', 'purchase.paymentStatus $1')
            ->filterColumn('purchaseDate', function ($query, $keyword) {
                $query->where('purchase.date', 'like', '%'.$keyword.'%');
            })
            ->filterColumn('dueDateHtml', function ($query, $keyword) {
                $query->where('purchase.dueDate', 'like', '%'.$keyword.'%');
            })
            ->filterColumn('supplierName', function ($query, $keyword) {
                $query->whereRaw(self::supplierNameSql().' LIKE ?', ['%'.$keyword.'%']);
            })
            ->filterColumn('totalPrice', function ($query, $keyword) {
                $trimmed = trim($keyword);
                if (in_array(mb_strtolower($trimmed), ['rp', 'rp.'], true)) {
                    $query->whereRaw('1 = 1');

                    return;
                }
                if (preg_match('/^(?:rp\.?\s*)?([\d.,]+)$/i', $trimmed, $m) !== 1) {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $digits = preg_replace('/\D/', '', $m[1]) ?? '';
                if ($digits === '') {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $query->whereRaw('CAST(COALESCE(purchase.nominal, 0) AS CHAR) LIKE ?', ['%'.$digits.'%']);
            })
            ->filterColumn('paidAmount', function ($query, $keyword) {
                $trimmed = trim($keyword);
                if (in_array(mb_strtolower($trimmed), ['rp', 'rp.'], true)) {
                    $query->whereRaw('1 = 1');

                    return;
                }
                if (preg_match('/^(?:rp\.?\s*)?([\d.,]+)$/i', $trimmed, $m) !== 1) {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $digits = preg_replace('/\D/', '', $m[1]) ?? '';
                if ($digits === '') {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $query->whereRaw('CAST(COALESCE(purchase.paidAmount, 0) AS CHAR) LIKE ?', ['%'.$digits.'%']);
            })
            ->filterColumn('remaining', function ($query, $keyword) {
                $trimmed = trim($keyword);
                if (in_array(mb_strtolower($trimmed), ['rp', 'rp.'], true)) {
                    $query->whereRaw('1 = 1');

                    return;
                }
                if (preg_match('/^(?:rp\.?\s*)?([\d.,]+)$/i', $trimmed, $m) !== 1) {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $digits = preg_replace('/\D/', '', $m[1]) ?? '';
                if ($digits === '') {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $query->whereRaw('CAST(GREATEST(0, COALESCE(purchase.nominal, 0) - COALESCE(purchase.paidAmount, 0)) AS CHAR) LIKE ?', ['%'.$digits.'%']);
            })
            ->filterColumn('paymentStatusHtml', function ($query, $keyword) {
                $lower = mb_strtolower(trim($keyword));
                $matched = [];
                if (str_contains('belum bayar', $lower) || str_contains('unpaid', $lower)) {
                    $matched[] = 'Unpaid';
                }
                if (str_contains('sebagian', $lower) || str_contains('partial', $lower) || $lower === 'dp') {
                    $matched[] = 'Partial';
                }
                if (str_contains('lunas', $lower) || str_contains('paid', $lower)) {
                    $matched[] = 'Paid';
                }
                if ($matched === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $query->whereIn('purchase.paymentStatus', $matched);
            })
            ->filterColumn('fleetPlate', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('fleet', function ($fleetQuery) use ($keyword) {
                        $fleetQuery->where('plateNumber', 'like', '%'.$keyword.'%');
                    })->orWhereHas('maintenances.fleet', function ($maintQuery) use ($keyword) {
                        $maintQuery->where('plateNumber', 'like', '%'.$keyword.'%');
                    });
                });
            })
            ->rawColumns(['dueDateHtml', 'paymentStatusHtml'])
            ->setRowClass(function ($row) {
                if ($row->paymentStatus != 'Paid') {
                    $due = $this->effectiveDueDate($row);
                    $diff = now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false);

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

        $payload = $response->getData(true);
        $breakdowns = $this->service->resolveVehicleBreakdownForPurchases(collect($pagePurchases));
        if (isset($payload['data']) && is_array($payload['data'])) {
            foreach ($payload['data'] as &$item) {
                $code = $item['code'] ?? '';
                $item['vehicleBreakdown'] = $breakdowns[$code] ?? [];
            }
            unset($item);
            $response->setData($payload);
        }

        return $response;
    }

    /**
     * Tanggal jatuh tempo efektif sebuah pembelian.
     *
     * Memakai `dueDate` hanya bila tahunnya wajar (2020-2030); bila tidak
     * (mis. 0026, 0206), jatuh tempo diperkirakan dari tanggal PO. Logika ini
     * sejalan dengan PurchasePaymentService::effectiveDueDateSql() agar urutan
     * tabel konsisten dengan yang ditampilkan.
     */
    protected function effectiveDueDate($row): Carbon
    {
        if ($row->dueDate) {
            $due = Carbon::parse($row->dueDate);

            if ($due->year >= 2020 && $due->year <= 2030) {
                return $due;
            }
        }

        if ($row->date) {
            $date = Carbon::parse($row->date);

            if ($date->year >= 1000 && $date->year <= 9999) {
                return $date;
            }
        }

        return Carbon::now();
    }

    /** Apakah jatuh tempo hanya perkiraan dari tanggal PO (dueDate tidak wajar). */
    protected function isDueDateEstimated($row): bool
    {
        if ($row->dueDate) {
            $year = Carbon::parse($row->dueDate)->year;

            if ($year >= 2020 && $year <= 2030) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ekspresi SQL nama supplier (subquery, tanpa join) — dipakai untuk
     * pengurutan kolom supplier pada datatable server-side.
     */
    protected static function supplierNameSql(): string
    {
        return '(select s.name from supplier s where s.code = purchase.supplierCode and s.deleted_at is null)';
    }

    public function datatablePaid(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['data' => []]);
        }

        $data = $this->service->filteredPaid($request->all());
        $pagePurchases = [];

        $response = DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) use (&$pagePurchases) {
                $pagePurchases[$row->code] = $row;

                return '';
            })
            ->addColumn('purchaseDate', fn ($row) => $row->date ? Carbon::parse($row->date)->format('d-M-Y') : '')
            ->addColumn('paymentDateHtml', fn ($row) => $row->paymentDate ? Carbon::parse($row->paymentDate)->format('d-M-Y') : '')
            ->addColumn('totalPrice', fn ($row) => number_format($this->service->billingOf($row), 0, ',', '.'))
            ->addColumn('paidAmount', fn ($row) => number_format((float) $row->paidAmount, 0, ',', '.'))
            ->editColumn('supplier.name', fn ($row) => $row->supplier->name ?? '')
            ->addColumn('fleetPlate', fn ($row) => $row->plate_number_display ?? '-')
            ->addColumn('paymentStatusHtml', fn ($row) => '<span class="badge bg-success">Paid</span>')
            ->orderColumn('purchaseDate', 'purchase.date $1, purchase.time $1')
            ->orderColumn('paymentDateHtml', 'purchase.paymentDate $1')
            ->orderColumn('totalPrice', 'COALESCE(NULLIF(purchase.nominal, 0), 0) $1')
            ->orderColumn('paidAmount', 'COALESCE(purchase.paidAmount, 0) $1')
            ->orderColumn('paymentStatusHtml', 'purchase.paymentStatus $1')
            ->filterColumn('purchaseDate', fn ($query, $keyword) => $query->where('purchase.date', 'like', '%'.$keyword.'%'))
            ->filterColumn('paymentDateHtml', fn ($query, $keyword) => $query->where('purchase.paymentDate', 'like', '%'.$keyword.'%'))
            ->filterColumn('totalPrice', function ($query, $keyword) {
                $trimmed = trim($keyword);
                if (in_array(mb_strtolower($trimmed), ['rp', 'rp.'], true)) {
                    $query->whereRaw('1 = 1');

                    return;
                }
                if (preg_match('/^(?:rp\.?\s*)?([\d.,]+)$/i', $trimmed, $m) !== 1) {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $digits = preg_replace('/\D/', '', $m[1]) ?? '';
                if ($digits === '') {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $query->whereRaw('CAST(COALESCE(purchase.nominal, 0) AS CHAR) LIKE ?', ['%'.$digits.'%']);
            })
            ->filterColumn('paidAmount', function ($query, $keyword) {
                $trimmed = trim($keyword);
                if (in_array(mb_strtolower($trimmed), ['rp', 'rp.'], true)) {
                    $query->whereRaw('1 = 1');

                    return;
                }
                if (preg_match('/^(?:rp\.?\s*)?([\d.,]+)$/i', $trimmed, $m) !== 1) {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $digits = preg_replace('/\D/', '', $m[1]) ?? '';
                if ($digits === '') {
                    $query->whereRaw('1 = 0');

                    return;
                }
                $query->whereRaw('CAST(COALESCE(purchase.paidAmount, 0) AS CHAR) LIKE ?', ['%'.$digits.'%']);
            })
            ->filterColumn('paymentStatusHtml', function ($query, $keyword) {
                $lower = mb_strtolower(trim($keyword));
                if (str_contains('lunas', $lower) || str_contains('paid', $lower)) {
                    $query->where('purchase.paymentStatus', 'Paid');

                    return;
                }
                $query->whereRaw('1 = 0');
            })
            ->filterColumn('fleetPlate', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('fleet', function ($fleetQuery) use ($keyword) {
                        $fleetQuery->where('plateNumber', 'like', '%'.$keyword.'%');
                    })->orWhereHas('maintenances.fleet', function ($maintQuery) use ($keyword) {
                        $maintQuery->where('plateNumber', 'like', '%'.$keyword.'%');
                    });
                });
            })
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-icon btn-sm bg-primary-subtle me-1 btn-detail-row" '
                    .'data-code="'.$row->code.'" data-bs-toggle="tooltip" title="Detail">'
                    .'<i class="mdi mdi-eye fs-14 text-primary"></i></button>';

                if ($row->paymentCode && $row->paymentBatch && $row->paymentBatch->status === 'active') {
                    $btn .= '<button type="button" class="btn btn-icon btn-sm bg-danger-subtle btn-cancel-row" '
                        .'data-code="'.$row->code.'" data-batch="'.$row->paymentCode.'" data-bs-toggle="tooltip" title="Batal Pembayaran">'
                        .'<i class="mdi mdi-close-circle-outline fs-14 text-danger"></i></button>';
                }

                return $btn;
            })
            ->rawColumns(['paymentStatusHtml', 'action'])
            ->toJson();

        $payload = $response->getData(true);
        $breakdowns = $this->service->resolveVehicleBreakdownForPurchases(collect($pagePurchases));
        if (isset($payload['data']) && is_array($payload['data'])) {
            foreach ($payload['data'] as &$item) {
                $code = $item['code'] ?? '';
                $item['vehicleBreakdown'] = $breakdowns[$code] ?? [];
            }
            unset($item);
            $response->setData($payload);
        }

        return $response;
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

    /* ------------------------------------------------------------------ */
    /* Export Excel & PDF */
    /* ------------------------------------------------------------------ */

    public function exportSummaryExcel(Request $request)
    {
        return Excel::download(
            new PurchaseUnpaidSummaryExport($request->all(), $this->service),
            'Rekap-Hutang-Supplier-Belum-Lunas-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportSummaryPdf(Request $request)
    {
        $suppliers = $this->service->filteredUnpaidBySupplier($request->all())->get();
        $filterSummary = $this->buildPaymentFilterSummary($request);

        return $this->renderPdf(
            view($this->view.'report.purchase-unpaid-summary-pdf', [
                'suppliers' => $suppliers,
                'title' => 'Rekap Hutang Supplier Belum Lunas',
                'filterSummary' => $filterSummary,
                'printedAt' => now(),
            ])->render(),
            'Rekap-Hutang-Supplier-Belum-Lunas-'.now()->format('YmdHis').'.pdf'
        );
    }

    public function exportUnpaidExcel(Request $request)
    {
        return Excel::download(
            new PurchasePaymentExport($request->all(), 'unpaid', $this->service),
            'Hutang-Supplier-Belum-Lunas-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportUnpaidPdf(Request $request)
    {
        $purchases = $this->service->filteredUnpaid($request->all())->get();
        $filterSummary = $this->buildPaymentFilterSummary($request);

        return $this->renderChunkedPurchasePdf(
            $this->view.'report.purchase-unpaid-pdf',
            $purchases,
            'Daftar Hutang Supplier Belum Lunas',
            $filterSummary,
            'Hutang-Supplier-Belum-Lunas-'.now()->format('YmdHis').'.pdf',
            true
        );
    }

    public function exportPaidExcel(Request $request)
    {
        return Excel::download(
            new PurchasePaymentExport($request->all(), 'paid', $this->service),
            'Hutang-Supplier-Lunas-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportPaidPdf(Request $request)
    {
        $purchases = $this->service->filteredPaid($request->all())->get();
        $filterSummary = $this->buildPaymentFilterSummary($request);

        return $this->renderChunkedPurchasePdf(
            $this->view.'report.purchase-paid-pdf',
            $purchases,
            'Daftar Hutang Supplier Lunas',
            $filterSummary,
            'Hutang-Supplier-Lunas-'.now()->format('YmdHis').'.pdf',
            false
        );
    }

    private function buildPaymentFilterSummary(Request $request): array
    {
        $summary = [];

        if ($request->filled('code')) {
            $summary[] = 'Kode PO: '.$request->code;
        }
        if ($request->filled('supplierCode')) {
            $supplierName = Supplier::query()->where('code', $request->supplierCode)->value('name');
            $summary[] = 'Supplier: '.($supplierName ?: $request->supplierCode);
        }
        if (in_array($request->paymentStatus, ['Unpaid', 'Partial'], true)) {
            $summary[] = 'Status: '.($request->paymentStatus === 'Partial' ? 'Belum Lunas (DP)' : 'Belum Bayar');
        }
        if ($request->filled('startDate') || $request->filled('endDate')) {
            $start = $request->filled('startDate') ? Carbon::parse($request->startDate)->format('d-m-Y') : 'Awal';
            $end = $request->filled('endDate') ? Carbon::parse($request->endDate)->format('d-m-Y') : 'Sekarang';
            $summary[] = 'Periode: '.$start.' s/d '.$end;
        }

        return $summary;
    }

    private function createMpdf(): Mpdf
    {
        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'default_font' => 'sans-serif',
        ]);
    }

    private function renderPdf(string $html, string $filename)
    {
        $mpdf = $this->createMpdf();
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }

    /**
     * Render PDF daftar hutang secara bertahap (chunk 250 baris per WriteHTML)
     * agar tidak menabrak limit pcre.backtrack_limit mPDF saat data ribuan baris.
     */
    private function renderChunkedPurchasePdf(
        string $viewName,
        $purchases,
        string $title,
        array $filterSummary,
        string $filename,
        bool $includeRemaining
    ) {
        $mpdf = $this->createMpdf();
        $printedAt = now();

        $sumBilling = 0.0;
        $sumPaid = 0.0;
        $sumRemaining = 0.0;

        foreach ($purchases as $row) {
            $billing = (float) $this->service->billingOf($row);
            $paid = (float) ($row->paidAmount ?? 0);
            $sumBilling += $billing;
            $sumPaid += $paid;
            if ($includeRemaining) {
                $sumRemaining += max(0, $billing - $paid);
            }
        }

        $vehicleBreakdowns = $this->service->resolveVehicleBreakdownForPurchases($purchases);

        $baseData = [
            'service' => $this->service,
            'vehicleBreakdowns' => $vehicleBreakdowns,
            'title' => $title,
            'filterSummary' => $filterSummary,
            'printedAt' => $printedAt,
            'totalCount' => $purchases->count(),
            'sumBilling' => $sumBilling,
            'sumPaid' => $sumPaid,
            'sumRemaining' => $sumRemaining,
        ];

        $mpdf->WriteHTML(view($viewName, array_merge($baseData, [
            'section' => 'header',
            'purchases' => $purchases,
        ]))->render());

        if ($purchases->isEmpty()) {
            $mpdf->WriteHTML(view($viewName, array_merge($baseData, [
                'section' => 'rows',
                'purchases' => $purchases,
                'startIndex' => 0,
            ]))->render());
        } else {
            $offset = 0;
            foreach ($purchases->chunk(250) as $chunk) {
                $mpdf->WriteHTML(view($viewName, array_merge($baseData, [
                    'section' => 'rows',
                    'purchases' => $chunk,
                    'startIndex' => $offset,
                ]))->render());
                $offset += $chunk->count();
            }
        }

        $mpdf->WriteHTML(view($viewName, array_merge($baseData, [
            'section' => 'footer',
            'purchases' => $purchases,
        ]))->render());

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }
}
