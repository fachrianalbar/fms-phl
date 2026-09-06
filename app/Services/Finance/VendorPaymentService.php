<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Models\Bank\UserBank;
use App\Models\Finance\VendorPayment;
use App\Models\Finance\VendorPaymentBatch;
use App\Models\Finance\VendorPaymentHistory;
use App\Models\LiveMutation;
use App\Models\Mutation;
use App\Models\Operational\Order;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\DB;

class VendorPaymentService
{
    use LogActivity;

    protected $service;

    protected $order;

    protected $userBank;

    protected $mutation;

    protected $paymentHistory;

    public function __construct(VendorPayment $vendorPayment, Order $order, UserBank $userBank, Mutation $mutation, VendorPaymentHistory $paymentHistory)
    {
        $this->service = $vendorPayment;
        $this->order = $order;
        $this->userBank = $userBank;
        $this->mutation = $mutation;
        $this->paymentHistory = $paymentHistory;
    }

    public function findAll()
    {
        return $this->order->whereHas('fleet.company', function ($q) {
            $q->whereRaw('LOWER(type) = ?', ['external']);
        })
            ->with(['fleet', 'fleet.company', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'vendorPayments'])
            ->orderBy('orderDate', 'asc')
            ->get();
    }

    /**
     * Order fleet external yang belum punya nota (menunggu dibuat invoice/nota).
     * Termasuk order yang punya vendor_payment lama tanpa nota_number.
     */
    public function findWaitingOrders()
    {
        return $this->order->whereHas('fleet.company', function ($q) {
            $q->whereRaw('LOWER(type) = ?', ['external']);
        })
            ->with(['fleet', 'fleet.company', 'customer', 'customer.company', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'orderStatus', 'vendorPayments'])
            ->where(function ($q) {
                $q->doesntHave('vendorPayments')
                    ->orWhereHas('vendorPayments', function ($q2) {
                        $q2->whereNull('nota_number');
                    });
            })
            ->orderBy('orderDate', 'asc')
            ->get();
    }

