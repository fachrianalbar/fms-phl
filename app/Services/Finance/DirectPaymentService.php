<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Bank\UserBank;
use App\Models\Finance\OrderPayment;
use App\Models\Finance\OrderPaymentBatch;
use App\Models\Finance\OrderPaymentHistory;
use App\Models\LiveMutation;
use App\Models\Mutation;
use App\Models\Operational\Order;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\DB;

/**
 * Service menu Pembayaran Langsung (customer non-DO).
 *
 * Dua mode pembayaran:
 * 1. Tunggal (legacy) : bayar satu order lewat modal form; setiap transaksi
 *    langsung menulis order_payment_history + mutasi bank.
 * 2. Nota (multi-DO)  : beberapa order milik SATU customer digabung ke satu
 *    nomor nota (prefix DP). Pembayaran dilakukan per nota (DP/cicilan/lunas),
 *    lalu dialokasikan proporsional ke tiap order (largest remainder).
 *
 * Aturan penting:
 * - order_payment.total = AKUMULASI pembayaran (bukan tagihan). Tagihan
 *   dihitung: cost + additional_cost + ppn - pph - claim.
 * - Order yang sudah masuk nota TIDAK bisa dibayar tunggal (guard di store()).
 * - Order yang sudah punya pembayaran (total > 0 / ada riwayat) TIDAK boleh
 *   digabung ke nota — harus diselesaikan sendiri.
 * - order.status TIDAK diubah oleh alur pembayaran langsung.
 */
class DirectPaymentService
{
    use LogActivity;

    protected $service;

    protected $order;

    protected $orderPaymentHistory;

    protected $mutation;

    protected $userBank;

    public function __construct(OrderPayment $service, Order $order, OrderPaymentHistory $orderPaymentHistory, Mutation $mutation, UserBank $userBank)
    {
        $this->service = $service;
        $this->order = $order;
        $this->orderPaymentHistory = $orderPaymentHistory;
        $this->mutation = $mutation;
        $this->userBank = $userBank;
    }

    /**
     * Semua order milik customer individual (legacy, tidak dipakai menu aktif).
     */
    public function findAll()
    {
        return $this->order->whereHas('customer', function ($q) {
            $q->where('type', 'Individual');
        })->with(['fleet', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'orderPayment'])->get();
    }

    /**
     * Order customer non-DO yang pembayarannya dicatat langsung per order.
     */
    public function findAllIsDoZero()
    {
        return $this->order->whereHas('customer', function ($q) {
            $q->where('isDo', 0);
        })->with(['fleet', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'orderPayment', 'cost'])->get();
    }

    public function getById(string $id)
    {
        return $this->order->where('id', $id)->with(['customer', 'orderPayment', 'orderPaymentHistory', 'cost'])->first();
    }

    /**
     * Hitung kalkulasi tagihan finansial satu order (untuk tabel & statistik).
     *
     * Cost dasar = routeAmount; biaya tambahan = additional_cost tersimpan
     * (jika order_payment sudah ada) atau jumlah cost component On Charge.
     */
    public function orderFinancials($order): array
    {
        $cost = (float) ($order->routeAmount ?? 0);
        $additionalCost = 0;
        if (isset($order->orderPayment) && isset($order->orderPayment->additional_cost)) {
            $additionalCost = (float) $order->orderPayment->additional_cost;
        } else {
            $additionalCost = (float) $order->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
        }
        $subtotal = $cost + $additionalCost;

        $ppn = 0;
        $ppnPercent = null;
        if (isset($order->orderPayment) && isset($order->orderPayment->ppn)) {
            $ppn = (float) $order->orderPayment->ppn;
            if (isset($order->orderPayment->ppn_percent) && (float) $order->orderPayment->ppn_percent > 0) {
                $ppnPercent = (float) $order->orderPayment->ppn_percent;
            }
        } else {
            if (isset($order->customer->ppn)) {
                $ppnPercent = (float) $order->customer->ppn;
                $ppn = $subtotal * ($ppnPercent / 100);
            }
        }

        $pph = 0;
        $pphPercent = null;
        if (isset($order->orderPayment) && isset($order->orderPayment->pph)) {
            $pph = (float) $order->orderPayment->pph;
            if (isset($order->orderPayment->pph_percent) && (float) $order->orderPayment->pph_percent > 0) {
                $pphPercent = (float) $order->orderPayment->pph_percent;
            }
        } else {
            if (isset($order->customer->pph)) {
                $pphPercent = (float) $order->customer->pph;
                $pph = $subtotal * ($pphPercent / 100);
            }
        }

        $claim = 0;
        $claimDescription = '';
        if (isset($order->orderPayment) && isset($order->orderPayment->claim)) {
            $claim = (float) $order->orderPayment->claim;
            $claimDescription = $order->orderPayment->claim_description ?? '';
        }

        $grandTotal = $subtotal + $ppn - $pph - $claim;
        $payment = isset($order->orderPayment) ? (float) $order->orderPayment->total : 0;
        $remaining = max(0, $grandTotal - $payment);

        $statusCode = 'unpaid';
        $statusLabel = 'Belum Bayar';
        if ($payment > 0) {
            if ($payment == $grandTotal) {
                $statusCode = 'paid';
                $statusLabel = 'Lunas';
            } elseif ($payment > $grandTotal) {
                $statusCode = 'overpaid';
                $statusLabel = 'Kelebihan Bayar';
            } else {
                $statusCode = 'partial';
                $statusLabel = 'Belum Lunas';
            }
        }

        return [
            'cost' => $cost,
            'additionalCost' => $additionalCost,
            'subtotal' => $subtotal,
            'claim' => $claim,
            'claimDescription' => $claimDescription,
            'ppn' => $ppn,
            'ppnPercent' => $ppnPercent,
            'pph' => $pph,
            'pphPercent' => $pphPercent,
            'grandTotal' => $grandTotal,
            'payment' => $payment,
            'remaining' => $remaining,
            'statusCode' => $statusCode,
            'statusLabel' => $statusLabel,
        ];
    }

    /**
     * Tagihan satu baris order_payment (dari kolom tersimpan).
     */
    private function orderPaymentBilling($orderPayment): float
    {
        return (float) (
            (float) ($orderPayment->cost ?? 0)
            + (float) ($orderPayment->additional_cost ?? 0)
            + (float) ($orderPayment->ppn ?? 0)
            - (float) ($orderPayment->pph ?? 0)
            - (float) ($orderPayment->claim ?? 0)
        );
    }

    /**
     * Hitung nominal PPN/PPH dari input form.
     * Pajak dapat diisi sebagai persentase (dihitung dari subtotal)
     * atau langsung sebagai nominal.
     *
     * @return array{nominal: float, percent: float|null}
     */
    private function resolveTax(?string $type, $percentInput, $nominalInput, float $subtotal): array
    {
        if ($type === 'percent') {
            $percent = (float) ($percentInput ?? 0);

            if ($percent > 0) {
                return [
                    'nominal' => (float) round($subtotal * $percent / 100),
                    'percent' => $percent,
                ];
            }

            return ['nominal' => 0, 'percent' => null];
        }

        return [
            'nominal' => (float) ($nominalInput ?? 0),
            'percent' => null,
        ];
    }

