<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Data\Route;
use App\Models\Finance\OrderPayment;
use App\Services\Bank\UserBankService;
use App\Services\Finance\DirectPaymentService;
use App\Services\Master\MenuService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mpdf\Mpdf;
use Throwable;
use Yajra\DataTables\DataTables;

/**
 * Controller menu Pembayaran Langsung (customer non-DO).
 *
 * - Order Belum Lunas : order standalone belum lunas + nota pending/partial.
 *                        Di sini nota multi-DO digenerate dan dibayar
 *                        (DP/cicilan/lunas), termasuk pembayaran tunggal.
 * - Order Lunas       : order standalone lunas + nota yang sudah lunas.
 * - Daftar Pembayaran : riwayat seluruh transaksi pembayaran.
 * - Operasi           : generate nota, bayar nota (batch), bayar tunggal,
 *                        batal nota, batal pembayaran, cetak PDF, detail.
 */
class DirectPaymentController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $userBankSvc;

    public function __construct(DirectPaymentService $directPaymentSvc, MenuService $menuSvc, UserBankService $userBankSvc)
    {
        $this->service = $directPaymentSvc;
        $this->menuSvc = $menuSvc;
        $this->userBankSvc = $userBankSvc;
        $this->view = 'direct-payment.';
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
     * Format persentase pajak: 11.00 -> "11", 2.50 -> "2.5".
     */
    private function formatPercent($percent): string
    {
        $formatted = number_format((float) $percent, 2, '.', '');

        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    /**
     * Halaman Order Belum Lunas — generate nota, bayar tunggal & bayar nota.
     */
    public function indexUnpaid()
    {
        $userBank = $this->userBankSvc->findAll();
        $stats = $this->service->statsUnpaid();

        return view($this->view . 'order.unpaid')
            ->with('view', $this->view)
            ->with('userBank', $userBank)
            ->with('stats', $stats)
            ->with('title', $this->pageTitle('DIRECT_PAYMENT_UNPAID', 'Order Belum Lunas'));
    }

    /**
     * Halaman Order Lunas (baca + cetak + batal pembayaran).
     */
    public function indexPaid()
    {
        $userBank = $this->userBankSvc->findAll();
        $stats = $this->service->statsPaid();

        return view($this->view . 'order.paid')
            ->with('view', $this->view)
            ->with('userBank', $userBank)
            ->with('stats', $stats)
            ->with('title', $this->pageTitle('DIRECT_PAYMENT_PAID', 'Order Lunas'));
    }

    /**
     * Halaman Daftar Pembayaran (riwayat transaksi).
     */
    public function paymentIndex()
    {
        $stats = $this->service->statsPayments();

        return view($this->view . 'payment.index')
            ->with('view', $this->view)
            ->with('stats', $stats)
            ->with('title', $this->pageTitle('DIRECT_PAYMENT_LIST', 'Daftar Pembayaran'));
    }

    /**
     * Simpan pembayaran TUNGGAL satu order (mode legacy, tetap dipakai untuk
     * order yang belum digabung ke nota).
     */
    public function store(Request $request)
    {
        $redirect = redirect()->route('direct-payment.order.unpaid');

        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' ' . __('general.data_was_save_successfully')
                ]);
            }

            return $redirect->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Line : ' . $th->getLine() . ' - ' . $th->getMessage()
                ], 500);
            }

            return $redirect->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    public function show(string $id)
    {
        $data = $this->service->getById($id);
        $orderPayment = $this->service->orderPaymentDetail($data->code);
        $route = Route::where('code', $data->routeCode)->first();

        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('data', $data)
            ->with('orderPayment', $orderPayment)
            ->with('route', $route)
            ->with('title', $this->pageTitle('DIRECT_PAYMENT_UNPAID', 'Order Belum Lunas'));
    }

    /**
     * Simpan pembayaran NOTA (bisa banyak nota sekaligus, lunas atau DP/cicilan).
     */
    public function storeBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requestKey' => ['required', 'uuid'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.nota_number' => ['required', 'string', 'distinct'],
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

            return redirect()->route('direct-payment.order.unpaid')->with('fail', $message);
        }

        $request->merge($validator->validated());

        try {
            $result = DB::transaction(fn () => $this->service->storeBatch($request, $this->title));
            $message = $result['idempotent']
                ? 'Pembayaran sebelumnya berhasil ditemukan.'
                : $result['nota_count'] . ' nota berhasil dibayar.';
            $message .= ' Kode pembayaran: ' . $result['batch_code'] . '.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'result' => $result,
                ]);
            }

            return redirect()->route('direct-payment.order.unpaid')->with('success', $message);
        } catch (Throwable $th) {
            $result = null;

            try {
                $result = $this->service->findBatchResultByRequest($request);
            } catch (Throwable $lookupException) {
                report($lookupException);
            }

            if ($result) {
                $message = 'Pembayaran sebelumnya berhasil ditemukan. Kode pembayaran: ' . $result['batch_code'] . '.';

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'result' => $result,
                    ]);
                }

                return redirect()->route('direct-payment.order.unpaid')->with('success', $message);
            }

            if ($th instanceof DomainException) {
                $status = in_array((int) $th->getCode(), [409, 422], true) ? (int) $th->getCode() : 422;
                $message = $th->getMessage();

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], $status);
                }

                return redirect()->route('direct-payment.order.unpaid')->with('fail', $message);
            }

            report($th);
            $message = 'Pembayaran gagal diproses. Silakan coba lagi.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return redirect()->route('direct-payment.order.unpaid')->with('fail', $message);
        }
    }

    /**
     * Generate nomor nota (DP) untuk order-order terpilih milik satu customer.
     * PPN & PPh diinput sebagai persentase, Biaya Claim sebagai nominal.
     */
    public function generateNota(Request $request)
    {
        $data = $request->all();

        // Normalisasi rate pajak. Koma diterima sebagai pemisah desimal,
        // sedangkan titik dipertahankan agar rate seperti 11.5 tetap benar.
        foreach (['ppnRate', 'pphRate'] as $taxField) {
            if (isset($data[$taxField]) && is_string($data[$taxField])) {
                $clean = str_replace(' ', '', trim($data[$taxField]));
                $clean = str_replace(',', '.', $clean);

                $data[$taxField] = $clean === '' ? 0 : (float) $clean;
            }
        }

        // Normalisasi nominal Biaya Claim. Titik ribuan dihapus (mis. 500.000),
        // koma diterima sebagai pemisah desimal.
        if (isset($data['claimAmount']) && is_string($data['claimAmount'])) {
            $clean = str_replace(' ', '', trim($data['claimAmount']));
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);

            $data['claimAmount'] = $clean === '' ? 0 : (float) $clean;
        }

        $validator = Validator::make($data, [
            'orderCodes' => 'required|array|min:1',
            'orderCodes.*' => 'required|string',
            'userBankCode' => 'required|string|exists:user_bank,code',
            'ppnRate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pphRate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'claimAmount' => ['nullable', 'numeric', 'min:0'],
            'claimDescription' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->all()[0],
                ], 422);
            }

            return redirect()->route('direct-payment.order.unpaid')
                ->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $notaNumber = $this->service->assignNota(
                $data['orderCodes'],
                $data['userBankCode'],
                $this->title,
                (float) ($data['ppnRate'] ?? 0),
                (float) ($data['pphRate'] ?? 0),
                (float) ($data['claimAmount'] ?? 0),
                $data['claimDescription'] ?? null
            );

            DB::commit();

            $ppnRate = (float) ($data['ppnRate'] ?? 0);
            $pphRate = (float) ($data['pphRate'] ?? 0);
            $claimAmount = (int) round((float) ($data['claimAmount'] ?? 0));
            $ppnInfo = $ppnRate > 0 || $pphRate > 0
                ? ' (PPN: ' . rtrim(rtrim(number_format($ppnRate, 4, ',', '.'), '0'), ',') . '%, PPh: ' . rtrim(rtrim(number_format($pphRate, 4, ',', '.'), '0'), ',') . '%)'
                : '';
            $claimInfo = $claimAmount > 0
                ? ' (Biaya Claim: Rp ' . number_format($claimAmount, 0, ',', '.') . ')'
                : '';

            $message = 'Nota pembayaran berhasil di-generate dengan nomor: ' . $notaNumber . $ppnInfo . $claimInfo;

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'nota_number' => $notaNumber,
                ]);
            }

            return redirect()->route('direct-payment.order.unpaid')
                ->with('success', $message);
        } catch (DomainException $exception) {
            DB::rollback();
            $message = $exception->getMessage();
            $status = in_array((int) $exception->getCode(), [409, 422], true)
                ? (int) $exception->getCode()
                : 422;

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], $status);
            }

            return redirect()->route('direct-payment.order.unpaid')->with('fail', $message);
        } catch (Throwable $th) {
            DB::rollback();
            report($th);
            $message = 'Generate nota gagal diproses. Silakan coba lagi.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return redirect()->route('direct-payment.order.unpaid')->with('fail', $message);
        }
    }

    /**
     * Membatalkan nomor nota (jika belum ada pembayaran sama sekali).
     */
    public function cancelNota($orderCode)
    {
        try {
            DB::transaction(fn () => $this->service->cancelNota($orderCode, $this->title));

            return redirect()->route('direct-payment.order.unpaid')
                ->with('success', 'Nota pembayaran berhasil dibatalkan.');
        } catch (DomainException $exception) {
            return redirect()->route('direct-payment.order.unpaid')
                ->with('fail', $exception->getMessage());
        } catch (Throwable $th) {
            report($th);

            return redirect()->route('direct-payment.order.unpaid')
                ->with('fail', 'Pembatalan nota gagal diproses. Silakan coba lagi.');
        }
    }

    /**
     * Membatalkan batch pembayaran nota (mengembalikan saldo, reset status).
     */
    public function cancelPayment(Request $request, $orderCode)
    {
        $request->validate([
            'expected_batch_code' => ['required', 'string', 'max:30'],
        ]);

        try {
            $result = DB::transaction(fn () => $this->service->cancelPayment(
                $orderCode,
                (string) $request->input('expected_batch_code'),
                $this->title
            ));
            $message = 'Batch pembayaran ' . $result['batch_code'] . ' berhasil dibatalkan. '
                . 'Dana Rp ' . number_format($result['payment_amount'], 0, ',', '.')
                . ' telah dikembalikan untuk ' . $result['nota_count'] . ' nota ('
                . $result['order_count'] . ' order).';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'result' => $result,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (DomainException $exception) {
            $status = in_array((int) $exception->getCode(), [409, 422], true)
                ? (int) $exception->getCode()
                : 422;

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], $status);
            }

            return redirect()->back()->with('fail', $exception->getMessage());
        } catch (Throwable $th) {
            report($th);
            $message = 'Pembatalan pembayaran gagal diproses. Silakan coba lagi.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return redirect()->back()->with('fail', $message);
        }
    }

    /**
     * Datatable unit belum lunas: order standalone + nota (pending/partial).
     * Filter status: all / unpaid / partial / nota.
     */
    public function datatableUnpaid(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findUnpaidUnits();

            $statusFilter = $request->input('status');
            if ($statusFilter && in_array($statusFilter, ['unpaid', 'partial', 'nota'])) {
                $data = $data->filter(function ($row) use ($statusFilter) {
                    if ($statusFilter === 'nota') {
                        return $row->unit_type === 'nota';
                    }

                    return $row->payment_status === $statusFilter;
                })->values();
            }

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
                            data_get($row, 'search_text'),
                            data_get($row, 'code'),
                            data_get($row, 'customer_name'),
                            data_get($row, 'plate'),
                            data_get($row, 'driver'),
                            data_get($row, 'shipment'),
                            data_get($row, 'rute'),
                            data_get($row, 'status_label'),
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
                    if ($row->unit_type === 'nota') {
                        $orderCodes = $row->order_codes->implode(',');
                        $ariaLabel = 'Pilih nota ' . $row->nota_number . ' customer ' . $row->customer_name;

                        return '<div class="form-check d-flex justify-content-center"><input type="checkbox" class="form-check-input row-payment-checkbox" data-order-codes="' . e($orderCodes) . '" data-nota-number="' . e($row->nota_number) . '" data-customer-code="' . e($row->customer_code) . '" data-customer-name="' . e($row->customer_name) . '" data-billing-amount="' . $row->grand_total . '" data-paid-amount="' . $row->payment . '" data-remaining-amount="' . $row->remaining . '" data-checkbox-type="payment" data-nota-date="' . e($row->nota_date ?? '') . '" data-order-count="' . e($row->order_count) . '" data-payment-status="' . e($row->payment_status) . '" aria-label="' . e($ariaLabel) . '"></div>';
                    }

                    // Order standalone: checkbox hanya untuk generate nota.
                    // Order partial (sudah ada DP) tidak boleh digabung nota.
                    $disabled = $row->payment_status === 'partial' ? ' disabled' : '';
                    $subtotalAmount = (float) $row->cost + (float) $row->additional_cost;

                    return '<div class="form-check d-flex justify-content-center"><input type="checkbox" class="form-check-input row-payment-checkbox"' . $disabled . ' data-order-code="' . e($row->code) . '" data-customer-code="' . e($row->customer_code) . '" data-customer-name="' . e($row->customer_name) . '" data-billing-amount="' . $row->grand_total . '" data-subtotal-amount="' . $subtotalAmount . '" data-paid-amount="' . $row->payment . '" data-remaining-amount="' . $row->remaining . '" data-checkbox-type="nota" data-nota-number=""></div>';
                })
                ->addColumn('action', function ($row) {
                    if ($row->unit_type === 'nota') {
                        $firstOrderCode = $row->order_codes->first();

                        $buttons = [];
                        $buttons[] = '<a href="' . route('direct-payment.pdf-nota', $firstOrderCode) . '" target="_blank" rel="noopener" class="btn btn-sm btn-icon bg-primary-subtle text-primary hover-scale me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Cetak Nota"><i class="mdi mdi-printer fs-15"></i></a>';
                        $buttons[] = '<button type="button" class="btn btn-sm btn-icon bg-info-subtle text-info hover-scale me-1 js-dp-nota-detail" data-order-code="' . e($firstOrderCode) . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Rincian Nota"><i class="mdi mdi-eye-outline fs-15"></i></button>';

                        if ($row->payment > 0 || $row->payment_status !== 'pending') {
                            if ($row->latest_batch_code) {
                                $buttons[] = '<button type="button" class="btn btn-sm btn-icon bg-danger-subtle text-danger hover-scale js-dp-payment-cancel" data-order-code="' . e($firstOrderCode) . '" data-batch-code="' . e($row->latest_batch_code) . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Batal Pembayaran"><i class="mdi mdi-close-circle-outline fs-15"></i></button>';
                            }
                        } else {
                            $buttons[] = '<button type="button" class="btn btn-sm btn-icon bg-warning-subtle text-warning-emphasis hover-scale js-dp-nota-cancel" data-order-code="' . e($firstOrderCode) . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Batal Nota"><i class="mdi mdi-file-remove-outline fs-15"></i></button>';
                        }

                        return '<div class="d-inline-flex align-items-center justify-content-center">' . implode('', $buttons) . '</div>';
                    }

                    $buttons = [];
                    $buttons[] = '<button type="button" onclick="showDetailModal(\'' . e($row->code) . '\')" class="btn btn-sm btn-icon bg-primary-subtle text-primary hover-scale" data-bs-toggle="tooltip" data-bs-placement="top" title="Rincian Pembayaran"><i class="mdi mdi-eye-outline fs-15"></i></button>';

                    return '<div class="d-inline-flex align-items-center justify-content-center">' . implode('', $buttons) . '</div>';
                })
                ->editColumn('code', function ($row) {
                    if ($row->unit_type === 'nota') {
                        return '<div class="d-flex flex-column gap-1">'
                            . '<span class="badge rounded-pill text-bg-primary font-monospace fs-11">' . e($row->nota_number) . '</span>'
                            . '<span class="badge rounded-pill text-bg-secondary fs-11" style="width: fit-content;">' . $row->order_count . ' DO</span>'
                            . '</div>';
                    }

                    return '<span class="fw-semibold text-primary font-monospace fs-12 d-inline-flex align-items-center gap-1">
                                <i class="mdi mdi-file-document-outline fs-14"></i>' . e($row->code) . '
                            </span>';
                })
                ->editColumn('date', function ($row) {
                    if (! $row->date) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="text-nowrap text-secondary fs-12 font-monospace">' . Carbon::parse($row->date)->format('d/m/Y') . '</span>';
                })
                ->editColumn('customer_name', function ($row) {
                    $customer = $row->customer_name ?: '-';

                    return '<div class="fw-semibold text-dark fs-12 text-nowrap">
                                <span class="cell-ellipsis" style="max-width: 140px;" title="' . e($customer) . '">' . e($customer) . '</span>
                            </div>';
                })
                ->addColumn('plate', function ($row) {
                    if ($row->unit_type === 'nota') {
                        $plates = collect(explode(', ', (string) $row->plate))->filter();
                        if ($plates->isEmpty()) {
                            return '<span class="text-muted">-</span>';
                        }
                        $shown = $plates->take(2)->implode(', ');
                        $more = $plates->count() > 2 ? ' +' . ($plates->count() - 2) : '';

                        return '<span class="text-nowrap fs-12" title="' . e($plates->implode(', ')) . '"><i class="mdi mdi-truck-outline text-primary me-1"></i>' . e($shown) . $more . '</span>';
                    }

                    if (! $row->plate) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="badge bg-light text-dark border px-2 py-1 font-monospace fw-semibold fs-11 text-nowrap">
                                <i class="mdi mdi-truck-outline text-primary me-1"></i>' . e($row->plate) . '
                            </span>';
                })
                ->addColumn('driver', function ($row) {
                    if (! $row->driver) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="cell-ellipsis text-dark fs-12 fw-medium text-nowrap" style="max-width: 120px;" title="' . e($row->driver) . '">
                                <i class="mdi mdi-account-circle-outline text-muted fs-13 me-1"></i>' . e($row->driver) . '
                            </span>';
                })
                ->addColumn('shipment', function ($row) {
                    if (! $row->shipment) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="badge bg-light text-secondary border px-2 py-1 font-monospace fs-11 text-nowrap">
                                <span class="cell-ellipsis" style="max-width: 120px;" title="' . e($row->shipment) . '">' . e($row->shipment) . '</span>
                            </span>';
                })
                ->addColumn('rute', function ($row) {
                    if (! $row->rute) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="d-inline-flex align-items-center gap-1 text-nowrap fs-12">
                                <span class="cell-ellipsis text-secondary" style="max-width: 95px;">' . e($row->rute) . '</span>
                            </div>';
                })
                ->addColumn('cost', function ($row) {
                    return '<span class="font-monospace text-dark fs-12">' . number_format((float) $row->cost, 0, ',', '.') . '</span>';
                })
                ->addColumn('additional_cost', function ($row) {
                    if ((float) $row->additional_cost > 0) {
                        return '<span class="font-monospace text-warning-emphasis fw-semibold fs-12">+' . number_format((float) $row->additional_cost, 0, ',', '.') . '</span>';
                    }

                    return '<span class="text-muted font-monospace fs-12">-</span>';
                })
                ->addColumn('ppn', function ($row) {
                    if ((float) $row->ppn > 0) {
                        $label = '+' . number_format((float) $row->ppn, 0, ',', '.');
                        if ($row->ppn_percent !== null && (float) $row->ppn_percent > 0) {
                            $label .= ' (' . $this->formatPercent($row->ppn_percent) . '%)';
                        }

                        return '<span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fs-11">' . $label . '</span>';
                    }

                    return '<span class="text-muted font-monospace fs-12">-</span>';
                })
                ->addColumn('pph', function ($row) {
                    if ((float) $row->pph > 0) {
                        $label = '-' . number_format((float) $row->pph, 0, ',', '.');
                        if ($row->pph_percent !== null && (float) $row->pph_percent > 0) {
                            $label .= ' (' . $this->formatPercent($row->pph_percent) . '%)';
                        }

                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace fs-11">' . $label . '</span>';
                    }

                    return '<span class="text-muted font-monospace fs-12">-</span>';
                })
                ->addColumn('claim', function ($row) {
                    if ((float) $row->claim > 0) {
                        return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace fs-11">-' . number_format((float) $row->claim, 0, ',', '.') . '</span>';
                    }

                    return '<span class="text-muted font-monospace fs-12">-</span>';
                })
                ->addColumn('grand_total', function ($row) {
                    return '<span class="fw-bold text-dark font-monospace fs-12">' . number_format((float) $row->grand_total, 0, ',', '.') . '</span>';
                })
                ->addColumn('paymentAmount', function ($row) {
                    if ((float) $row->payment > 0) {
                        return '<span class="fw-semibold text-success font-monospace fs-12">' . number_format((float) $row->payment, 0, ',', '.') . '</span>';
                    }

                    return '<span class="text-muted font-monospace fs-12">0</span>';
                })
                ->addColumn('total', function ($row) {
                    if ((float) $row->remaining > 0) {
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace fw-bold fs-11">Rp ' . number_format((float) $row->remaining, 0, ',', '.') . '</span>';
                    }

                    return '<span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fs-11"><i class="mdi mdi-check me-1"></i>Lunas</span>';
                })
                ->addColumn('paymentStatus', function ($row) {
                    if ($row->payment_status === 'pending') {
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-close-circle-outline me-1"></i>Belum Bayar</span>';
                    }
                    if ($row->payment_status === 'paid') {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-check-circle-outline me-1"></i>Lunas</span>';
                    }

                    return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-clock-outline me-1"></i>Belum Lunas</span>';
                })
                ->rawColumns([
                    'select', 'action', 'code', 'date', 'customer_name', 'plate', 'driver',
                    'shipment', 'rute', 'cost', 'additional_cost', 'ppn', 'pph', 'claim',
                    'grand_total', 'paymentAmount', 'total', 'paymentStatus',
                ])
                ->toJson();
        }
    }

    /**
     * Datatable unit lunas: order standalone paid/overpaid + nota paid.
     */
    public function datatablePaid(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findPaidUnits();

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
                        $haystack = $normalize(data_get($row, 'search_text'));

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
                ->addColumn('action', function ($row) {
                    if ($row->unit_type === 'nota') {
                        $firstOrderCode = $row->order_codes->first();

                        $buttons = [];
                        $buttons[] = '<a href="' . route('direct-payment.pdf-nota', $firstOrderCode) . '" target="_blank" rel="noopener" class="btn btn-sm btn-icon bg-primary-subtle text-primary hover-scale me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Cetak Nota"><i class="mdi mdi-printer fs-15"></i></a>';
                        $buttons[] = '<button type="button" class="btn btn-sm btn-icon bg-info-subtle text-info hover-scale me-1 js-dp-nota-detail" data-order-code="' . e($firstOrderCode) . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Rincian Nota"><i class="mdi mdi-eye-outline fs-15"></i></button>';

                        if ($row->latest_batch_code) {
                            $buttons[] = '<button type="button" class="btn btn-sm btn-icon bg-danger-subtle text-danger hover-scale js-dp-payment-cancel" data-order-code="' . e($firstOrderCode) . '" data-batch-code="' . e($row->latest_batch_code) . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Batal Pembayaran"><i class="mdi mdi-close-circle-outline fs-15"></i></button>';
                        }

                        return '<div class="d-inline-flex align-items-center justify-content-center">' . implode('', $buttons) . '</div>';
                    }

                    return '<div class="d-inline-flex align-items-center justify-content-center"><button type="button" onclick="showDetailModal(\'' . e($row->code) . '\')" class="btn btn-sm btn-icon bg-primary-subtle text-primary hover-scale" data-bs-toggle="tooltip" data-bs-placement="top" title="Rincian Pembayaran"><i class="mdi mdi-eye-outline fs-15"></i></button></div>';
                })
                ->editColumn('code', function ($row) {
                    if ($row->unit_type === 'nota') {
                        return '<div class="d-flex flex-column gap-1">'
                            . '<span class="badge rounded-pill text-bg-primary font-monospace fs-11">' . e($row->nota_number) . '</span>'
                            . '<span class="badge rounded-pill text-bg-secondary fs-11" style="width: fit-content;">' . $row->order_count . ' DO</span>'
                            . '</div>';
                    }

                    return '<span class="fw-semibold text-primary font-monospace fs-12 d-inline-flex align-items-center gap-1">
                                <i class="mdi mdi-file-document-outline fs-14"></i>' . e($row->code) . '
                            </span>';
                })
                ->editColumn('date', function ($row) {
                    if (! $row->date) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="text-nowrap text-secondary fs-12 font-monospace">' . Carbon::parse($row->date)->format('d/m/Y') . '</span>';
                })
                ->editColumn('customer_name', function ($row) {
                    $customer = $row->customer_name ?: '-';

                    return '<div class="fw-semibold text-dark fs-12 text-nowrap">
                                <span class="cell-ellipsis" style="max-width: 140px;" title="' . e($customer) . '">' . e($customer) . '</span>
                            </div>';
                })
                ->addColumn('plate', function ($row) {
                    if ($row->unit_type === 'nota') {
                        $plates = collect(explode(', ', (string) $row->plate))->filter();
                        if ($plates->isEmpty()) {
                            return '<span class="text-muted">-</span>';
                        }
                        $shown = $plates->take(2)->implode(', ');
                        $more = $plates->count() > 2 ? ' +' . ($plates->count() - 2) : '';

                        return '<span class="text-nowrap fs-12" title="' . e($plates->implode(', ')) . '"><i class="mdi mdi-truck-outline text-primary me-1"></i>' . e($shown) . $more . '</span>';
                    }

                    if (! $row->plate) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="badge bg-light text-dark border px-2 py-1 font-monospace fw-semibold fs-11 text-nowrap">
                                <i class="mdi mdi-truck-outline text-primary me-1"></i>' . e($row->plate) . '
                            </span>';
                })
                ->addColumn('grand_total', function ($row) {
                    return '<span class="fw-bold text-dark font-monospace fs-12">' . number_format((float) $row->grand_total, 0, ',', '.') . '</span>';
                })
                ->addColumn('paymentAmount', function ($row) {
                    return '<span class="fw-semibold text-success font-monospace fs-12">' . number_format((float) $row->payment, 0, ',', '.') . '</span>';
                })
                ->addColumn('paymentStatus', function ($row) {
                    if ($row->status_code === 'overpaid') {
                        return '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-alert-circle-outline me-1"></i>Kelebihan</span>';
                    }

                    return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-check-circle-outline me-1"></i>Lunas</span>';
                })
                ->rawColumns(['action', 'code', 'date', 'customer_name', 'plate', 'grand_total', 'paymentAmount', 'paymentStatus'])
                ->toJson();
        }
    }

    /**
     * Datatable Daftar Pembayaran: 1 baris = 1 transaksi (batch / legacy).
     */
    public function paymentDatatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findPayments();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('payment_date', function ($row) {
                    if (! $row->payment_date) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="fw-medium text-dark fs-12 text-nowrap">' . Carbon::parse($row->payment_date)->format('d M Y') . '</span>';
                })
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
                        ? $label . '<div class="text-muted fs-11">Transaksi tunggal (arsip)</div>'
                        : $label;
                })
                ->addColumn('nota_orders', function ($row) {
                    if ($row->notas->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }

                    $badges = $row->notas->map(function ($nota) {
                        return '<span class="badge rounded-pill border border-primary-subtle bg-primary-subtle text-primary font-monospace fw-semibold fs-11 px-2 py-1 text-start">' . e($nota->number) . '</span>';
                    })->implode('');

                    $searchableText = $row->notas->flatMap(function ($nota) {
                        return collect([$nota->number])->merge($nota->orders->pluck('code'));
                    })->filter()->implode(' ');

                    return '<div class="d-flex flex-column align-items-start gap-1">'
                        . '<div class="d-flex flex-wrap gap-1">' . $badges . '</div>'
                        . '<span class="text-muted fs-11">' . $row->order_count . ' order</span>'
                        . '</div>'
                        . '<span class="d-none">' . e($searchableText) . '</span>';
                })
                ->addColumn('customer', function ($row) {
                    if ($row->customers->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="text-start">' . $row->customers->map(function ($customer) {
                        return '<span class="fw-semibold text-dark fs-12 d-block text-truncate" style="max-width: 180px;" title="' . e($customer) . '">' . e($customer) . '</span>';
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
                ->rawColumns(['action', 'payment_date', 'batch_code', 'nota_orders', 'customer', 'amount', 'bank', 'description'])
                ->toJson();
        }
    }

    /** Rincian transaksi pembayaran: nota lalu order. */
    public function paymentDetail(string $transactionKey)
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
                    'customer_name' => $order->customer_name,
                    'amount' => $order->amount,
                ])->values(),
            ])->values(),
        ]);
    }

    /** Rincian satu nota (untuk modal). */
    public function notaDetail($orderCode)
    {
        $detail = $this->service->notaDetail($orderCode);

        if (! $detail) {
            return response()->json(['message' => 'Nota tidak ditemukan.'], 404);
        }

        return response()->json($detail);
    }

    public function orderDetailPayment($orderCode)
    {
        return $this->service->orderPaymentDetail($orderCode);
    }

    /**
     * Cetak PDF gabungan order terpilih (bukan bagian alur nota).
     */
    public function pdfMulti(Request $request)
    {
        $orderCodes = $request->input('orderCodes', []);

        if (is_string($orderCodes)) {
            $orderCodes = array_filter(array_map('trim', explode(',', $orderCodes)));
        }

        if (empty($orderCodes)) {
            return redirect()->route('direct-payment.order.unpaid')->with('fail', 'Tidak ada order yang dipilih');
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
            return redirect()->route('direct-payment.order.unpaid')->with('fail', 'Data order tidak ditemukan');
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

        $totalCost = 0;
        $totalAdditionalCost = 0;
        $totalPpnAmount = 0;
        $totalPphAmount = 0;
        $totalGrandTotal = 0;

        foreach ($orders as $order) {
            $routeAmount = (float) ($order->routeAmount ?? 0);
            $additionalCost = $order->cost ? $order->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal') : 0;
            $totalBefore = $routeAmount + $additionalCost;

            $ppn = $order->customer->ppn ?? 0;
            $ppnAmount = ($totalBefore * $ppn) / 100;

            $pph = $order->customer->pph ?? 0;
            $pphAmount = ($totalBefore * $pph) / 100;

            $claim = isset($order->orderPayment->claim) ? (float) $order->orderPayment->claim : 0;

            $grandTotal = $totalBefore + $ppnAmount - $pphAmount - $claim;

            $totalCost += $routeAmount;
            $totalAdditionalCost += $additionalCost;
            $totalPpnAmount += $ppnAmount;
            $totalPphAmount += $pphAmount;
            $totalGrandTotal += $grandTotal;
        }

        $company = CompanySetting::first();
        $customerFirst = $orders->first()->customer;

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $mpdf->WriteHTML(
            view($pdfTemplate . '-multi')
                ->with('orders', $orders)
                ->with('customer', $customerFirst)
                ->with('company', $company)
                ->with('totalSubtotal', $totalCost)
                ->with('totalAdditionalCost', $totalAdditionalCost)
                ->with('totalPpnAmount', $totalPpnAmount)
                ->with('totalPphAmount', $totalPphAmount)
                ->with('totalGrandTotal', $totalGrandTotal)
                ->with('isOrderPaymentPdf', true)
        );

        return $mpdf->Output('Nota-Pembayaran-Multi-' . now()->format('YmdHis') . '.pdf', 'I');
    }

    /**
     * Cetak PDF satu nota utuh (seluruh order di dalam nomor nota yang sama).
     * Dipakai tombol cetak per baris nota, sehingga 1 file PDF = 1 nota.
     */
    public function pdfNota($orderCode)
    {
        $orderPayment = OrderPayment::where('orderCode', $orderCode)->first();

        if (! $orderPayment || ! $orderPayment->nota_number) {
            return redirect()->route('direct-payment.order.unpaid')->with('fail', 'Nomor nota belum di-generate untuk order ini. Silakan generate nota terlebih dahulu.');
        }

        $orderCodes = OrderPayment::where('nota_number', $orderPayment->nota_number)
            ->pluck('orderCode')
            ->toArray();

        try {
            $document = $this->buildNotaPdf($orderCodes);
        } catch (DomainException $exception) {
            return redirect()->route('direct-payment.order.unpaid')->with('fail', $exception->getMessage());
        }

        return response($document['content'])
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Nota-Pembayaran-' . str_replace('/', '-', $orderPayment->nota_number) . '.pdf"');
    }

    /**
     * Susun konten PDF nota pembayaran dari daftar order.
     * Seluruh order yang berada pada nomor nota yang sama ikut disertakan.
     *
     * Berbeda dari vendor (pajak dihitung dari data order), nilai tagihan di
     * sini diambil dari kolom order_payment (hasil distribusi saat generate
     * nota) agar PDF selalu konsisten dengan yang dibayarkan.
     *
     * @throws \DomainException bila order belum punya nota / tidak ditemukan
     *
     * @return array{content: string, notaNumber: string|null}
     */
    private function buildNotaPdf(array $orderCodes)
    {
        $orderCodes = array_values(array_unique(array_filter($orderCodes)));

        if (empty($orderCodes)) {
            throw new DomainException('Tidak ada order yang dipilih', 422);
        }

        $selectedNotaNumbers = OrderPayment::whereIn('orderCode', $orderCodes)
            ->whereNotNull('nota_number')
            ->pluck('nota_number')
            ->unique();

        if ($selectedNotaNumbers->isNotEmpty()) {
            $allOrderCodesWithSameNotas = OrderPayment::whereIn('nota_number', $selectedNotaNumbers)
                ->pluck('orderCode')
                ->toArray();

            $orderCodes = array_values(array_unique(array_merge($orderCodes, $allOrderCodesWithSameNotas)));
        }

        $orderPayments = OrderPayment::whereIn('orderCode', $orderCodes)->get();
        if ($orderPayments->count() < count($orderCodes) || $orderPayments->contains(fn ($op) => ! $op->nota_number)) {
            throw new DomainException('Beberapa order terpilih belum memiliki nomor nota. Silakan generate nota terlebih dahulu.', 422);
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
            throw new DomainException('Data order tidak ditemukan', 422);
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

        $orderPayments = OrderPayment::with(['paymentHistory.userBank.bank'])
            ->whereIn('orderCode', $orderCodes)
            ->get();

        $totalSubtotal = 0;
        $totalAdditionalCost = 0;
        $totalPphAmount = 0;
        $totalPpnAmount = 0;
        $totalPpnRate = 0;
        $totalPphRate = 0;
        $totalClaim = 0;
        $totalGrandTotal = 0;

        foreach ($orders as $order) {
            $orderPayment = $orderPayments->firstWhere('orderCode', $order->code);
            $subtotal = (float) ($orderPayment->cost ?? 0);
            if ($subtotal <= 0) {
                $subtotal = (float) ($order->routeAmount ?? 0);
            }
            $additionalCost = (float) ($orderPayment->additional_cost ?? 0);
            if ($additionalCost <= 0) {
                $additionalCost = $order->cost
                    ? $order->cost
                        ->filter(fn ($cost) => strtolower(trim((string) ($cost->type ?? ''))) === 'on charge')
                        ->sum('nominal')
                    : 0;
            }

            $totalSubtotal += $subtotal;
            $totalAdditionalCost += $additionalCost;
            $totalPpnAmount += (float) ($orderPayment->ppn ?? 0);
            $totalPphAmount += (float) ($orderPayment->pph ?? 0);
            $totalClaim += (float) ($orderPayment->claim ?? 0);
            $totalGrandTotal += $subtotal + $additionalCost + (float) ($orderPayment->ppn ?? 0) - (float) ($orderPayment->pph ?? 0) - (float) ($orderPayment->claim ?? 0);
        }

        $totalPpnRate = (float) ($orderPayments->max('ppn_percent') ?? 0);
        $totalPphRate = (float) ($orderPayments->max('pph_percent') ?? 0);

        $company = CompanySetting::first();
        $customerFirst = $orders->first()->customer;

        $orderPayment = OrderPayment::whereIn('orderCode', $orderCodes)
            ->whereNotNull('nota_number')
            ->first();

        $notaNumber = $orderPayment ? $orderPayment->nota_number : null;
        $userBankCode = $orderPayment ? $orderPayment->user_bank_code : null;

        $userBank = null;
        if ($userBankCode) {
            $userBank = \App\Models\Bank\UserBank::with('bank')->where('code', $userBankCode)->first();
        }

        $allHistories = collect();
        foreach ($orderPayments as $op) {
            if ($op->paymentHistory) {
                foreach ($op->paymentHistory as $ph) {
                    $allHistories->push($ph);
                }
            }
        }

        $groupedHistories = $allHistories->groupBy(function ($history) {
            return $history->date . '_' . $history->description . '_' . $history->userBankCode;
        })->map(function ($group) {
            $first = $group->first();

            return (object) [
                'payment_date' => $first->date,
                'description' => $first->description,
                'amount' => $group->sum('total'),
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
                ->with('totalPpnRate', $totalPpnRate)
                ->with('totalPphRate', $totalPphRate)
                ->with('totalClaim', $totalClaim)
                ->with('totalGrandTotal', $totalGrandTotal)
                ->with('notaNumber', $notaNumber)
                ->with('userBank', $userBank)
                ->with('paymentHistories', $groupedHistories)
                ->with('paymentHistoryTotal', $paymentHistoryTotal)
                ->with('isOrderPaymentPdf', true)
        );

        return [
            'content' => $mpdf->Output('', 'S'),
            'notaNumber' => $notaNumber,
        ];
    }
}