    /**
     * Grup vendor_payment per nomor nota (1 nota = 1 invoice ke vendor).
     * Return koleksi objek nota dengan agregat tagihan/terbayar/sisa & status.
     */
    public function findNotaGroups()
    {
        $vendorPayments = $this->service->with([
            'order.fleet',
            'order.fleet.company',
            'order.customer',
            'order.customer.company',
            'order.driver',
            'order.route',
            'order.route.originLocation',
            'order.route.destinationLocation',
            'paymentHistory',
        ])
            ->whereNotNull('nota_number')
            ->get();

        return $vendorPayments->groupBy('nota_number')->map(function ($group, $notaNumber) {
            $orders = $group->pluck('order')->filter();
            $firstPayment = $group->first();
            $firstOrder = $orders->first();

            $totalAmount = (float) $group->sum('amount');
            $totalPaid = (float) $group->sum('paid_amount');
            $totalRemaining = (float) $group->sum('remaining_amount');

            // PPN/PPh disimpan di semua baris nota dengan nilai sama →
            // ambil MAX agar tidak terhitung ganda saat agregasi
            $notaPpn = (float) $group->max('ppn_amount');
            $notaPph = (float) $group->max('pph_amount');
            $notaPpnRate = (float) $group->max('ppn_rate');
            $notaPphRate = (float) $group->max('pph_rate');
            $latestBatchCode = $group
                ->flatMap(fn ($payment) => $payment->paymentHistory)
                ->pluck('batch_code')
                ->filter()
                ->sortDesc(SORT_STRING)
                ->first();

            $status = 'pending';
            if ($group->every(fn ($vp) => $vp->payment_status === 'paid')) {
                $status = 'paid';
            } elseif ($totalPaid > 0) {
                $status = 'partial';
            }

            return (object) [
                'nota_number' => $notaNumber,
                'fleet_company_name' => $firstOrder?->fleet?->company?->name,
                'fleetCompanyCode' => $firstOrder?->fleet?->fleetCompanyCode,
                'order_format' => strtoupper(trim((string) ($firstOrder?->customer?->company?->format ?? ''))),
                'order_count' => $group->count(),
                'orders' => $orders,
                'order_codes' => $group->pluck('orderCode')->values(),
                'plate_numbers' => $orders->map(fn ($o) => $o?->fleet?->plateNumber)->filter()->values(),
                // Format ISO agar sorting di server (yajra) benar secara
                // kronologis; tampilan diformat ulang di sisi JavaScript.
                'nota_date' => optional($group->min('created_at'))->format('Y-m-d\TH:i:s'),
                'date' => $firstPayment?->date,
                'amount' => $totalAmount,
                'paid_amount' => $totalPaid,
                'remaining_amount' => $totalRemaining,
                'ppn_amount' => $notaPpn,
                'pph_amount' => $notaPph,
                'ppn_rate' => $notaPpnRate,
                'pph_rate' => $notaPphRate,
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
     * Daftar seluruh transaksi pembayaran vendor (1 baris = 1 pembayaran/DP/cicilan).
     */
    public function findPayments()
    {
        return $this->paymentHistory->with([
            'userBank.bank',
            'vendorPayment.order.fleet.company',
            'vendorPayment.order.driver',
            'vendorPayment.order.customer',
        ])
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Statistik untuk halaman Invoice Belum Lunas.
     */
    /**
     * Statistik untuk halaman Order Menunggu Nota (order belum punya nota).
     */
    public function statsWaiting()
    {
        $waiting = $this->findWaitingOrders();

        $totalBilling = (float) $waiting->sum(function ($order) {
            $vp = $order->vendorPayments->first();

            return $vp ? (float) $vp->amount : (float) ($order->vendorPrice ?? 0);
        });

        $vendorCount = $waiting->map(function ($order) {
            return $order->fleet->fleetCompanyCode ?? null;
        })->filter()->unique()->count();

        return [
            'waitingCount' => $waiting->count(),
            'totalBilling' => $totalBilling,
            'vendorCount' => $vendorCount,
        ];
    }

    /**
     * Statistik untuk halaman Invoice Belum Lunas (nota pending/partial).
     */
    public function statsUnpaid()
    {
        $unpaidNotas = $this->findUnpaidNotas();

        $notaBilling = (float) $unpaidNotas->sum('amount');
        $notaPaid = (float) $unpaidNotas->sum('paid_amount');
        $notaRemaining = (float) $unpaidNotas->sum('remaining_amount');

        $partialCount = $unpaidNotas->where('payment_status', 'partial')->count();
        $pendingCount = $unpaidNotas->where('payment_status', 'pending')->count();

        return [
            'notaCount' => $unpaidNotas->count(),
            'orderCount' => (int) $unpaidNotas->sum('order_count'),
            'totalCount' => $unpaidNotas->count(),
            'partialCount' => $partialCount,
            'pendingCount' => $pendingCount,
            'totalBilling' => $notaBilling,
            'totalPaid' => $notaPaid,
            'totalRemaining' => $notaRemaining,
        ];
    }

    /**
     * Statistik untuk halaman Invoice Lunas.
     */
    public function statsPaid()
    {
        $paidNotas = $this->findPaidNotas();

        return [
            'notaCount' => $paidNotas->count(),
            'orderCount' => (int) $paidNotas->sum('order_count'),
            'totalPaid' => (float) $paidNotas->sum('paid_amount'),
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
            'notaCount' => $payments->pluck('vendorPayment.nota_number')->filter()->unique()->count(),
            'vendorCount' => $payments->pluck('vendorPayment.order.fleet.company.name')->filter()->unique()->count(),
        ];
    }

    public function store($request, $title)
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
        $existingBatch = VendorPaymentBatch::where('request_key', $requestKey)->first();

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

        $existingBatch = VendorPaymentBatch::where('request_key', $requestKey)
            ->lockForUpdate()
            ->first();

        if ($existingBatch) {
            return $this->resolveExistingBatch($existingBatch, $payloadHash);
        }

        $currentBalance = (int) round((float) $liveMutation->balance);
        if ($currentBalance < $totalPaymentAmount) {
            throw new \DomainException('Saldo sumber dana tidak mencukupi untuk pembayaran ini.', 422);
        }

        $vendorPayments = $this->service->newQuery()
            ->with(['order.fleet.company'])
            ->whereIn('nota_number', $notaNumbers)
            ->orderBy('nota_number')
            ->orderBy('orderCode')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $foundNotas = $vendorPayments->pluck('nota_number')->unique()->sort()->values();
        if ($foundNotas->count() !== count($notaNumbers) || $foundNotas->all() !== $notaNumbers) {
            throw new \DomainException('Satu atau beberapa nota tidak ditemukan.', 422);
        }

        foreach ($vendorPayments as $vendorPayment) {
            $companyType = strtolower(trim((string) ($vendorPayment->order?->fleet?->company?->type ?? '')));

            if (! $vendorPayment->order || $companyType !== 'external') {
                throw new \DomainException('Nota hanya dapat dibayar untuk order vendor eksternal yang valid.', 422);
            }
        }

        $batchCode = GenerateCode::generateUniqueCode('FVB', 'vendor_payment_batch');
        $fullyPaidCount = 0;
        $partialCount = 0;
        $processedOrderCount = 0;
        $allocatedTotal = 0;

        foreach ($payments as $payment) {
            $notaPayments = $vendorPayments->where('nota_number', $payment['nota_number']);
            $remainingByOrder = [];

            foreach ($notaPayments as $vendorPayment) {
                $billingAmount = (int) round((float) $vendorPayment->amount);
                $paidAmount = (int) round((float) $vendorPayment->paid_amount);
                $remainingByOrder[$vendorPayment->id] = max(0, $billingAmount - $paidAmount);
            }

            $currentRemaining = array_sum($remainingByOrder);
            $isFullyPaid = $notaPayments->every(fn ($vendorPayment) => $vendorPayment->payment_status === 'paid');

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
                throw new \LogicException('Vendor payment allocation total is inconsistent.');
            }

            foreach ($notaPayments as $vendorPayment) {
                $paymentAmount = (int) ($orderAllocations[$vendorPayment->id] ?? 0);
                if ($paymentAmount < 1) {
                    continue;
                }

                $billingAmount = (int) round((float) $vendorPayment->amount);
                $paidAmount = (int) round((float) $vendorPayment->paid_amount);
                $newPaidAmount = $paidAmount + $paymentAmount;
                $newRemainingAmount = max(0, $billingAmount - $newPaidAmount);
                $paymentStatus = $newRemainingAmount === 0 ? 'paid' : 'partial';

                $vendorPayment->update([
                    'date' => $request->date,
                    'code' => $batchCode,
                    'user_bank_code' => $userBank->code,
                    'description' => $request->description,
                    'paid_amount' => $newPaidAmount,
                    'remaining_amount' => $newRemainingAmount,
                    'payment_status' => $paymentStatus,
                ]);

                $this->paymentHistory->create([
                    'vendor_payment_id' => $vendorPayment->id,
                    'batch_code' => $batchCode,
                    'amount' => $paymentAmount,
                    'payment_date' => $request->date,
                    'user_bank_code' => $userBank->code,
                    'description' => $request->description,
                ]);

                $this->mutation->create([
                    'code' => GenerateCode::generateUniqueCode('FMT', 'mutation'),
                    'userBankCode' => $userBank->code,
                    'nominal' => $paymentAmount,
                    'type' => 'Out',
                    'date' => $request->date,
                    'description' => 'Vendor Payment Batch ' . $batchCode . ' for Order ' . $vendorPayment->order->code . ' with amount ' . number_format($paymentAmount, 0, '.', ','),
                    'transactionCode' => $batchCode,
                    'transactionTypeCode' => 'FTT251208001130',
                ]);

                if ($paymentStatus === 'paid') {
                    $vendorPayment->order->update(['status' => 6]);
                }

                $this->logActivity($title, $vendorPayment, 'Update');
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
            throw new \LogicException('Vendor payment batch allocation total is inconsistent.');
        }

        $persistedTotal = (int) round((float) $this->paymentHistory
            ->newQuery()
            ->where('batch_code', $batchCode)
            ->sum('amount'));

        if ($persistedTotal !== $totalPaymentAmount) {
            throw new \LogicException('Persisted vendor payment allocation total is inconsistent.');
        }

        $currentCredit = (int) round((float) $liveMutation->credit);
        $currentDebit = (int) round((float) $liveMutation->debit);
        if ($currentCredit + $totalPaymentAmount > 2147483647) {
            throw new \DomainException('Akumulasi pengeluaran rekening melewati kapasitas ledger.', 422);
        }

        $liveMutation->credit = $currentCredit + $totalPaymentAmount;
        $liveMutation->balance = $currentDebit - $liveMutation->credit;
        $liveMutation->save();

        $batch = VendorPaymentBatch::create([
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
        $batch = VendorPaymentBatch::where('request_key', trim((string) $request->requestKey))->first();

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

    private function resolveExistingBatch(VendorPaymentBatch $batch, string $payloadHash): array
    {
        if (! hash_equals((string) $batch->payload_hash, $payloadHash)) {
            throw new \DomainException('Request key sudah digunakan untuk detail pembayaran yang berbeda.', 409);
        }

        if ($batch->status !== 'active') {
            throw new \DomainException('Pembayaran dengan request key ini sudah dibatalkan dan tidak dapat dibuat ulang.', 409);
        }

        return $this->buildBatchResult($batch, true);
    }

    private function buildBatchResult(VendorPaymentBatch $batch, bool $idempotent): array
    {
        $histories = $this->paymentHistory->newQuery()
            ->with('vendorPayment')
            ->where('batch_code', $batch->code)
            ->orderBy('vendor_payment_id')
            ->get();

        $allocations = $histories
            ->groupBy(fn ($history) => (string) ($history->vendorPayment?->nota_number ?? ''))
            ->filter(fn ($group, $notaNumber) => $notaNumber !== '')
            ->map(function ($group, $notaNumber) {
                return [
                    'nota_number' => $notaNumber,
                    'payment_amount' => (int) round((float) $group->sum('amount')),
                    'order_count' => $group->count(),
                    'orders' => $group->map(function ($history) {
                        return [
                            'order_code' => $history->vendorPayment?->orderCode,
                            'payment_amount' => (int) round((float) $history->amount),
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
        $batchSnapshot = VendorPaymentBatch::where('code', $batchCode)->first();

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
            throw new \DomainException('Data pembayaran vendor tidak ditemukan.', 422);
        }

        $initialHistories = $this->paymentHistory->newQuery()
            ->with('vendorPayment:id,nota_number')
            ->where('batch_code', $batchCode)
            ->get();
        $batchNotaNumbers = $initialHistories
            ->pluck('vendorPayment.nota_number')
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
            ->groupBy('user_bank_code')
            ->map(fn ($group) => (int) round((float) $group->sum('amount')))
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

            if ((int) round((float) $liveMutation->credit) < $refundAmount) {
                throw new \DomainException('Akumulasi pengeluaran rekening tidak konsisten dengan nominal batch. Pembatalan dihentikan demi keamanan.', 422);
            }

            $liveMutations->put($bankCode, $liveMutation);
        }

        $batch = VendorPaymentBatch::where('code', $batchCode)
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

        $allPaymentIds = $paymentsInBatch->pluck('id')->sort()->values();
        $allHistories = $this->paymentHistory->newQuery()
            ->whereIn('vendor_payment_id', $allPaymentIds)
            ->orderBy('vendor_payment_id')
            ->orderBy('batch_code')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        $histories = $allHistories->where('batch_code', $batchCode)->values();
        $lockedRefundsByBank = $histories
            ->groupBy('user_bank_code')
            ->map(fn ($group) => (int) round((float) $group->sum('amount')))
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
            $notaPaymentIds = $paymentsInBatch
                ->where('nota_number', $notaNumber)
                ->pluck('id');
            $latestBatchCode = $allHistories
                ->whereIn('vendor_payment_id', $notaPaymentIds)
                ->pluck('batch_code')
                ->filter()
                ->max();

            if ($latestBatchCode !== $batchCode) {
                throw new \DomainException('Salah satu nota dalam batch memiliki pembayaran lebih baru. Batalkan batch terbaru terlebih dahulu.', 409);
            }
        }

        if ($histories->pluck('vendor_payment_id')->diff($allPaymentIds)->isNotEmpty()) {
            throw new \DomainException('Sebagian data nota pada batch tidak ditemukan.', 422);
        }

        $mutations = $this->mutation->newQuery()
            ->where('transactionCode', $batchCode)
            ->orderBy('userBankCode')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        $mutationRefundsByBank = $mutations
            ->where('type', 'Out')
            ->groupBy('userBankCode')
            ->map(fn ($group) => (int) round((float) $group->sum('nominal')))
            ->sortKeys();
        $refundTotal = (int) $refundsByBank->sum();

        if ($mutations->count() !== $histories->count()
            || $mutations->contains(fn ($mutation) => $mutation->type !== 'Out')
            || $mutationRefundsByBank->all() !== $refundsByBank->all()) {
            throw new \DomainException('Nilai mutasi rekening tidak konsisten dengan riwayat pembayaran. Pembatalan dihentikan demi keamanan.', 422);
        }

        foreach ($refundsByBank as $bankCode => $refundAmount) {
            $liveMutation = $liveMutations->get($bankCode);
            $liveMutation->credit = (int) round((float) $liveMutation->credit) - $refundAmount;
            $liveMutation->balance = (int) round((float) $liveMutation->debit) - $liveMutation->credit;
            $liveMutation->save();
        }

        $this->mutation->newQuery()
            ->where('transactionCode', $batchCode)
            ->forceDelete();
        $this->paymentHistory->newQuery()
            ->where('batch_code', $batchCode)
            ->forceDelete();

        foreach ($paymentsInBatch as $payment) {
            $remainingHistories = $allHistories
                ->where('vendor_payment_id', $payment->id)
                ->where('batch_code', '!=', $batchCode)
                ->sortByDesc('batch_code')
                ->values();
            $paidAmount = (int) round((float) $remainingHistories->sum('amount'));
            $billingAmount = (int) round((float) $payment->amount);
            $remainingAmount = max(0, $billingAmount - $paidAmount);
            $lastHistory = $remainingHistories->first();
            $paymentStatus = $remainingAmount === 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');

            $payment->update([
                'date' => $lastHistory?->payment_date,
                'code' => $lastHistory?->batch_code,
                'user_bank_code' => $lastHistory?->user_bank_code,
                'description' => $lastHistory?->description,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'payment_status' => $paymentStatus,
            ]);

            if ($payment->order) {
                $isInvoiced = \App\Models\Finance\InvoiceDetail::where('orderCode', $payment->orderCode)
                    ->whereNull('deleted_at')
                    ->exists();
                $payment->order->update([
                    'status' => $paymentStatus === 'paid' ? 6 : ($isInvoiced ? 5 : 4),
                ]);
            }

            $this->logActivity($title, $payment, 'Cancel Payment Batch ' . $batchCode);
        }

        $batch->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return [
            'batch_code' => $batchCode,
            'payment_amount' => $refundTotal,
            'order_count' => $histories->pluck('vendor_payment_id')->unique()->count(),
            'nota_count' => $batchNotaNumbers->count(),
        ];
    }

    /**
     * Generate nomor nota berformat PREFIX/SEQUENCE/YEAR.
     * SEQUENCE = urutan yang reset setiap tahun baru.
     */
    public function generateNotaNumber($prefix)
    {
        $prefix = strtoupper(trim((string) $prefix));
        $year = (int) now()->format('Y');
        $sequence = DB::table('vendor_nota_sequence')
            ->where('prefix', $prefix)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            throw new \DomainException('Konfigurasi urutan nomor nota tidak ditemukan.', 422);
        }

        $nextSequence = (int) $sequence->year === $year
            ? (int) $sequence->last_sequence + 1
            : 1;

        DB::table('vendor_nota_sequence')
            ->where('prefix', $prefix)
            ->update([
                'year' => $year,
                'last_sequence' => $nextSequence,
                'updated_at' => now(),
            ]);

        return $prefix . '/' . str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT) . '/' . $year;
    }

    /**
     * Assign nomor nota ke beberapa order sekaligus.
     *
     * PPN & PPh diinput sebagai persentase pada level nota. Nominal pajak
     * dihitung dari total DPP, lalu didistribusikan proporsional ke tiap order
     * agar seluruh alur pembayaran tetap konsisten.
     *
     * @param array $orderCodes
     * @param string $userBankCode
     * @param string $title
     * @param float|int $ppnRate Persentase PPN (>= 0)
     * @param float|int $pphRate Persentase PPh (>= 0)
     * @return string Nomor nota yang dihasilkan
     * @throws \Exception
     */
    public function assignNota(array $orderCodes, $userBankCode, $title, $ppnRate = 0, $pphRate = 0)
    {
        $orderCodes = array_values(array_unique(array_filter($orderCodes)));

        if (empty($orderCodes)) {
            throw new \DomainException('Pilih minimal satu order untuk di-nota-kan.', 422);
        }

        $ppnRate = max(0, (float) $ppnRate);
        $pphRate = max(0, (float) $pphRate);
        $userBank = $this->userBank->where('code', $userBankCode)->first();

        if (! $userBank || (int) $userBank->type !== 2) {
            throw new \DomainException('Rekening sumber dana perusahaan tidak valid.', 422);
        }

        // Ambil semua order terpilih dengan relasi customer, company, dan fleet
        $orders = $this->order->with(['customer.company', 'fleet.company'])
            ->whereIn('code', $orderCodes)
            ->orderBy('code')
            ->lockForUpdate()
            ->get();
        if ($orders->count() !== count($orderCodes)) {
            throw new \DomainException('Beberapa order tidak ditemukan.', 422);
        }

        // Validasi 1: Perusahaan kendaraan (fleet company) yang berbeda tidak boleh dalam satu nota
        $nonVendorOrders = $orders->filter(function ($order) {
            return strtolower(trim((string) ($order->fleet->company->type ?? ''))) !== 'external';
        });
        if ($nonVendorOrders->isNotEmpty()) {
            throw new \DomainException('Nota hanya dapat dibuat untuk order vendor eksternal.', 422);
        }

        $fleetCompanyCodes = $orders->map(function ($order) {
            return $order->fleet->fleetCompanyCode ?? null;
        })->filter()->unique();
        if ($fleetCompanyCodes->count() > 1) {
            throw new \DomainException('Gagal: Order yang dipilih memiliki perusahaan kendaraan yang berbeda. Satu nota hanya diperbolehkan untuk perusahaan kendaraan yang sama.', 422);
        }

        // Validasi 3: Format Perusahaan (Pribadi, PHL, WTMS) yang berbeda tidak boleh dalam satu nota
        $companyFormats = $orders->map(function ($order) {
            return strtoupper(trim((string) ($order->customer->company->format ?? '')));
        })->filter()->unique();
        if ($companyFormats->count() > 1) {
            throw new \DomainException('Gagal: Order yang dipilih memiliki format perusahaan yang berbeda (' . $companyFormats->implode(', ') . '). Semua order dalam satu nota harus memiliki format perusahaan yang sama.', 422);
        }

        // Cari vendor payment yang sudah ada untuk order-order ini
        $vendorPayments = $this->service
            ->whereIn('orderCode', $orderCodes)
            ->orderBy('orderCode')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        // Validasi: tidak boleh ada order yang sudah memiliki nota
        $alreadyNota = $vendorPayments->whereNotNull('nota_number');
        if ($alreadyNota->isNotEmpty()) {
            throw new \DomainException('Order sudah memiliki nota: ' . $alreadyNota->pluck('orderCode')->implode(', '), 409);
        }

        // Ambil format perusahaan dari order pertama
        $firstOrder = $orders->first();
        $companyFormat = strtoupper(trim((string) ($firstOrder->customer->company->format ?? '')));

        // Map format ke prefix nota
        if ($companyFormat === 'P') {
            $prefix = 'P';
        } elseif ($companyFormat === 'WTMS' || $companyFormat === 'WT') {
            $prefix = 'WTMS';
        } else {
            $prefix = 'PHL';
        }

        $notaNumber = $this->generateNotaNumber($prefix);

        // DPP tiap order = tagihan saat ini (vendorPrice / amount lama, dipakai sebagai basis distribusi pajak)
        $dppByOrder = [];
        $totalDpp = 0;
        foreach ($orderCodes as $orderCode) {
            $order = $orders->firstWhere('code', $orderCode);
            $vendorPayment = $vendorPayments->firstWhere('orderCode', $orderCode);
            $dpp = (int) round((float) ($vendorPayment->amount ?? $order->vendorPrice ?? 0));

            if ($dpp < 0) {
                throw new \DomainException('Nilai DPP order tidak valid.', 422);
            }

            $dppByOrder[$orderCode] = $dpp;
            $totalDpp += $dpp;
        }

        // Nominal pajak dihitung dari total DPP berdasarkan rate yang diinput.
        $ppnAmount = (int) round($totalDpp * $ppnRate / 100);
        $pphAmount = (int) round($totalDpp * $pphRate / 100);

        // Validasi: total bayar (DPP + PPN − PPh) tidak boleh negatif
        $grandTotal = $totalDpp + $ppnAmount - $pphAmount;
        if ($grandTotal < 0) {
            throw new \DomainException('Total bayar (DPP + PPN − PPh) tidak boleh negatif. Periksa kembali persentase PPh yang diinput.', 422);
        }

        // Distribusi nominal pajak proporsional ke tiap order (largest remainder).
        $ppnShares = $this->distributeProportionally($ppnAmount, $dppByOrder, $totalDpp);
        $pphShares = $this->distributeProportionally($pphAmount, $dppByOrder, $totalDpp);

        $logPayment = null;

        foreach ($orderCodes as $orderCode) {
            $order = $orders->firstWhere('code', $orderCode);
            $vendorPayment = $vendorPayments->firstWhere('orderCode', $orderCode);

            // Amount baru = DPP + porsi PPN − porsi PPh (integer rupiah)
            $newAmount = $dppByOrder[$orderCode] + $ppnShares[$orderCode] - $pphShares[$orderCode];

            if ($newAmount < 0 || $newAmount > 2147483647) {
                throw new \DomainException('Total nota berada di luar batas nominal yang didukung.', 422);
            }

            if ($vendorPayment) {
                // Update yang sudah ada (pertahankan riwayat pembayaran)
                $paidAmount = (int) round((float) ($vendorPayment->paid_amount ?? 0));
                if ($paidAmount > $newAmount) {
                    throw new \DomainException('Total nota baru lebih kecil daripada pembayaran yang sudah tercatat pada order ' . $orderCode . '.', 422);
                }

                $newPaidAmount = $paidAmount;
                $newRemainingAmount = max(0, $newAmount - $newPaidAmount);
                $newStatus = $newRemainingAmount === 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'pending');

                $vendorPayment->update([
                    'nota_number' => $notaNumber,
                    'user_bank_code' => $userBankCode,
                    'amount' => $newAmount,
                    'remaining_amount' => $newRemainingAmount,
                    'payment_status' => $newStatus,
                    'ppn_amount' => $ppnAmount,
                    'ppn_rate' => $ppnRate,
                    'pph_amount' => $pphAmount,
                    'pph_rate' => $pphRate,
                ]);

                if ($newStatus === 'paid') {
                    $order->update(['status' => 6]);
                }
            } else {
                // Buat baru jika belum ada
                $vendorPayment = $this->service->create([
                    'date' => now()->format('Y-m-d'),
                    'amount' => $newAmount,
                    'paid_amount' => 0,
                    'remaining_amount' => $newAmount,
                    'payment_status' => 'pending',
                    'orderCode' => $orderCode,
                    'nota_number' => $notaNumber,
                    'user_bank_code' => $userBankCode,
                    'ppn_amount' => $ppnAmount,
                    'ppn_rate' => $ppnRate,
                    'pph_amount' => $pphAmount,
                    'pph_rate' => $pphRate,
                ]);
            }

            if (!$logPayment) {
                $logPayment = $vendorPayment;
            }
        }

        // Log activity
        if ($logPayment) {
            $this->logActivity($title, $logPayment, 'Generate Nota ' . $notaNumber);
        }

        return $notaNumber;
    }

    /**
     * Distribusi nominal pajak proporsional terhadap DPP tiap order.
     * Menggunakan metode largest remainder agar jumlah seluruh porsi
     * PERSIS sama dengan nominal pajak yang diinput (tanpa selisih pembulatan).
     *
     * @param float|int $amount Nominal yang akan didistribusikan
     * @param array $weights Basis pembobotan per orderCode
     * @param float|int $totalWeight Total bobot
     * @return array Porsi (integer rupiah) per orderCode
     */
    private function distributeProportionally($amount, array $weights, $totalWeight): array
    {
        $amount = max(0, (int) round((float) $amount));
        $shares = [];

        if ($amount <= 0) {
            foreach (array_keys($weights) as $orderCode) {
                $shares[$orderCode] = 0;
            }

            return $shares;
        }

        // Fallback: bila total bobot 0 (semua DPP nol), bagi rata ke semua order
        // agar nominal pajak tidak hilang.
        $effectiveTotal = ((float) $totalWeight) > 0 ? (float) $totalWeight : (float) max(1, count($weights));

        $floors = [];
        $remainders = [];
        $sumFloors = 0;

        foreach ($weights as $orderCode => $weight) {
            $effectiveWeight = ((float) $totalWeight) > 0 ? (float) $weight : 1.0;
            $exact = $amount * $effectiveWeight / $effectiveTotal;
            $floor = (int) floor($exact);

            $floors[$orderCode] = $floor;
            $remainders[$orderCode] = $exact - $floor;
            $sumFloors += $floor;
        }

        // Sisa rupiah akibat pembulatan ke bawah dibagikan ke order
        // dengan sisa pecahan terbesar (largest remainder)
        $leftover = $amount - $sumFloors;
        if ($leftover > 0) {
            arsort($remainders);
            foreach (array_keys($remainders) as $orderCode) {
                if ($leftover <= 0) {
                    break;
                }

                $floors[$orderCode] += 1;
                $leftover -= 1;
            }
        }

        foreach ($floors as $orderCode => $share) {
            $shares[$orderCode] = max(0, $share);
        }

        return $shares;
    }

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
        $vendorPayment = $paymentsInNota->firstWhere('orderCode', $orderCode);

        if (! $vendorPayment || $paymentsInNota->isEmpty()) {
            throw new \DomainException('Data nota telah berubah. Muat ulang halaman.', 409);
        }

        $hasHistory = $this->paymentHistory->newQuery()
            ->whereIn('vendor_payment_id', $paymentsInNota->pluck('id'))
            ->lockForUpdate()
            ->exists();
        $alreadyPaid = $paymentsInNota->contains(function ($payment) {
            return (int) round((float) $payment->paid_amount) > 0
                || $payment->payment_status !== 'pending';
        });

        if ($hasHistory || $alreadyPaid) {
            throw new \DomainException(
                'Nota ' . ($vendorPayment->nota_number ?: '-') . ' sudah memiliki pembayaran. Batalkan batch pembayaran terlebih dahulu.',
                409
            );
        }

        foreach ($paymentsInNota as $payment) {
            $payment->forceDelete();
        }

        $this->logActivity(
            $title,
            $vendorPayment,
            $vendorPayment->nota_number
                ? 'Cancel Nota ' . $vendorPayment->nota_number . ' (All associated orders reset)'
                : 'Cancel Unassigned Payment Record'
        );
    }
}