    /**
     * Simpan pembayaran TUNGGAL satu order (mode legacy).
     * Order yang sudah tergabung dalam nota ditolak di sini.
     */
    public function store($request, $title)
    {
        $orderPayment = $this->service->where('orderCode', $request->orderCode)->first();

        // Guard: order yang sudah masuk nota hanya boleh dibayar via nota.
        if ($orderPayment && $orderPayment->nota_number) {
            throw new \InvalidArgumentException(
                'Order ini sudah tergabung dalam nota ' . $orderPayment->nota_number
                . '. Pembayaran harus dilakukan melalui nota tersebut (multi-order).'
            );
        }

        $billingCost = (float) ($request->cost ?? 0);
        $billingAdditionalCost = (float) ($request->additional_cost ?? 0);
        $billingSubtotal = $billingCost + $billingAdditionalCost;

        // PPN / PPH: persentase atau nominal
        $ppn = $this->resolveTax($request->ppn_type, $request->ppn_percent, $request->ppn, $billingSubtotal);
        $pph = $this->resolveTax($request->pph_type, $request->pph_percent, $request->pph, $billingSubtotal);

        $billingPpn = $ppn['nominal'];
        $billingPpnPercent = $ppn['percent'];
        $billingPph = $pph['nominal'];
        $billingPphPercent = $pph['percent'];

        // Biaya Claim (pengurang tagihan)
        $billingClaim = (float) ($request->claim ?? 0);
        $billingClaimDescription = $request->claim_description ?? null;

        $billingTotal = $billingSubtotal + $billingPpn - $billingPph - $billingClaim;

        // Nominal pembayaran: mendukung DP/cicilan (partial) maupun pelunasan
        $payment = (float) ($request->paymentAmount ?? 0);

        if ($payment <= 0) {
            throw new \InvalidArgumentException('Nominal pembayaran harus lebih besar dari 0.');
        }

        $alreadyPaid = $orderPayment ? (float) $orderPayment->total : 0;
        $newTotal = $alreadyPaid + $payment;

        $paymentType = $request->type;
        if (! in_array($paymentType, ['Full', 'Dp'])) {
            $paymentType = $payment >= ($billingTotal - $alreadyPaid) ? 'Full' : 'Dp';
        }

        $billingData = [
            'cost' => $billingCost,
            'additional_cost' => $billingAdditionalCost,
            'claim' => $billingClaim,
            'claim_description' => $billingClaimDescription,
            'ppn' => $billingPpn,
            'ppn_percent' => $billingPpnPercent,
            'pph' => $billingPph,
            'pph_percent' => $billingPphPercent,
            'total' => $newTotal,
            'status' => $newTotal >= $billingTotal ? 1 : 0,
        ];

        if (! $orderPayment) {
            $data = $this->service->create(array_merge([
                'code' => GenerateCode::generateUniqueCode('FOP', 'order_payment'),
                'orderCode' => $request->orderCode,
            ], $billingData));

            $this->logActivity($title, $data, 'Create');
        } else {
            $this->logActivity($title, $orderPayment, 'Before Update');

            $orderPayment->update($billingData);

            $this->logActivity($title, $orderPayment->refresh(), 'After Update');
        }

        LiveMutationHelper::updateLiveMutation($request->userBankCode, $payment, 'debit');

        // Riwayat pembayaran: setiap transaksi tercatat lengkap
        // dengan snapshot pajak dan claim yang dipakai saat pembayaran dibuat
        $orderPaymentHistory = $this->orderPaymentHistory->create([
            'code' => GenerateCode::generateUniqueCode('FOPH', 'order_payment_history'),
            'orderCode' => $request->orderCode,
            'paymentType' => $paymentType,
            'claim' => $billingClaim,
            'claim_description' => $billingClaimDescription,
            'ppn' => $billingPpn,
            'ppn_percent' => $billingPpnPercent,
            'pph' => $billingPph,
            'pph_percent' => $billingPphPercent,
            'total' => $payment,
            'date' => $request->date,
            'description' => $request->description,
            'userBankCode' => $request->userBankCode,
        ]);

        $mutation = $this->mutation->create([
            'code' => GenerateCode::generateUniqueCode('FMT', 'mutation'),
            'userBankCode' => $request->userBankCode,
            'date' => now(),
            'description' => 'Direct payment for order ' . $request->orderCode . ' with amount ' . number_format($payment, 0, ',', '.'),
            'nominal' => $payment,
            'type' => 'In',
            'transactionCode' => $orderPaymentHistory->code,
            'transactionTypeCode' => 'FTT250306114178', // Order Payment
        ]);

        $this->logActivity('Order Payment History', $orderPaymentHistory, 'Create');

        $this->logActivity('Mutation', $mutation, 'Create');
    }

    /**
     * Ringkasan tagihan order untuk form pembayaran.
     * Menyertakan persentase PPN/PPH tersimpan dan default customer
     * agar form dapat mengisi mode persentase secara otomatis.
     */
    public function orderPaymentDetail($orderCode)
    {
        $data = $this->order->where('code', $orderCode)
            ->with([
                'customer',
                'orderPayment',
                'orderPaymentHistory.userBank.bank',
                'cost.costComponent',
                'fleet',
                'driver',
                'route.originLocation',
                'route.destinationLocation',
            ])
            ->first();

        $cost = (float) ($data->routeAmount ?? 0);

        $customerPpnPercent = isset($data->customer->ppn) ? (float) $data->customer->ppn : 0;
        $customerPphPercent = isset($data->customer->pph) ? (float) $data->customer->pph : 0;

        if (isset($data->orderPayment)) {
            $additional_cost = isset($data->orderPayment->additional_cost)
                ? (float) $data->orderPayment->additional_cost
                : (float) $data->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');

            $ppn = isset($data->orderPayment->ppn)
                ? (float) $data->orderPayment->ppn
                : ($customerPpnPercent > 0 ? ($cost + $additional_cost) * ($customerPpnPercent / 100) : 0);

            $pph = isset($data->orderPayment->pph)
                ? (float) $data->orderPayment->pph
                : ($customerPphPercent > 0 ? ($cost + $additional_cost) * ($customerPphPercent / 100) : 0);

            $ppn_percent = isset($data->orderPayment->ppn_percent) ? (float) $data->orderPayment->ppn_percent : null;
            $pph_percent = isset($data->orderPayment->pph_percent) ? (float) $data->orderPayment->pph_percent : null;
        } else {
            $additional_cost = (float) $data->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
            $ppn = $customerPpnPercent > 0 ? ($cost + $additional_cost) * ($customerPpnPercent / 100) : 0;
            $pph = $customerPphPercent > 0 ? ($cost + $additional_cost) * ($customerPphPercent / 100) : 0;
            $ppn_percent = null;
            $pph_percent = null;
        }

        $claim = 0;
        $claim_description = '';
        if (isset($data->orderPayment)) {
            $claim = (float) ($data->orderPayment->claim ?? 0);
            $claim_description = $data->orderPayment->claim_description ?? '';
        }

        $payment = $data->orderPayment->total ?? 0;
        $grandTotal = $cost + $additional_cost + $ppn - $pph - $claim;

        $origin = $data->route->originLocation->name ?? '-';
        $dest = $data->route->destinationLocation->name ?? '-';

        $routeTypeCode = $data->route->routeTypeCode ?? '-';
        $routeTypeLabel = 'Kubik';
        if ($routeTypeCode === 'TONASE') {
            $routeTypeLabel = 'Tonase';
        } elseif ($routeTypeCode === 'TRIP') {
            $routeTypeLabel = 'Trip';
        }

        $onChargeCosts = ($data->cost ?? collect())->filter(fn($c) => strtolower($c->type ?? '') === 'on charge');
        $additionalCostsBreakdown = $onChargeCosts->map(fn($c) => [
            'component' => $c->costComponent->name ?? 'Biaya Tambahan',
            'description' => $c->description ?? '-',
            'nominal' => (float) ($c->nominal ?? 0),
        ])->values();

        $historyList = ($data->orderPaymentHistory ?? collect())->sortByDesc('id')->map(function ($h) {
            $bankStr = '-';
            if ($h->userBank) {
                $accNo = $h->userBank->accountNumber ?? '';
                $bName = $h->userBank->bank->name ?? '';
                $accName = $h->userBank->accountName ?? '';
                $bankStr = trim("{$accNo} - {$bName} a/n {$accName}", ' -');
            }

            return [
                'id' => $h->id,
                'date' => $h->date ? \Carbon\Carbon::parse($h->date)->format('d/m/Y') : '-',
                'type' => $h->paymentType ?? 'Full',
                'bank' => $bankStr,
                'ppn' => (float) ($h->ppn ?? 0),
                'ppn_percent' => $h->ppn_percent !== null ? (float) $h->ppn_percent : null,
                'pph' => (float) ($h->pph ?? 0),
                'pph_percent' => $h->pph_percent !== null ? (float) $h->pph_percent : null,
                'claim' => (float) ($h->claim ?? 0),
                'claim_description' => $h->claim_description ?? '',
                'description' => $h->description ?? '-',
                'total' => (float) ($h->total ?? 0),
            ];
        })->values();

        // Calculate status
        $status = 'Belum Bayar';
        $statusCode = 'unpaid';
        if ($payment > 0) {
            if ($payment == $grandTotal) {
                $status = 'Lunas';
                $statusCode = 'paid';
            } elseif ($payment > $grandTotal) {
                $status = 'Kelebihan Bayar';
                $statusCode = 'overpaid';
            } else {
                $status = 'Belum Lunas';
                $statusCode = 'partial';
            }
        }

        return [
            'order_id' => $data->id,
            'order_code' => $data->code,
            'order_date' => $data->orderDate ? \Carbon\Carbon::parse($data->orderDate)->format('d/m/Y') : '-',
            'customer_name' => $data->customer->name ?? '-',
            'plate_number' => $data->fleet->plateNumber ?? '-',
            'driver_name' => $data->driver->name ?? '-',
            'shipment_number' => $data->shipmentNumber ?? '-',
            'route_type_label' => $routeTypeLabel,
            'qty' => (float) ($data->qty ?? 0),
            'notes' => $data->notes ?? '',
            'route_origin' => $origin,
            'route_destination' => $dest,
            'status' => $status,
            'status_code' => $statusCode,
            'cost' => $cost,
            'additional_cost' => $additional_cost,
            'additional_costs' => $additionalCostsBreakdown,
            'claim' => $claim,
            'claim_description' => $claim_description,
            'ppn' => $ppn,
            'ppn_percent' => $ppn_percent,
            'pph' => $pph,
            'pph_percent' => $pph_percent,
            'customer_ppn_percent' => $customerPpnPercent,
            'customer_pph_percent' => $customerPphPercent,
            'grand_total' => $grandTotal,
            'payment' => $payment,
            'total' => $grandTotal - $payment,
            'nota_number' => isset($data->orderPayment->nota_number) ? $data->orderPayment->nota_number : null,
            'histories' => $historyList,
        ];
    }

