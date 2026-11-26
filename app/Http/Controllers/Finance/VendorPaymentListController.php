<?php

namespace App\Http\Controllers\Finance;

use App\Exports\VendorPaymentListExport;
use App\Http\Controllers\Controller;
use App\Models\Bank\UserBank;
use App\Models\Master\FleetCompany;
use App\Services\Finance\VendorPaymentService;
use App\Services\Master\MenuService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

/**
 * Controller menu Vendor → Daftar Pembayaran.
 *
 * Menampilkan seluruh transaksi pembayaran ke vendor eksternal.
 * 1 baris = 1 kode transaksi; rinciannya dikelompokkan per nota lalu order.
 */
class VendorPaymentListController extends Controller
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
        $this->view = 'vendor.payment.';
    }

    /**
     * Halaman Daftar Pembayaran (Vendor).
     */
    public function index()
    {
        $stats = $this->service->statsPayments();

        $menu = $this->menuSvc->getByCode('VENDOR_PAY_LIST');
        $title = $menu
            ? ((Auth::user()?->languange ?? 'id') === 'id' ? $menu->nama : $menu->name)
            : 'Daftar Pembayaran';

        $fleetCompanies = FleetCompany::query()->orderBy('name')->get(['code', 'name', 'type']);
        $userBanks = UserBank::with('bank')->where('type', 2)->orderBy('accountName')->get();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $title)
            ->with('stats', $stats)
            ->with('fleetCompanies', $fleetCompanies)
            ->with('userBanks', $userBanks);
    }

    /**
     * Datatable daftar pembayaran vendor: 1 baris = 1 kode transaksi.
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findPayments($request->all());

            return DataTables::of($data)
                ->editColumn('payment_date', function ($row) {
                    if (! $row->payment_date) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="fw-medium text-dark fs-12 text-nowrap">' . Carbon::parse($row->payment_date)->format('d M Y') . '</span>';
                })
                // Kolom pertama (paling kiri): tombol perluas rincian transaksi.
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center js-toggle-detail"'
                        . ' style="width: 34px; height: 34px;"'
                        . ' data-transaction-key="' . e($row->transaction_key) . '"'
                        . ' title="Lihat rincian nota & order"'
                        . ' aria-label="Lihat rincian nota & order">'
                        . '<i class="mdi mdi-chevron-down fs-18"></i></button>';
                })
                ->addColumn('batch_code', function ($row) {
                    $code = $row->batch_code ?: $row->legacy_code;

                    if (! $code) {
                        return '<span class="text-muted">-</span>';
                    }

                    $label = '<span class="font-monospace fw-bold text-primary fs-12 text-nowrap">' . e($code) . '</span>';

                    return $row->is_legacy
                        ? $label . '<div class="text-muted fs-11">Arsip transaksi lama</div>'
                        : $label;
                })
                ->addColumn('nota_orders', function ($row) {
                    if ($row->notas->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }

                    $badges = $row->notas->map(function ($nota) {
                        return '<span class="badge rounded-pill border border-primary-subtle bg-primary-subtle text-primary font-monospace fw-semibold fs-11 px-2 py-1 text-start">' . e($nota->number) . '</span>';
                    })->implode('');

                    // Teks tersembunyi tetap disertakan agar pencarian global mencocokkan kode order.
                    $searchableText = $row->notas->flatMap(function ($nota) {
                        return collect([$nota->number])->merge($nota->orders->pluck('code'));
                    })->filter()->implode(' ');

                    return '<div class="d-flex flex-column align-items-start gap-1">'
                        . '<div class="d-flex flex-wrap gap-1">' . $badges . '</div>'
                        . '<span class="text-muted fs-11">' . $row->order_count . ' order</span>'
                        . '</div>'
                        . '<span class="d-none">' . e($searchableText) . '</span>';
                })
                ->addColumn('vendor', function ($row) {
                    if ($row->vendors->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="text-start">' . $row->vendors->map(function ($vendor) {
                        return '<span class="fw-semibold text-dark fs-12 d-block text-truncate" style="max-width: 180px;" title="' . e($vendor) . '">' . e($vendor) . '</span>';
                    })->implode('') . '</div>';
                })
                ->editColumn('amount', function ($row) {
                    return '<span class="fw-bold text-dark fs-13 text-nowrap">Rp ' . number_format((float) $row->amount, 0, ',', '.') . '</span>';
                })
                ->addColumn('bank', function ($row) {
                    if ($row->userBank) {
                        $bankName = $row->userBank->bank->name ?? 'Bank';
                        $accountNumber = $row->userBank->accountNumber ?? '';
                        $accountName = $row->userBank->accountName ?? '';

                        return '<div class="text-start"><span class="fw-semibold text-dark fs-12">' . e($bankName) . '</span><div class="text-muted font-monospace fs-11">' . e($accountNumber) . '</div></div>';
                    }

                    return '<span class="text-muted">-</span>';
                })
                ->editColumn('description', function ($row) {
                    return $row->description
                        ? '<span class="text-muted fs-12">' . e($row->description) . '</span>'
                        : '<span class="text-muted">-</span>';
                })
                ->rawColumns(['action', 'payment_date', 'batch_code', 'nota_orders', 'vendor', 'amount', 'bank', 'description'])
                ->toJson();
        }
    }

    /** Rincian transaksi: nota lalu order. */
    public function detail(string $transactionKey)
    {
        $transaction = $this->service->findPaymentDetail($transactionKey);

        if (! $transaction) {
            return response()->json(['message' => 'Transaksi pembayaran tidak ditemukan.'], 404);
        }

        $bank = $transaction->userBank;

        return response()->json([
            'code' => $transaction->batch_code ?: $transaction->legacy_code,
            'is_legacy' => $transaction->is_legacy,
            'payment_date' => $transaction->payment_date,
            'amount' => $transaction->amount,
            'description' => $transaction->description,
            'bank' => $bank ? [
                'name' => $bank->bank?->name ?: 'Bank',
                'account_number' => $bank->accountNumber,
                'account_name' => $bank->accountName,
            ] : null,
            'notas' => $transaction->notas->map(fn ($nota) => [
                'number' => $nota->number,
                'amount' => $nota->amount,
                'orders' => $nota->orders->map(fn ($order) => [
                    'code' => $order->code,
                    'shipment_number' => $order->shipment_number,
                    'vendor_name' => $order->vendor_name,
                    'amount' => $order->amount,
                ])->values(),
            ])->values(),
        ]);
    }

    /**
     * Export Excel Daftar Pembayaran Vendor.
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(
            new VendorPaymentListExport($request->all(), $this->service),
            'Daftar-Pembayaran-Vendor-'.now()->format('YmdHis').'.xlsx'
        );
    }

    /**
     * Export PDF Daftar Pembayaran Vendor.
     */
    public function exportPdf(Request $request)
    {
        $payments = $this->service->findPayments($request->all());
        $menu = $this->menuSvc->getByCode('VENDOR_PAY_LIST');
        $title = $menu
            ? ((Auth::user()?->languange ?? 'id') === 'id' ? $menu->nama : $menu->name)
            : 'Daftar Pembayaran Vendor';

        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'default_font' => 'sans-serif',
        ]);

        $mpdf->WriteHTML(view('vendor.payment.report.payment-list-pdf', [
            'payments' => $payments,
            'title' => $title,
            'filterSummary' => $this->buildFilterSummary($request),
            'printedAt' => now(),
        ])->render());

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Daftar-Pembayaran-Vendor-'.now()->format('YmdHis').'.pdf"');
    }

    private function buildFilterSummary(Request $request): array
    {
        $summary = [];

        if ($request->filled('startDate') || $request->filled('endDate')) {
            $start = $request->filled('startDate') ? Carbon::parse($request->startDate)->format('d-m-Y') : 'Awal';
            $end = $request->filled('endDate') ? Carbon::parse($request->endDate)->format('d-m-Y') : 'Sekarang';
            $summary[] = 'Periode Bayar: '.$start.' s/d '.$end;
        }

        if ($request->filled('fleetCompanyCode')) {
            $name = FleetCompany::where('code', $request->fleetCompanyCode)->value('name') ?: $request->fleetCompanyCode;
            $summary[] = 'Vendor: '.$name;
        }

        if ($request->filled('userBankCode')) {
            $bank = UserBank::with('bank')->where('code', $request->userBankCode)->first();
            $bankLabel = $bank
                ? trim(($bank->bank->name ?? 'Bank').($bank->accountNumber ? ' - '.$bank->accountNumber : ''))
                : $request->userBankCode;
            $summary[] = 'Bank Sumber Dana: '.$bankLabel;
        }

        return $summary;
    }
}
