<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\VendorPaymentService;
use App\Services\Master\MenuService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

/**
 * Controller menu Vendor → Daftar Pembayaran.
 *
 * Menampilkan seluruh transaksi pembayaran ke vendor eksternal.
 * 1 baris = 1 pembayaran (DP / cicilan / pelunasan).
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
            ? (Auth::user()->languange == 'id' ? $menu->nama : $menu->name)
            : 'Daftar Pembayaran';

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $title)
            ->with('stats', $stats);
    }

    /**
     * Datatable daftar pembayaran vendor (per transaksi pembayaran).
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findPayments();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('DT_RowIndex', function ($row) {
                    return '<span class="text-muted fw-semibold fs-12">' . ($row->DT_RowIndex ?? '') . '</span>';
                })
                ->editColumn('payment_date', function ($row) {
                    if (! $row->payment_date) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="fw-medium text-dark fs-12 text-nowrap">' . Carbon::parse($row->payment_date)->format('d M Y') . '</span>';
                })
                ->addColumn('batch_code', function ($row) {
                    $code = $row->batch_code ?: ($row->vendorPayment->code ?? null);

                    return $code
                        ? '<span class="font-monospace fw-bold text-primary fs-12 text-nowrap">' . e($code) . '</span>'
                        : '<span class="text-muted">-</span>';
                })
                ->addColumn('nota_number', function ($row) {
                    $nota = $row->vendorPayment->nota_number ?? null;

                    return $nota
                        ? '<span class="badge rounded-pill text-bg-primary">' . e($nota) . '</span>'
                        : '<span class="text-muted">-</span>';
                })
                ->addColumn('order', function ($row) {
                    $order = $row->vendorPayment->order ?? null;

                    if (! $order) {
                        return '<span class="text-muted">-</span>';
                    }

                    $html = '<div class="text-start"><span class="font-monospace fw-semibold text-dark fs-12 text-nowrap">' . e($order->code) . '</span>';

                    if ($order->shipmentNumber) {
                        $html .= '<div class="text-muted fs-11 text-nowrap"><i class="mdi mdi-truck-fast-outline me-1"></i>' . e($order->shipmentNumber) . '</div>';
                    }

                    return $html . '</div>';
                })
                ->addColumn('vendor', function ($row) {
                    $vendor = $row->vendorPayment->order?->fleet?->company?->name;
                    $plate = $row->vendorPayment->order?->fleet?->plateNumber;

                    if (! $vendor && ! $plate) {
                        return '<span class="text-muted">-</span>';
                    }

                    $html = '<div class="text-start">';
                    if ($vendor) {
                        $html .= '<span class="fw-semibold text-dark fs-12 d-block text-truncate" style="max-width: 180px;" title="' . e($vendor) . '">' . e($vendor) . '</span>';
                    }
                    if ($plate) {
                        $html .= '<span class="text-muted font-monospace fs-11"><i class="mdi mdi-card-text-outline me-1"></i>' . e($plate) . '</span>';
                    }

                    return $html . '</div>';
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
                ->rawColumns(['DT_RowIndex', 'payment_date', 'batch_code', 'nota_number', 'order', 'vendor', 'amount', 'bank', 'description'])
                ->toJson();
        }
    }
}