    // =====================================================================
    // NOTA (multi-DO)
    // =====================================================================

    /**
     * Generate nomor nota berformat DP/SEQUENCE/YEAR.
     * SEQUENCE = urutan yang reset setiap tahun baru.
     */
    public function generateNotaNumber($prefix)
    {
        $prefix = strtoupper(trim((string) $prefix));
        $year = (int) now()->format('Y');
        $sequence = DB::table('order_payment_nota_sequence')
            ->where('prefix', $prefix)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            throw new \DomainException('Konfigurasi urutan nomor nota tidak ditemukan.', 422);
        }

        $nextSequence = (int) $sequence->year === $year
            ? (int) $sequence->last_sequence + 1
            : 1;

        DB::table('order_payment_nota_sequence')
            ->where('prefix', $prefix)
            ->update([
                'year' => $year,
                'last_sequence' => $nextSequence,
                'updated_at' => now(),
            ]);

        return $prefix . '/' . str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT) . '/' . $year;
    }

    /**
     * Assign nomor nota ke beberapa order sekaligus (satu customer).
     *
     * PPN & PPh diinput sebagai persentase pada level nota, sedangkan Biaya
     * Claim diinput sebagai nominal. Nominal pajak & claim dihitung dari total
     * DPP (routeAmount + biaya on-charge), lalu didistribusikan proporsional
     * ke tiap order agar seluruh alur pembayaran tetap konsisten.
     *
     * Aturan ketat: hanya order yang BELUM punya pembayaran sama sekali
     * (total = 0 dan tanpa riwayat) yang boleh digabung ke nota.
     *
     * @param  array  $orderCodes
     * @param  string  $userBankCode
     * @param  string  $title
     * @param  float|int  $ppnRate Persentase PPN (>= 0)
     * @param  float|int  $pphRate Persentase PPh (>= 0)
     * @param  float|int  $claimAmount Biaya Claim (nominal Rupiah, >= 0)
     * @param  string|null  $claimDescription Keterangan biaya claim
     * @return string Nomor nota yang dihasilkan
     *
     * @throws \DomainException
     */
    public function assignNota(array $orderCodes, $userBankCode, $title, $ppnRate = 0, $pphRate = 0, $claimAmount = 0, $claimDescription = null)
    {
        $orderCodes = array_values(array_unique(array_filter($orderCodes)));

        if (empty($orderCodes)) {
            throw new \DomainException('Pilih minimal satu order untuk digabung ke nota.', 422);
        }

        $ppnRate = max(0, (float) $ppnRate);
        $pphRate = max(0, (float) $pphRate);
        $claimAmount = max(0, (int) round((float) $claimAmount));
        $claimDescription = $claimDescription !== null && trim((string) $claimDescription) !== '' ? trim((string) $claimDescription) : null;

        $userBank = $this->userBank->where('code', $userBankCode)->first();

        if (! $userBank || (int) $userBank->type !== 2) {
            throw new \DomainException('Rekening sumber dana perusahaan tidak valid.', 422);
        }

        // Ambil semua order terpilih dengan relasi customer, company, dan cost
        $orders = $this->order->with(['customer.company', 'fleet', 'cost', 'orderPayment'])
            ->whereIn('code', $orderCodes)
            ->orderBy('code')
            ->lockForUpdate()
            ->get();
        if ($orders->count() !== count($orderCodes)) {
            throw new \DomainException('Beberapa order tidak ditemukan.', 422);
        }

        // Validasi 1: hanya customer non-DO (scope menu Pembayaran Langsung)
        $nonDoOrders = $orders->filter(function ($order) {
            return (int) ($order->customer->isDo ?? 1) !== 0;
        });
        if ($nonDoOrders->isNotEmpty()) {
            throw new \DomainException('Nota hanya dapat dibuat untuk order customer non-DO (Pembayaran Langsung).', 422);
        }

        // Validasi 2: satu nota = satu customer
        $customerCodes = $orders->map(function ($order) {
            return $order->customerCode ?? ($order->customer->code ?? null);
        })->filter()->unique();
        if ($customerCodes->count() > 1) {
            throw new \DomainException('Gagal: Order yang dipilih milik customer yang berbeda. Satu nota hanya diperbolehkan untuk customer yang sama.', 422);
        }

        // Cari order_payment yang sudah ada untuk order-order ini
        $orderPayments = $this->service
            ->whereIn('orderCode', $orderCodes)
            ->orderBy('orderCode')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        // Validasi 3: tidak boleh ada order yang sudah memiliki nota
        $alreadyNota = $orderPayments->whereNotNull('nota_number');
        if ($alreadyNota->isNotEmpty()) {
            throw new \DomainException('Order sudah memiliki nota: ' . $alreadyNota->pluck('orderCode')->implode(', '), 409);
        }

        // Validasi 4 (ketat): order yang sudah dibayar / punya riwayat tidak
        // boleh digabung ke nota — harus diselesaikan secara individual.
        $paidOrders = $orderPayments->filter(function ($orderPayment) {
            return (float) ($orderPayment->total ?? 0) > 0 || (int) ($orderPayment->status ?? 0) !== 0;
        });
        if ($paidOrders->isNotEmpty()) {
            throw new \DomainException(
                'Order sudah memiliki pembayaran dan tidak dapat digabung ke nota: '
                . $paidOrders->pluck('orderCode')->implode(', ') . '. Selesaikan pembayaran order tersebut secara individual.',
                409
            );
        }

        $hasHistory = $this->orderPaymentHistory
            ->whereIn('orderCode', $orderCodes)
            ->lockForUpdate()
            ->exists();
        if ($hasHistory) {
            throw new \DomainException('Salah satu order terpilih sudah memiliki riwayat pembayaran sehingga tidak dapat digabung ke nota.', 409);
        }

        $notaNumber = $this->generateNotaNumber('DP');

        // DPP tiap order = routeAmount + biaya on-charge (atau additional_cost
        // tersimpan bila order_payment sudah ada).
        $dppByOrder = [];
        $totalDpp = 0;
        foreach ($orderCodes as $orderCode) {
            $order = $orders->firstWhere('code', $orderCode);
            $fin = $this->orderFinancials($order);
            $dpp = (int) round($fin['subtotal']);

            if ($dpp < 0) {
                throw new \DomainException('Nilai DPP order tidak valid.', 422);
            }

            $dppByOrder[$orderCode] = $dpp;
            $totalDpp += $dpp;
        }

        // Nominal pajak dihitung dari total DPP berdasarkan rate yang diinput.
        $ppnAmount = (int) round($totalDpp * $ppnRate / 100);
        $pphAmount = (int) round($totalDpp * $pphRate / 100);

        // Validasi: total bayar (DPP + PPN − PPh − Claim) tidak boleh negatif
        $grandTotal = $totalDpp + $ppnAmount - $pphAmount - $claimAmount;
        if ($grandTotal < 0) {
            throw new \DomainException('Total bayar (DPP + PPN − PPh − Claim) tidak boleh negatif. Periksa kembali persentase PPh dan nominal Biaya Claim yang diinput.', 422);
        }

        // Distribusi nominal pajak & claim proporsional ke tiap order (largest remainder).
        $ppnShares = $this->distributeProportionally($ppnAmount, $dppByOrder, $totalDpp);
        $pphShares = $this->distributeProportionally($pphAmount, $dppByOrder, $totalDpp);
        $claimShares = $this->distributeProportionally($claimAmount, $dppByOrder, $totalDpp);

        $logPayment = null;

        foreach ($orderCodes as $orderCode) {
            $order = $orders->firstWhere('code', $orderCode);
            $orderPayment = $orderPayments->firstWhere('orderCode', $orderCode);
            $fin = $this->orderFinancials($order);

            $billingData = [
                'cost' => (float) $fin['cost'],
                'additional_cost' => (float) $fin['additionalCost'],
                'claim' => (float) ($claimShares[$orderCode] ?? 0),
                'claim_description' => $claimDescription,
                'ppn' => (float) ($ppnShares[$orderCode] ?? 0),
                'ppn_percent' => $ppnRate > 0 ? $ppnRate : null,
                'pph' => (float) ($pphShares[$orderCode] ?? 0),
                'pph_percent' => $pphRate > 0 ? $pphRate : null,
                'total' => 0,
                'status' => 0,
                'nota_number' => $notaNumber,
                'user_bank_code' => $userBank->code,
            ];

            if ($orderPayment) {
                $orderPayment->update($billingData);
                $logPayment = $logPayment ?: $orderPayment;
            } else {
                $orderPayment = $this->service->create(array_merge([
                    'code' => GenerateCode::generateUniqueCode('FOP', 'order_payment'),
                    'orderCode' => $orderCode,
                ], $billingData));
                $logPayment = $logPayment ?: $orderPayment;
            }
        }

        // Log activity
        if ($logPayment) {
            $this->logActivity($title, $logPayment, 'Generate Nota ' . $notaNumber);
        }

        return $notaNumber;
    }

    /**
     * Batalkan nomor nota (hanya jika belum ada pembayaran sama sekali).
     * Seluruh baris order_payment pada nota tersebut dihapus.
     */
    public function cancelNota($orderCode, $title): void
    {
        $snapshot = $this->service->newQuery()
            ->where('orderCode', $orderCode)
            ->first();

        if (! $snapshot) {
            throw new \DomainException('Data nota tidak ditemukan.', 422);
        }

        $paymentsInNota = $this->service->newQuery()
            ->when(
                $snapshot->nota_number,
                fn ($query) => $query->where('nota_number', $snapshot->nota_number),
                fn ($query) => $query->whereKey($snapshot->id)
            )
            ->orderBy('orderCode')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        $orderPayment = $paymentsInNota->firstWhere('orderCode', $orderCode);

        if (! $orderPayment || $paymentsInNota->isEmpty()) {
            throw new \DomainException('Data nota telah berubah. Muat ulang halaman.', 409);
        }

        $hasHistory = $this->orderPaymentHistory->newQuery()
            ->whereIn('orderCode', $paymentsInNota->pluck('orderCode'))
            ->lockForUpdate()
            ->exists();
        $alreadyPaid = $paymentsInNota->contains(function ($payment) {
            return (float) ($payment->total ?? 0) > 0 || (int) ($payment->status ?? 0) !== 0;
        });

        if ($hasHistory || $alreadyPaid) {
            throw new \DomainException(
                'Nota ' . ($orderPayment->nota_number ?: '-') . ' sudah memiliki pembayaran. Batalkan batch pembayaran terlebih dahulu.',
                409
            );
        }

        foreach ($paymentsInNota as $payment) {
            $payment->forceDelete();
        }

        $this->logActivity(
            $title,
            $orderPayment,
            $orderPayment->nota_number
                ? 'Cancel Nota ' . $orderPayment->nota_number . ' (All associated orders reset)'
                : 'Cancel Unassigned Payment Record'
        );
    }

    /**
     * Grup order_payment per nomor nota (1 nota = beberapa DO milik 1 customer).
     * Return koleksi objek nota dengan agregat tagihan/terbayar/sisa & status.
     *
     * Berbeda dari vendor (pajak nota tersimpan sama di semua baris → MAX),
     * pajak & claim di sini DIDISTRIBUSIKAN per order → dijumlahkan (SUM).
     */
    public function findNotaGroups()
    {
        $orderPayments = $this->service->with([
            'order.fleet',
            'order.customer.company',
            'order.driver',
            'order.route',
            'order.route.originLocation',
            'order.route.destinationLocation',
            'paymentHistory.userBank.bank',
        ])
            ->whereNotNull('nota_number')
            ->get();

        return $orderPayments->groupBy('nota_number')->map(function ($group, $notaNumber) {
            $orders = $group->pluck('order')->filter();
            $firstPayment = $group->first();
            $firstOrder = $orders->first();

            $totalBilling = 0;
            $totalPaid = 0;
            $totalRemaining = 0;
            foreach ($group as $orderPayment) {
                $billing = $this->orderPaymentBilling($orderPayment);
                $paid = (float) ($orderPayment->total ?? 0);
                $totalBilling += $billing;
                $totalPaid += $paid;
                $totalRemaining += max(0, $billing - $paid);
            }

            // Pajak & claim terdistribusi per order → SUM untuk total nota.
            $notaCost = (float) $group->sum('cost');
            $notaAdditionalCost = (float) $group->sum('additional_cost');
            $notaPpn = (float) $group->sum('ppn');
            $notaPph = (float) $group->sum('pph');
            $notaClaim = (float) $group->sum('claim');
            // Rate disimpan sama di semua baris → MAX agar tidak ambigu.
            $notaPpnRate = $group->max('ppn_percent');
            $notaPphRate = $group->max('pph_percent');

            $latestBatchCode = $group
                ->flatMap(fn ($payment) => $payment->paymentHistory)
                ->pluck('batch_code')
                ->filter()
                ->sortDesc(SORT_STRING)
                ->first();

            $status = 'pending';
            if ($group->every(fn ($op) => (int) ($op->status ?? 0) === 1)) {
                $status = 'paid';
            } elseif ($totalPaid > 0) {
                $status = 'partial';
            }

            return (object) [
                'nota_number' => $notaNumber,
                'customer_code' => $firstOrder?->customerCode ?? ($firstOrder?->customer?->code ?? ''),
                'customer_name' => $firstOrder?->customer?->name,
                'order_format' => strtoupper(trim((string) ($firstOrder?->customer?->company?->format ?? ''))),
                'order_count' => $group->count(),
                'orders' => $orders,
                'order_codes' => $group->pluck('orderCode')->values(),
                'plate_numbers' => $orders->map(fn ($o) => $o?->fleet?->plateNumber)->filter()->values(),
                // Format ISO agar sorting di server (yajra) benar secara
                // kronologis; tampilan diformat ulang di sisi JavaScript.
                'nota_date' => optional($group->min('created_at'))->format('Y-m-d\TH:i:s'),
                'date' => optional($group->min('created_at'))->format('Y-m-d'),
                'amount' => $totalBilling,
                'paid_amount' => $totalPaid,
                'remaining_amount' => $totalRemaining,
                'cost_amount' => $notaCost,
                'additional_cost_amount' => $notaAdditionalCost,
                'ppn_amount' => $notaPpn,
                'pph_amount' => $notaPph,
                'claim_amount' => $notaClaim,
                'ppn_rate' => $notaPpnRate !== null ? (float) $notaPpnRate : 0,
                'pph_rate' => $notaPphRate !== null ? (float) $notaPphRate : 0,
                'payment_status' => $status,
                'user_bank_code' => $firstPayment?->user_bank_code,
                'latest_batch_code' => $latestBatchCode,
            ];
        })->values();
    }

    /**
     * Nota yang belum lunas (pending / partial).
     */
    public function findUnpaidNotas()
    {
        return $this->findNotaGroups()->filter(fn ($nota) => $nota->payment_status !== 'paid')->values();
    }

    /**
     * Nota yang sudah lunas (semua order di dalamnya paid).
     */
    public function findPaidNotas()
    {
        return $this->findNotaGroups()->filter(fn ($nota) => $nota->payment_status === 'paid')->values();
    }

    /**
     * Unit pembayaran = SATU baris di halaman Order Belum Lunas / Order Lunas.
     *
     * Dua jenis unit:
     * - 'order' : order standalone (belum masuk nota) → dibayar individual
     * - 'nota'  : grup order dalam satu nomor nota → dibayar per nota
     *
     * Struktur objek dibuat seragam agar datatable & statistik seragam.
     */
    private function buildOrderUnit($order): object
    {
        $fin = $this->orderFinancials($order);

        $origin = $order->route->originLocation->name ?? '';
        $dest = $order->route->destinationLocation->name ?? '';
        $customerName = $order->customer->name ?? '';

        return (object) [
            'unit_type' => 'order',
            'unit_key' => $order->code,
            'code' => $order->code,
            'date' => optional($order->orderDate)->format('Y-m-d'),
            'customer_code' => $order->customerCode ?? ($order->customer->code ?? ''),
            'customer_name' => $customerName,
            'plate' => $order->fleet->plateNumber ?? '',
            'driver' => $order->driver->name ?? '',
            'shipment' => $order->shipmentNumber ?? '',
            'rute' => trim($origin . ($dest ? ' → ' . $dest : '')),
            'cost' => $fin['cost'],
            'additional_cost' => $fin['additionalCost'],
            'ppn' => $fin['ppn'],
            'ppn_percent' => $fin['ppnPercent'],
            'pph' => $fin['pph'],
            'pph_percent' => $fin['pphPercent'],
            'claim' => $fin['claim'],
            'grand_total' => $fin['grandTotal'],
            'payment' => $fin['payment'],
            'remaining' => $fin['remaining'],
            'status_code' => $fin['statusCode'],
            'status_label' => $fin['statusLabel'],
            'payment_status' => $fin['statusCode'] === 'unpaid' ? 'pending' : ($fin['statusCode'] === 'paid' || $fin['statusCode'] === 'overpaid' ? 'paid' : 'partial'),
            'order_codes' => collect([$order->code]),
            'order_count' => 1,
            'nota_number' => null,
            'nota_date' => null,
            'latest_batch_code' => null,
            'search_text' => implode(' ', array_filter([
                $order->code,
                $order->shipmentNumber,
                $order->orderDate,
                $customerName,
                $order->fleet->plateNumber ?? '',
                $order->driver->name ?? '',
                $origin,
                $dest,
                $fin['statusLabel'],
            ])),
        ];
    }

    private function buildNotaUnit($nota): object
    {
        $plates = $nota->plate_numbers;

        return (object) [
            'unit_type' => 'nota',
            'unit_key' => $nota->nota_number,
            'code' => $nota->nota_number,
            'date' => $nota->nota_date,
            'customer_code' => $nota->customer_code,
            'customer_name' => $nota->customer_name ?? '',
            'plate' => $plates->implode(', '),
            'driver' => '',
            'shipment' => '',
            'rute' => '',
            'cost' => (float) ($nota->cost_amount ?? 0),
            'additional_cost' => (float) ($nota->additional_cost_amount ?? 0),
            'ppn' => (float) $nota->ppn_amount,
            'ppn_percent' => $nota->ppn_rate,
            'pph' => (float) $nota->pph_amount,
            'pph_percent' => $nota->pph_rate,
            'claim' => (float) $nota->claim_amount,
            'grand_total' => (float) $nota->amount,
            'payment' => (float) $nota->paid_amount,
            'remaining' => (float) $nota->remaining_amount,
            'status_code' => $nota->payment_status,
            'status_label' => $nota->payment_status === 'paid' ? 'Lunas' : ($nota->payment_status === 'partial' ? 'Belum Lunas' : 'Belum Bayar'),
            'payment_status' => $nota->payment_status,
            'order_codes' => $nota->order_codes,
            'order_count' => (int) $nota->order_count,
            'nota_number' => $nota->nota_number,
            'nota_date' => $nota->nota_date,
            'latest_batch_code' => $nota->latest_batch_code,
            'search_text' => implode(' ', array_filter([
                $nota->nota_number,
                $nota->customer_name ?? '',
                $nota->orders->pluck('code')->implode(' '),
                $nota->orders->pluck('shipmentNumber')->filter()->implode(' '),
                $plates->implode(' '),
            ])),
        ];
    }

    /**
     * Semua unit (order standalone + nota) milik customer non-DO.
     */
    public function findAllUnits()
    {
        $units = collect();

        // Order standalone = TIDAK punya order_payment berta nota.
        foreach ($this->findAllIsDoZero() as $order) {
            if (isset($order->orderPayment->nota_number) && $order->orderPayment->nota_number) {
                continue;
            }

            $units->push($this->buildOrderUnit($order));
        }

        foreach ($this->findNotaGroups() as $nota) {
            $units->push($this->buildNotaUnit($nota));
        }

        return $units->sortBy(fn ($unit) => (string) $unit->date)->values();
    }

    /**
     * Unit yang belum lunas: order standalone unpaid/partial + nota pending/partial.
     */
    public function findUnpaidUnits()
    {
        return $this->findAllUnits()->filter(function ($unit) {
            if ($unit->unit_type === 'nota') {
                return $unit->payment_status !== 'paid';
            }

            return in_array($unit->status_code, ['unpaid', 'partial'], true);
        })->values();
    }

    /**
     * Unit yang sudah lunas: order standalone paid/overpaid + nota paid.
     */
    public function findPaidUnits()
    {
        return $this->findAllUnits()->filter(function ($unit) {
            if ($unit->unit_type === 'nota') {
                return $unit->payment_status === 'paid';
            }

            return in_array($unit->status_code, ['paid', 'overpaid'], true);
        })->values();
    }

    /**
     * Statistik untuk halaman Order Belum Lunas.
     */
    public function statsUnpaid()
    {
        $units = $this->findUnpaidUnits();

        $orderUnits = $units->where('unit_type', 'order');
        $notaUnits = $units->where('unit_type', 'nota');

        return [
            'totalCount' => $units->count(),
            'orderCount' => $orderUnits->count(),
            'notaCount' => $notaUnits->count(),
            'unpaidCount' => $units->where('payment_status', 'pending')->count(),
            'partialCount' => $units->where('payment_status', 'partial')->count(),
            'totalBilling' => (float) $units->sum('grand_total'),
            'totalPaid' => (float) $units->sum('payment'),
            'totalRemaining' => (float) $units->sum('remaining'),
        ];
    }

    /**
     * Statistik untuk halaman Order Lunas.
     */
    public function statsPaid()
    {
        $units = $this->findPaidUnits();

        return [
            'totalCount' => $units->count(),
            'orderCount' => $units->where('unit_type', 'order')->count(),
            'notaCount' => $units->where('unit_type', 'nota')->count(),
            'totalPaid' => (float) $units->sum('payment'),
        ];
    }

    /**
     * Statistik untuk halaman Daftar Pembayaran.
     */
    public function statsPayments()
    {
        $payments = $this->findPayments();

        return [
            'paymentCount' => $payments->count(),
            'paymentSum' => (float) $payments->sum('amount'),
            'notaCount' => $payments
                ->flatMap(fn ($payment) => $payment->notas->pluck('number'))
                ->filter(fn ($nota) => $nota !== '-')
                ->unique()
                ->count(),
            'customerCount' => $payments
                ->flatMap(fn ($payment) => $payment->customers)
                ->unique()
                ->count(),
        ];
    }

    // =====================================================================
    // BATCH PEMBAYARAN (per nota)
    // =====================================================================

    /**
     * Simpan satu transaksi pembayaran yang bisa mencakup beberapa nota
     * sekaligus (lunas / DP / cicilan). Idempotent via request_key +
     * payload_hash sehanya double-submit tidak membuat pembayaran ganda.
     */
    public function storeBatch($request, $title)
    {
        $requestKey = trim((string) $request->requestKey);
        $payments = collect($request->payments ?? [])->map(function ($payment) {
            if (! is_array($payment)) {
                throw new \DomainException('Data alokasi pembayaran tidak valid.', 422);
            }

            return [
                'nota_number' => trim((string) ($payment['nota_number'] ?? '')),
                'amount' => (int) ($payment['amount'] ?? 0),
                'expected_remaining' => (int) ($payment['expected_remaining'] ?? 0),
            ];
        });

        if ($payments->isEmpty() || $payments->contains(fn ($payment) => $payment['nota_number'] === '')) {
            throw new \DomainException('Pilih minimal satu nota yang valid untuk dibayar.', 422);
        }

        if ($payments->pluck('nota_number')->unique()->count() !== $payments->count()) {
            throw new \DomainException('Nomor nota pembayaran tidak boleh duplikat.', 422);
        }

        if ($payments->contains(fn ($payment) => $payment['amount'] < 1 || $payment['expected_remaining'] < 1)) {
            throw new \DomainException('Nominal pembayaran dan sisa tagihan harus berupa rupiah positif.', 422);
        }

        $payments = $payments->sortBy('nota_number', SORT_STRING)->values();
        $notaNumbers = $payments->pluck('nota_number')->all();
        $totalPaymentAmount = (int) $payments->sum('amount');
        if ($totalPaymentAmount > 2147483647) {
            throw new \DomainException('Total pembayaran maksimal Rp 2.147.483.647 per transaksi.', 422);
        }

        $payloadHash = $this->paymentPayloadHash($request, $payments);
        $existingBatch = OrderPaymentBatch::where('request_key', $requestKey)->first();

        if ($existingBatch) {
            return $this->resolveExistingBatch($existingBatch, $payloadHash);
        }

        $userBank = $this->userBank->where('code', $request->userBankCode)->first();
        if (! $userBank || (int) $userBank->type !== 2) {
            throw new \DomainException('Sumber dana perusahaan tidak ditemukan atau tidak valid.', 422);
        }

        $liveMutation = LiveMutation::where('userBankCode', $userBank->code)
            ->lockForUpdate()
            ->first();

        if (! $liveMutation) {
            throw new \DomainException('Saldo sumber dana tidak tersedia.', 422);
        }

        $existingBatch = OrderPaymentBatch::where('request_key', $requestKey)
            ->lockForUpdate()
            ->first();

        if ($existingBatch) {
            return $this->resolveExistingBatch($existingBatch, $payloadHash);
        }

        $orderPayments = $this->service->newQuery()
            ->with(['order.customer'])
            ->whereIn('nota_number', $notaNumbers)
            ->orderBy('nota_number')
            ->orderBy('orderCode')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $foundNotas = $orderPayments->pluck('nota_number')->unique()->sort()->values();
        if ($foundNotas->count() !== count($notaNumbers) || $foundNotas->all() !== $notaNumbers) {
            throw new \DomainException('Satu atau beberapa nota tidak ditemukan.', 422);
        }

        foreach ($orderPayments as $orderPayment) {
            if (! $orderPayment->order || (int) ($orderPayment->order->customer->isDo ?? 1) !== 0) {
                throw new \DomainException('Nota hanya dapat dibayar untuk order customer non-DO yang valid.', 422);
            }
        }

        $batchCode = GenerateCode::generateUniqueCode('DPB', 'order_payment_batch');
        $fullyPaidCount = 0;
        $partialCount = 0;
        $processedOrderCount = 0;
        $allocatedTotal = 0;

        foreach ($payments as $payment) {
            $notaPayments = $orderPayments->where('nota_number', $payment['nota_number']);
            $remainingByOrder = [];

            foreach ($notaPayments as $orderPayment) {
                $billingAmount = (int) round($this->orderPaymentBilling($orderPayment));
                $paidAmount = (int) round((float) $orderPayment->total);
                $remainingByOrder[$orderPayment->id] = max(0, $billingAmount - $paidAmount);
            }

            $currentRemaining = array_sum($remainingByOrder);
            $isFullyPaid = $notaPayments->every(fn ($orderPayment) => (int) $orderPayment->status === 1);

            if ($isFullyPaid || $currentRemaining < 1) {
                throw new \DomainException('Salah satu nota yang dipilih sudah lunas.', 409);
            }

            if ($payment['expected_remaining'] !== $currentRemaining) {
                throw new \DomainException('Sisa tagihan salah satu nota telah berubah. Muat ulang data sebelum membayar.', 409);
            }

            if ($payment['amount'] > $currentRemaining) {
                throw new \DomainException('Nominal pembayaran salah satu nota melebihi sisa tagihan.', 422);
            }

            $orderAllocations = $this->distributeProportionally(
                $payment['amount'],
                $remainingByOrder,
                $currentRemaining
            );
            $notaAllocatedTotal = array_sum($orderAllocations);

            if ($notaAllocatedTotal !== $payment['amount']) {
                throw new \LogicException('Order payment allocation total is inconsistent.');
            }

            foreach ($notaPayments as $orderPayment) {
                $paymentAmount = (int) ($orderAllocations[$orderPayment->id] ?? 0);
                if ($paymentAmount < 1) {
                    continue;
                }

                $billingAmount = (int) round($this->orderPaymentBilling($orderPayment));
                $paidAmount = (int) round((float) $orderPayment->total);
                $newPaidAmount = $paidAmount + $paymentAmount;
                $newRemainingAmount = max(0, $billingAmount - $newPaidAmount);

                // total = akumulasi pembayaran; status = 1 saat lunas.
                // user_bank_code (bank nota untuk cetak PDF) TIDAK dioverwrite —
                // bank pembayaran hidup di order_payment_history.
                $orderPayment->update([
                    'total' => $newPaidAmount,
                    'status' => $newRemainingAmount === 0 ? 1 : 0,
                ]);

                $this->orderPaymentHistory->create([
                    'code' => GenerateCode::generateUniqueCode('FOPH', 'order_payment_history'),
                    'orderCode' => $orderPayment->orderCode,
                    'paymentType' => $newRemainingAmount === 0 ? 'Full' : 'Dp',
                    'claim' => (float) ($orderPayment->claim ?? 0),
                    'claim_description' => $orderPayment->claim_description,
                    'ppn' => (float) ($orderPayment->ppn ?? 0),
                    'ppn_percent' => $orderPayment->ppn_percent,
                    'pph' => (float) ($orderPayment->pph ?? 0),
                    'pph_percent' => $orderPayment->pph_percent,
                    'total' => $paymentAmount,
                    'date' => $request->date,
                    'description' => $request->description,
                    'userBankCode' => $userBank->code,
                    'batch_code' => $batchCode,
                ]);

                $this->mutation->create([
                    'code' => GenerateCode::generateUniqueCode('FMT', 'mutation'),
                    'userBankCode' => $userBank->code,
                    'nominal' => $paymentAmount,
                    'type' => 'In',
                    'date' => $request->date,
                    'description' => 'Order Payment Batch ' . $batchCode . ' for Order ' . $orderPayment->orderCode . ' with amount ' . number_format($paymentAmount, 0, '.', ','),
                    'transactionCode' => $batchCode,
                    'transactionTypeCode' => 'FTT250306114178', // Order Payment
                ]);

                $this->logActivity($title, $orderPayment, 'Update');
                $processedOrderCount++;
                $allocatedTotal += $paymentAmount;
            }

            if ($payment['amount'] === $currentRemaining) {
                $fullyPaidCount++;
            } else {
                $partialCount++;
            }
        }

        if ($allocatedTotal !== $totalPaymentAmount) {
            throw new \LogicException('Order payment batch allocation total is inconsistent.');
        }

        $persistedTotal = (int) round((float) $this->orderPaymentHistory
            ->newQuery()
            ->where('batch_code', $batchCode)
            ->sum('total'));

        if ($persistedTotal !== $totalPaymentAmount) {
            throw new \LogicException('Persisted order payment allocation total is inconsistent.');
        }

        // Penerimaan uang menambah DEBIT rekening (balance = debit - credit).
        $currentCredit = (int) round((float) $liveMutation->credit);
        $currentDebit = (int) round((float) $liveMutation->debit);
        if ($currentDebit + $totalPaymentAmount > 2147483647) {
            throw new \DomainException('Akumulasi penerimaan rekening melewati kapasitas ledger.', 422);
        }

        $liveMutation->debit = $currentDebit + $totalPaymentAmount;
        $liveMutation->balance = $liveMutation->debit - $currentCredit;
        $liveMutation->save();

        $batch = OrderPaymentBatch::create([
            'code' => $batchCode,
            'request_key' => $requestKey,
            'payload_hash' => $payloadHash,
            'status' => 'active',
            'payment_date' => $request->date,
            'user_bank_code' => $userBank->code,
            'amount' => $totalPaymentAmount,
            'nota_count' => count($notaNumbers),
            'order_count' => $processedOrderCount,
            'fully_paid_count' => $fullyPaidCount,
            'partial_count' => $partialCount,
            'description' => $request->description,
        ]);

        return $this->buildBatchResult($batch, false);
    }

    public function findBatchResultByRequest($request): ?array
    {
        $payments = collect($request->payments ?? [])->map(fn ($payment) => [
            'nota_number' => trim((string) ($payment['nota_number'] ?? '')),
            'amount' => (int) ($payment['amount'] ?? 0),
            'expected_remaining' => (int) ($payment['expected_remaining'] ?? 0),
        ])->sortBy('nota_number', SORT_STRING)->values();
        $batch = OrderPaymentBatch::where('request_key', trim((string) $request->requestKey))->first();

        return $batch
            ? $this->resolveExistingBatch($batch, $this->paymentPayloadHash($request, $payments))
            : null;
    }

    private function paymentPayloadHash($request, $payments): string
    {
        $payload = [
            'payments' => $payments->values()->all(),
            'date' => (string) $request->date,
            'user_bank_code' => trim((string) $request->userBankCode),
            'description' => trim((string) $request->description),
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function resolveExistingBatch(OrderPaymentBatch $batch, string $payloadHash): array
    {
        if (! hash_equals((string) $batch->payload_hash, $payloadHash)) {
            throw new \DomainException('Request key sudah digunakan untuk detail pembayaran yang berbeda.', 409);
        }

        if ($batch->status !== 'active') {
            throw new \DomainException('Pembayaran dengan request key ini sudah dibatalkan dan tidak dapat dibuat ulang.', 409);
        }

        return $this->buildBatchResult($batch, true);
    }

    private function buildBatchResult(OrderPaymentBatch $batch, bool $idempotent): array
    {
        $histories = $this->orderPaymentHistory->newQuery()
            ->with('orderPayment')
            ->where('batch_code', $batch->code)
            ->orderBy('orderCode')
            ->get();

        $allocations = $histories
            ->groupBy(fn ($history) => (string) ($history->orderPayment?->nota_number ?? ''))
            ->filter(fn ($group, $notaNumber) => $notaNumber !== '')
            ->map(function ($group, $notaNumber) {
                return [
                    'nota_number' => $notaNumber,
                    'payment_amount' => (int) round((float) $group->sum('total')),
                    'order_count' => $group->count(),
                    'orders' => $group->map(function ($history) {
                        return [
                            'order_code' => $history->orderCode,
                            'payment_amount' => (int) round((float) $history->total),
                        ];
                    })->values()->all(),
                ];
            })
            ->sortBy('nota_number')
            ->values()
            ->all();

        return [
            'batch_code' => $batch->code,
            'payment_amount' => (int) $batch->amount,
            'nota_count' => (int) $batch->nota_count,
            'processed_count' => (int) $batch->order_count,
            'order_count' => (int) $batch->order_count,
            'fully_paid_count' => (int) $batch->fully_paid_count,
            'partial_count' => (int) $batch->partial_count,
            'allocations' => $allocations,
            'idempotent' => $idempotent,
        ];
    }

    /**
     * Membatalkan satu batch pembayaran terakhir dari nota/order yang dipilih.
     * History batch lain tetap dipertahankan agar cicilan sebelumnya tidak hilang.
     */
    public function cancelPayment($orderCode, $expectedBatchCode, $title): array
    {
        $batchCode = trim((string) $expectedBatchCode);
        $batchSnapshot = OrderPaymentBatch::where('code', $batchCode)->first();

        if (! $batchSnapshot) {
            throw new \DomainException('Batch pembayaran tidak ditemukan.', 422);
        }

        if ($batchSnapshot->status !== 'active') {
            throw new \DomainException('Batch pembayaran ini sudah dibatalkan sebelumnya.', 409);
        }

        $selectedPayment = $this->service->newQuery()
            ->where('orderCode', $orderCode)
            ->first();

        if (! $selectedPayment) {
            throw new \DomainException('Data pembayaran order tidak ditemukan.', 422);
        }

        $initialHistories = $this->orderPaymentHistory->newQuery()
            ->with('orderPayment:id,orderCode,nota_number')
            ->where('batch_code', $batchCode)
            ->get();
        $batchNotaNumbers = $initialHistories
            ->pluck('orderPayment.nota_number')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($initialHistories->isEmpty() || $batchNotaNumbers->isEmpty()) {
            throw new \DomainException('Riwayat batch pembayaran tidak ditemukan.', 422);
        }

        if (! $selectedPayment->nota_number || ! $batchNotaNumbers->contains($selectedPayment->nota_number)) {
            throw new \DomainException('Batch pembayaran tidak sesuai dengan nota yang dipilih.', 409);
        }

        $refundsByBank = $initialHistories
            ->groupBy('userBankCode')
            ->map(fn ($group) => (int) round((float) $group->sum('total')))
            ->sortKeys();
        $liveMutations = collect();

        foreach ($refundsByBank as $bankCode => $refundAmount) {
            if (! $bankCode || $refundAmount < 1) {
                throw new \DomainException('Data rekening atau nominal refund pada batch tidak valid.', 422);
            }

            $liveMutation = LiveMutation::where('userBankCode', $bankCode)
                ->lockForUpdate()
                ->first();

            if (! $liveMutation) {
                throw new \DomainException('Saldo bank untuk pembatalan batch tidak ditemukan.', 422);
            }

            if ((int) round((float) $liveMutation->debit) < $refundAmount) {
                throw new \DomainException('Akumulasi penerimaan rekening tidak konsisten dengan nominal batch. Pembatalan dihentikan demi keamanan.', 422);
            }

            $liveMutations->put($bankCode, $liveMutation);
        }

        $batch = OrderPaymentBatch::where('code', $batchCode)
            ->lockForUpdate()
            ->first();

        if (! $batch || $batch->status !== 'active') {
            throw new \DomainException('Batch pembayaran ini sudah dibatalkan atau berubah.', 409);
        }

        $paymentsInBatch = $this->service->newQuery()
            ->with('order')
            ->whereIn('nota_number', $batchNotaNumbers)
            ->orderBy('nota_number')
            ->orderBy('orderCode')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        $selectedPayment = $paymentsInBatch->firstWhere('orderCode', $orderCode);

        if (! $selectedPayment || ! $selectedPayment->nota_number
            || ! $batchNotaNumbers->contains($selectedPayment->nota_number)) {
            throw new \DomainException('Data nota telah berubah. Muat ulang halaman sebelum membatalkan.', 409);
        }

        $allOrderCodes = $paymentsInBatch->pluck('orderCode')->sort()->values();
        $allHistories = $this->orderPaymentHistory->newQuery()
            ->whereIn('orderCode', $allOrderCodes)
            ->orderBy('orderCode')
            ->orderBy('batch_code')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        $histories = $allHistories->where('batch_code', $batchCode)->values();
        $lockedRefundsByBank = $histories
            ->groupBy('userBankCode')
            ->map(fn ($group) => (int) round((float) $group->sum('total')))
            ->sortKeys();

        if ($histories->count() !== $initialHistories->count()
            || $lockedRefundsByBank->all() !== $refundsByBank->all()) {
            throw new \DomainException('Data batch pembayaran telah berubah. Muat ulang halaman sebelum membatalkan.', 409);
        }

        if ($refundsByBank->count() !== 1
            || (string) $refundsByBank->keys()->first() !== (string) $batch->user_bank_code
            || (int) $refundsByBank->sum() !== (int) $batch->amount) {
            throw new \DomainException('Ringkasan batch tidak konsisten dengan riwayat pembayaran. Pembatalan dihentikan demi keamanan.', 422);
        }

        foreach ($batchNotaNumbers as $notaNumber) {
            $notaOrderCodes = $paymentsInBatch
                ->where('nota_number', $notaNumber)
                ->pluck('orderCode');
            $latestBatchCode = $allHistories
                ->whereIn('orderCode', $notaOrderCodes)
                ->pluck('batch_code')
                ->filter()
                ->max();

            if ($latestBatchCode !== $batchCode) {
                throw new \DomainException('Salah satu nota dalam batch memiliki pembayaran lebih baru. Batalkan batch terbaru terlebih dahulu.', 409);
            }
        }

        if ($histories->pluck('orderCode')->diff($allOrderCodes)->isNotEmpty()) {
            throw new \DomainException('Sebagian data nota pada batch tidak ditemukan.', 422);
        }

        $mutations = $this->mutation->newQuery()
            ->where('transactionCode', $batchCode)
            ->orderBy('userBankCode')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        $mutationRefundsByBank = $mutations
            ->where('type', 'In')
            ->groupBy('userBankCode')
            ->map(fn ($group) => (int) round((float) $group->sum('nominal')))
            ->sortKeys();
        $refundTotal = (int) $refundsByBank->sum();

        if ($mutations->count() !== $histories->count()
            || $mutations->contains(fn ($mutation) => $mutation->type !== 'In')
            || $mutationRefundsByBank->all() !== $refundsByBank->all()) {
            throw new \DomainException('Nilai mutasi rekening tidak konsisten dengan riwayat pembayaran. Pembatalan dihentikan demi keamanan.', 422);
        }

        foreach ($refundsByBank as $bankCode => $refundAmount) {
            $liveMutation = $liveMutations->get($bankCode);
            $liveMutation->debit = (int) round((float) $liveMutation->debit) - $refundAmount;
            $liveMutation->balance = (int) round((float) $liveMutation->debit) - (int) round((float) $liveMutation->credit);
            $liveMutation->save();
        }

        $this->mutation->newQuery()
            ->where('transactionCode', $batchCode)
            ->forceDelete();
        $this->orderPaymentHistory->newQuery()
            ->where('batch_code', $batchCode)
            ->forceDelete();

        foreach ($paymentsInBatch as $payment) {
            $remainingHistories = $allHistories
                ->where('orderCode', $payment->orderCode)
                ->where('batch_code', '!=', $batchCode)
                ->sortByDesc('batch_code')
                ->values();
            $paidAmount = (int) round((float) $remainingHistories->sum('total'));
            $billingAmount = (int) round($this->orderPaymentBilling($payment));
            $remainingAmount = max(0, $billingAmount - $paidAmount);

            // total = akumulasi pembayaran; status = 1 hanya saat benar-benar
            // lunas (ada pembayaran dan sisa nol).
            $payment->update([
                'total' => $paidAmount,
                'status' => ($remainingAmount === 0 && $paidAmount > 0) ? 1 : 0,
            ]);

            $this->logActivity($title, $payment, 'Cancel Payment Batch ' . $batchCode);
        }

        $batch->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return [
            'batch_code' => $batchCode,
            'payment_amount' => $refundTotal,
            'order_count' => $histories->pluck('orderCode')->unique()->count(),
            'nota_count' => $batchNotaNumbers->count(),
        ];
    }

    // =====================================================================
    // DAFTAR PEMBAYARAN (riwayat transaksi)
    // =====================================================================

    /**
     * Daftar transaksi pembayaran customer per transaksi.
     *
     * Satu transaksi batch disimpan dengan satu batch_code, meski dialokasikan
     * ke beberapa order dalam satu atau beberapa nota. Riwayat lama (pembayaran
     * tunggal) yang belum memiliki batch_code tetap ditampilkan per alokasi.
     */
    public function findPayments()
    {
        $histories = $this->orderPaymentHistory->with($this->paymentHistoryRelations())
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        return $this->mapPaymentTransactions($histories);
    }

    /** Ambil satu transaksi beserta rincian nota dan order-nya. */
    public function findPaymentDetail(string $transactionKey)
    {
        $query = $this->orderPaymentHistory->with($this->paymentHistoryRelations());

        if (str_starts_with($transactionKey, 'batch:')) {
            $query->where('batch_code', substr($transactionKey, 6));
        } elseif (str_starts_with($transactionKey, 'legacy:')) {
            $query->whereKey(substr($transactionKey, 7));
        } else {
            return null;
        }

        return $this->mapPaymentTransactions(
            $query->orderByDesc('date')->orderByDesc('created_at')->get()
        )->first();
    }

    private function paymentHistoryRelations(): array
    {
        return [
            'userBank.bank',
            'orderPayment',
            'order.customer.company',
            'order.fleet',
        ];
    }

    private function mapPaymentTransactions($histories)
    {
        return $histories
            ->groupBy(fn ($history) => $history->batch_code
                ? 'batch:' . $history->batch_code
                : 'legacy:' . $history->id)
            ->map(function ($transaction, $transactionKey) {
                $firstHistory = $transaction->first();
                $isLegacy = empty($firstHistory->batch_code);

                $notas = $transaction
                    ->groupBy(fn ($history) => (string) ($history->orderPayment?->nota_number ?: ($history->orderPayment ? 'order:' . $history->orderPayment->orderCode : '-')))
                    ->map(function ($notaHistories, $notaKey) {
                        $isStandalone = str_starts_with($notaKey, 'order:');

                        $orders = $notaHistories->map(function ($history) {
                            $order = $history->order;

                            return (object) [
                                'code' => $order?->code ?: ($history->orderPayment?->orderCode ?: ($history->orderCode ?: '-')),
                                'shipment_number' => $order?->shipmentNumber,
                                'customer_name' => $order?->customer?->name,
                                'amount' => (float) $history->total,
                            ];
                        })->sortBy('code')->values();

                        return (object) [
                            'number' => $isStandalone ? '-' : $notaKey,
                            'amount' => (float) $notaHistories->sum('total'),
                            'orders' => $orders,
                        ];
                    })
                    ->sortBy('number')
                    ->values();

                return (object) [
                    'transaction_key' => $transactionKey,
                    'payment_date' => $firstHistory->date,
                    'batch_code' => $firstHistory->batch_code,
                    'legacy_code' => $firstHistory->orderPayment?->code,
                    'is_legacy' => $isLegacy,
                    'amount' => (float) $transaction->sum('total'),
                    'userBank' => $firstHistory->userBank,
                    'description' => $transaction->pluck('description')->filter()->first(),
                    'notas' => $notas,
                    'customers' => $notas
                        ->flatMap(fn ($nota) => $nota->orders->pluck('customer_name'))
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values(),
                    'order_count' => (int) $notas->sum(fn ($nota) => $nota->orders->count()),
                ];
            })
            ->values();
    }

    // =====================================================================
    // DETAIL NOTA (untuk modal rincian)
    // =====================================================================

    /**
     * Rincian satu nota: daftar order + tagihan per order + riwayat pembayaran.
     */
    public function notaDetail($orderCode)
    {
        $orderPayment = $this->service->newQuery()
            ->where('orderCode', $orderCode)
            ->whereNotNull('nota_number')
            ->first();

        if (! $orderPayment) {
            return null;
        }

        $payments = $this->service->newQuery()
            ->with([
                'order.fleet',
                'order.driver',
                'order.customer.company',
                'order.route.originLocation',
                'order.route.destinationLocation',
                'paymentHistory.userBank.bank',
            ])
            ->where('nota_number', $orderPayment->nota_number)
            ->orderBy('orderCode')
            ->get();

        if ($payments->isEmpty()) {
            return null;
        }

        $orders = [];
        $totalCost = 0;
        $totalAdditionalCost = 0;
        $totalPpn = 0;
        $totalPph = 0;
        $totalClaim = 0;
        $totalGrandTotal = 0;
        $totalPaid = 0;

        foreach ($payments as $payment) {
            $billing = $this->orderPaymentBilling($payment);
            $paid = (float) ($payment->total ?? 0);
            $remaining = max(0, $billing - $paid);
            $order = $payment->order;

            $statusCode = 'unpaid';
            $statusLabel = 'Belum Bayar';
            if ($paid > 0) {
                if ((int) $payment->status === 1 || ($paid >= $billing && $billing > 0)) {
                    $statusCode = 'paid';
                    $statusLabel = 'Lunas';
                } else {
                    $statusCode = 'partial';
                    $statusLabel = 'Belum Lunas';
                }
            }

            $origin = $order?->route?->originLocation?->name ?? '';
            $dest = $order?->route?->destinationLocation?->name ?? '';

            $orders[] = [
                'code' => $payment->orderCode,
                'order_date' => $order?->orderDate ? \Carbon\Carbon::parse($order->orderDate)->format('d/m/Y') : '-',
                'shipment_number' => $order?->shipmentNumber ?? '-',
                'plate_number' => $order?->fleet?->plateNumber ?? '-',
                'driver_name' => $order?->driver?->name ?? '-',
                'rute' => trim($origin . ($dest ? ' → ' . $dest : '')) ?: '-',
                'cost' => (float) ($payment->cost ?? 0),
                'additional_cost' => (float) ($payment->additional_cost ?? 0),
                'ppn' => (float) ($payment->ppn ?? 0),
                'pph' => (float) ($payment->pph ?? 0),
                'claim' => (float) ($payment->claim ?? 0),
                'grand_total' => $billing,
                'payment' => $paid,
                'remaining' => $remaining,
                'status_code' => $statusCode,
                'status_label' => $statusLabel,
            ];

            $totalCost += (float) ($payment->cost ?? 0);
            $totalAdditionalCost += (float) ($payment->additional_cost ?? 0);
            $totalPpn += (float) ($payment->ppn ?? 0);
            $totalPph += (float) ($payment->pph ?? 0);
            $totalClaim += (float) ($payment->claim ?? 0);
            $totalGrandTotal += $billing;
            $totalPaid += $paid;
        }

        $paymentStatus = 'pending';
        if ($payments->every(fn ($payment) => (int) ($payment->status ?? 0) === 1)) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }

        // Riwayat pembayaran digabung per batch (satu transaksi).
        $histories = $payments
            ->flatMap(fn ($payment) => $payment->paymentHistory)
            ->sortByDesc('created_at')
            ->groupBy(fn ($history) => $history->batch_code ?: ('legacy:' . $history->id))
            ->map(function ($group) {
                $first = $group->first();
                $bankStr = '-';
                if ($first->userBank) {
                    $accNo = $first->userBank->accountNumber ?? '';
                    $bName = $first->userBank->bank->name ?? '';
                    $accName = $first->userBank->accountName ?? '';
                    $bankStr = trim("{$accNo} - {$bName} a/n {$accName}", ' -');
                }

                return [
                    'batch_code' => $first->batch_code ?: null,
                    'date' => $first->date ? \Carbon\Carbon::parse($first->date)->format('d/m/Y') : '-',
                    'type' => $group->pluck('paymentType')->contains('Full') ? 'Full' : 'Dp',
                    'bank' => $bankStr,
                    'description' => $first->description ?? '-',
                    'order_count' => $group->count(),
                    'total' => (float) $group->sum('total'),
                ];
            })
            ->values();

        $userBank = null;
        $userBankCode = $payments->first()->user_bank_code;
        if ($userBankCode) {
            $bank = $this->userBank->with('bank')->where('code', $userBankCode)->first();
            if ($bank) {
                $userBank = [
                    'name' => $bank->bank->name ?? 'Bank',
                    'account_number' => $bank->accountNumber ?? '',
                    'account_name' => $bank->accountName ?? '',
                ];
            }
        }

        $firstPayment = $payments->first();

        return [
            'nota_number' => $firstPayment->nota_number,
            'nota_date' => $firstPayment->created_at ? \Carbon\Carbon::parse($firstPayment->created_at)->format('d/m/Y') : '-',
            'customer_code' => $firstPayment->order?->customerCode ?? '',
            'customer_name' => $firstPayment->order?->customer?->name ?? '-',
            'order_count' => $payments->count(),
            'ppn_percent' => $firstPayment->ppn_percent !== null ? (float) $firstPayment->ppn_percent : null,
            'pph_percent' => $firstPayment->pph_percent !== null ? (float) $firstPayment->pph_percent : null,
            'claim_description' => $firstPayment->claim_description,
            'payment_status' => $paymentStatus,
            'user_bank' => $userBank,
            'orders' => $orders,
            'totals' => [
                'cost' => $totalCost,
                'additional_cost' => $totalAdditionalCost,
                'ppn' => $totalPpn,
                'pph' => $totalPph,
                'claim' => $totalClaim,
                'grand_total' => $totalGrandTotal,
                'payment' => $totalPaid,
                'remaining' => max(0, $totalGrandTotal - $totalPaid),
            ],
            'histories' => $histories,
        ];
    }

    /**
     * Distribusi nominal pajak proporsional terhadap DPP tiap order.
     * Menggunakan metode largest remainder agar jumlah seluruh porsi
     * PERSIS sama dengan nominal pajak yang diinput (tanpa selisih pembulatan).
     *
     * @param  float|int  $amount Nominal yang akan didistribusikan
     * @param  array  $weights Basis pembobotan per kunci
     * @param  float|int  $totalWeight Total bobot
     * @return array Porsi (integer rupiah) per kunci
     */
    private function distributeProportionally($amount, array $weights, $totalWeight): array
    {
        $amount = max(0, (int) round((float) $amount));
        $shares = [];

        if ($amount <= 0) {
            foreach (array_keys($weights) as $key) {
                $shares[$key] = 0;
            }

            return $shares;
        }

        // Fallback: bila total bobot 0 (semua DPP nol), bagi rata ke semua order
        // agar nominal pajak tidak hilang.
        $effectiveTotal = ((float) $totalWeight) > 0 ? (float) $totalWeight : (float) max(1, count($weights));

        $floors = [];
        $remainders = [];
        $sumFloors = 0;

        foreach ($weights as $key => $weight) {
            $effectiveWeight = ((float) $totalWeight) > 0 ? (float) $weight : 1.0;
            $exact = $amount * $effectiveWeight / $effectiveTotal;
            $floor = (int) floor($exact);

            $floors[$key] = $floor;
            $remainders[$key] = $exact - $floor;
            $sumFloors += $floor;
        }

        // Sisa rupiah akibat pembulatan ke bawah dibagikan ke order
        // dengan sisa pecahan terbesar (largest remainder)
        $leftover = $amount - $sumFloors;
        if ($leftover > 0) {
            arsort($remainders);
            foreach (array_keys($remainders) as $key) {
                if ($leftover <= 0) {
                    break;
                }

                $floors[$key] += 1;
                $leftover -= 1;
            }
        }

        foreach ($floors as $key => $share) {
            $shares[$key] = max(0, $share);
        }

        return $shares;
    }
}
