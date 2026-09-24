<?php

namespace App\Services\Purchasing;

use App\Helpers\GenerateCode;
use App\Models\Bank\UserBank;
use App\Models\LiveMutation;
use App\Models\Mutation;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchasePaymentHistory;
use App\Models\Purchasing\SupplierPaymentBatch;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\DB;

class PurchasePaymentService
{
    use LogActivity;

    /** Kode tipe mutasi "Purchase" pada tabel transaction_type. */
    public const TRANSACTION_TYPE = 'FTT250306114138';

    protected Purchase $service;

    protected LiveMutation $liveMutation;

    protected Mutation $mutation;

    protected PurchasePaymentHistory $history;

    protected SupplierPaymentBatch $batch;

    public function __construct(
        Purchase $purchase,
        LiveMutation $liveMutation,
        Mutation $mutation,
        PurchasePaymentHistory $history,
        SupplierPaymentBatch $batch
    ) {
        $this->service = $purchase;
        $this->liveMutation = $liveMutation;
        $this->mutation = $mutation;
        $this->history = $history;
        $this->batch = $batch;
    }

    /* ------------------------------------------------------------------ */
    /* Query                                                              */
    /* ------------------------------------------------------------------ */

    public function getById(string $id)
    {
        return $this->service->newQuery()->where('id', $id)->with([
            'details',
            'details.item',
            'supplier',
            'purchaseStatus',
            'paymentHistories',
        ])->first();
    }

    public function getByCode(string $code)
    {
        return $this->service->newQuery()->where('code', $code)->first();
    }

    public function datatableUnpaid()
    {
        return $this->service->newQuery()->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
            'details',
        ])->where('paymentStatus', '!=', 'Paid')
            ->orderBy('dueDate', 'asc')
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc');
    }

    public function datatablePaid()
    {
        return $this->service->newQuery()->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
            'details',
            'paymentBatch',
        ])->where('paymentStatus', 'Paid')
            ->orderBy('paymentDate', 'desc')
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc');
    }

    /**
     * Nilai tagihan satu pembelian (basis hutang).
     *
     * Kolom `nominal` hanya terisi sebagian (data lama sering NULL), sehingga
     * bila kosong nilainya dihitung dari detail (price * qty) — konsisten
     * dengan cara PurchaseService menyimpan `nominal`.
     */
    public function billingOf(Purchase $purchase): int
    {
        $nominal = (int) round((float) ($purchase->nominal ?? 0));

        if ($nominal > 0) {
            return $nominal;
        }

        $total = 0.0;

        foreach ($purchase->details as $detail) {
            $quantity = (float) ($detail->qty ?: $detail->receivedQty);
            $total += (float) $detail->price * $quantity;
        }

        return (int) round($total);
    }

    public function statsUnpaid(): array
    {
        // Nilai tagihan = nominal bila terisi, jika tidak dihitung dari detail.
        // Dihitung sebagai satu agregat SQL (derived table) agar tidak memuat
        // ribuan model maupun subquery berkorelasi per baris.
        $detailSums = DB::table('purchase_detail')
            ->whereNull('deleted_at')
            ->groupBy('purchaseCode')
            ->selectRaw('purchaseCode, SUM(price * COALESCE(qty, receivedQty)) as detail_sum');

        $row = $this->service->newQuery()
            ->leftJoinSub($detailSums, 'ds', 'ds.purchaseCode', '=', 'purchase.code')
            ->where('purchase.paymentStatus', '!=', 'Paid')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw("SUM(CASE WHEN purchase.paymentStatus = 'Unpaid' THEN 1 ELSE 0 END) as unpaid_count")
            ->selectRaw("SUM(CASE WHEN purchase.paymentStatus = 'Partial' THEN 1 ELSE 0 END) as partial_count")
            ->selectRaw('SUM(COALESCE(NULLIF(purchase.nominal, 0), ds.detail_sum, 0)) as total_billing')
            ->selectRaw('SUM(COALESCE(purchase.paidAmount, 0)) as total_paid')
            ->first();

        $totalBilling = (float) ($row->total_billing ?? 0);
        $totalPaid = (float) ($row->total_paid ?? 0);

        return [
            'totalCount' => (int) ($row->total_count ?? 0),
            'unpaidCount' => (int) ($row->unpaid_count ?? 0),
            'partialCount' => (int) ($row->partial_count ?? 0),
            'totalBilling' => $totalBilling,
            'totalPaid' => $totalPaid,
            'totalRemaining' => max(0, $totalBilling - $totalPaid),
        ];
    }

    public function statsPaid(): array
    {
        $rows = $this->service->newQuery()->where('paymentStatus', 'Paid')->get();

        return [
            'totalCount' => $rows->count(),
            'totalPaid' => $rows->sum(fn ($row) => (float) $row->paidAmount),
        ];
    }

    /**
     * Detail pembelian + riwayat pembayaran (untuk modal detail / ajax).
     */
    public function findPaymentDetail(string $purchaseCode): array
    {
        $purchase = $this->service->newQuery()->with(['supplier', 'details'])->where('code', $purchaseCode)->first();

        if (! $purchase) {
            return [];
        }

        $histories = $this->history->newQuery()
            ->with(['userBank.bank'])
            ->where('purchaseCode', $purchaseCode)
            ->orderBy('paymentDate')
            ->orderBy('created_at')
            ->get();

        $billing = (float) $this->billingOf($purchase);
        $paid = (float) $purchase->paidAmount;

        return [
            'code' => $purchase->code,
            'supplier' => $purchase->supplier->name ?? '-',
            'date' => $purchase->date,
            'due_date' => $purchase->dueDate,
            'billing' => $billing,
            'paid' => $paid,
            'remaining' => max(0, $billing - $paid),
            'payment_status' => $purchase->paymentStatus,
            'histories' => $histories->map(fn ($h) => [
                'batch_code' => $h->batch_code,
                'amount' => (float) $h->amount,
                'payment_date' => $h->paymentDate,
                'bank' => $h->userBank?->bank?->name,
                'description' => $h->description,
            ])->values()->all(),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Pembayaran tunggal (halaman edit lama)                             */
    /* ------------------------------------------------------------------ */

    public function update($request, string $id, string $title): void
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $purchase = $this->service->newQuery()->with('details')->where('id', $id)->lockForUpdate()->first();

        if (! $purchase) {
            throw new \DomainException('Data pembelian tidak ditemukan.', 422);
        }

        $paymentAmount = (float) str_replace(['Rp', '.', ',', ' '], '', (string) $request->nominal);
        $billing = (float) $this->billingOf($purchase);
        $paid = (float) $purchase->paidAmount;
        $remaining = max(0, $billing - $paid);

        if ($paymentAmount <= 0) {
            throw new \DomainException('Nominal pembayaran harus lebih besar dari 0.', 422);
        }

        if ($paymentAmount > $remaining) {
            throw new \DomainException('Nominal pembayaran melebihi sisa hutang (Rp '.number_format($remaining, 0, ',', '.').').', 422);
        }

        $userBank = UserBank::query()->where('code', $request->userBankCode)->first();

        if (! $userBank) {
            throw new \DomainException('Sumber dana wajib dipilih dan valid.', 422);
        }

        $batchCode = GenerateCode::generateUniqueCode('FSP', 'supplier_payment_batch');

        $newPaid = $paid + $paymentAmount;
        $newRemaining = max(0, $billing - $newPaid);
        $paymentStatus = $newRemaining <= 0 ? 'Paid' : 'Partial';

        $this->history->create([
            'purchaseCode' => $purchase->code,
            'batch_code' => $batchCode,
            'amount' => $paymentAmount,
            'paymentDate' => $request->paymentDate,
            'userBankCode' => $userBank->code,
            'description' => $request->description,
        ]);

        $purchase->paidAmount = $newPaid;
        $purchase->paymentStatus = $paymentStatus;
        $purchase->paymentDate = $request->paymentDate;
        $purchase->paymentCode = $batchCode;
        $purchase->userBankCode = $userBank->code;

        if ($paymentStatus === 'Paid') {
            $purchase->status = 3;
        }

        $purchase->save();

        $this->mutation->create([
            'code' => GenerateCode::generateUniqueCode('FMT', 'mutation'),
            'userBankCode' => $userBank->code,
            'nominal' => $paymentAmount,
            'type' => 'Out',
            'date' => $request->paymentDate,
            'description' => 'Pembayaran Pembelian '.$purchase->code.' sebesar '.number_format($paymentAmount, 0, '.', ','),
            'transactionCode' => $batchCode,
            'transactionTypeCode' => self::TRANSACTION_TYPE,
        ]);

        $this->applyBankMovement($userBank->code, $paymentAmount, 'out');

        $this->batch->create([
            'code' => $batchCode,
            'request_key' => null,
            'payload_hash' => null,
            'status' => 'active',
            'payment_date' => $request->paymentDate,
            'user_bank_code' => $userBank->code,
            'amount' => $paymentAmount,
            'purchase_count' => 1,
            'fully_paid_count' => $paymentStatus === 'Paid' ? 1 : 0,
            'partial_count' => $paymentStatus === 'Paid' ? 0 : 1,
            'description' => $request->description,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    /* ------------------------------------------------------------------ */
    /* Pembayaran banyak pembelian sekaligus (batch)                      */
    /* ------------------------------------------------------------------ */

    /**
     * Simpan satu transaksi pembayaran untuk banyak pembelian.
     *
     * Alur: validasi → cek idempotency (request_key) → lock baris pembelian
     * & saldo bank → alokasi per pembelian (history + mutasi) → update saldo
     * → simpan batch. Semua dijalankan dalam satu DB transaction oleh caller.
     */
    public function storeBatch($request, string $title): array
    {
        $requestKey = trim((string) $request->requestKey);

        $payments = collect($request->payments ?? [])->map(function ($payment) {
            if (! is_array($payment)) {
                throw new \DomainException('Data alokasi pembayaran tidak valid.', 422);
            }

            return [
                'purchase_code' => trim((string) ($payment['purchase_code'] ?? '')),
                'amount' => (int) ($payment['amount'] ?? 0),
                'expected_remaining' => (int) ($payment['expected_remaining'] ?? 0),
            ];
        });

        if ($payments->isEmpty() || $payments->contains(fn ($payment) => $payment['purchase_code'] === '')) {
            throw new \DomainException('Pilih minimal satu pembelian yang valid untuk dibayar.', 422);
        }

        if ($payments->pluck('purchase_code')->unique()->count() !== $payments->count()) {
            throw new \DomainException('Data pembelian tidak boleh duplikat.', 422);
        }

        if ($payments->contains(fn ($payment) => $payment['amount'] < 1 || $payment['expected_remaining'] < 1)) {
            throw new \DomainException('Nominal pembayaran dan sisa tagihan harus berupa rupiah positif.', 422);
        }

        $payments = $payments->sortBy('purchase_code', SORT_STRING)->values();
        $purchaseCodes = $payments->pluck('purchase_code')->all();
        $totalPaymentAmount = (int) $payments->sum('amount');

        $payloadHash = $this->paymentPayloadHash($request, $payments);

        $existingBatch = $this->batch->newQuery()->where('request_key', $requestKey)->first();

        if ($existingBatch) {
            return $this->resolveExistingBatch($existingBatch, $payloadHash);
        }

        $userBank = UserBank::query()->where('code', $request->userBankCode)->first();

        if (! $userBank) {
            throw new \DomainException('Sumber dana tidak ditemukan atau tidak valid.', 422);
        }

        $existingBatch = $this->batch->newQuery()->where('request_key', $requestKey)->lockForUpdate()->first();

        if ($existingBatch) {
            return $this->resolveExistingBatch($existingBatch, $payloadHash);
        }

        $purchases = $this->service->newQuery()
            ->with('details')
            ->whereIn('code', $purchaseCodes)
            ->orderBy('code')
            ->lockForUpdate()
            ->get()
            ->keyBy('code');

        $foundCodes = $purchases->keys()->sort()->values();
        $requestedCodes = collect($purchaseCodes)->sort()->values();

        if ($foundCodes->count() !== $requestedCodes->count() || $foundCodes->all() !== $requestedCodes->all()) {
            throw new \DomainException('Satu atau beberapa data pembelian tidak ditemukan.', 422);
        }

        $batchCode = GenerateCode::generateUniqueCode('FSP', 'supplier_payment_batch');
        $fullyPaidCount = 0;
        $partialCount = 0;
        $allocatedTotal = 0;

        foreach ($payments as $payment) {
            /** @var Purchase $purchase */
            $purchase = $purchases->get($payment['purchase_code']);

            $billingAmount = $this->billingOf($purchase);
            $paidAmount = (int) round((float) $purchase->paidAmount);
            $currentRemaining = max(0, $billingAmount - $paidAmount);

            if ($billingAmount < 1) {
                throw new \DomainException('Pembelian '.$purchase->code.' tidak memiliki nilai tagihan.', 422);
            }

            if ($currentRemaining < 1 || $purchase->paymentStatus === 'Paid') {
                throw new \DomainException('Pembelian '.$purchase->code.' sudah lunas.', 409);
            }

            if ($payment['expected_remaining'] !== $currentRemaining) {
                throw new \DomainException('Sisa tagihan salah satu pembelian telah berubah. Muat ulang data sebelum membayar.', 409);
            }

            if ($payment['amount'] > $currentRemaining) {
                throw new \DomainException('Nominal pembayaran melebihi sisa tagihan pembelian '.$purchase->code.'.', 422);
            }

            $newPaidAmount = $paidAmount + $payment['amount'];
            $newRemainingAmount = max(0, $billingAmount - $newPaidAmount);
            $paymentStatus = $newRemainingAmount === 0 ? 'Paid' : 'Partial';

            $purchase->paidAmount = $newPaidAmount;
            $purchase->paymentStatus = $paymentStatus;
            $purchase->paymentDate = $request->date;
            $purchase->paymentCode = $batchCode;
            $purchase->userBankCode = $userBank->code;

            if ($paymentStatus === 'Paid') {
                $purchase->status = 3;
            }

            $purchase->save();

            $this->history->create([
                'purchaseCode' => $purchase->code,
                'batch_code' => $batchCode,
                'amount' => $payment['amount'],
                'paymentDate' => $request->date,
                'userBankCode' => $userBank->code,
                'description' => $request->description,
            ]);

            $this->mutation->create([
                'code' => GenerateCode::generateUniqueCode('FMT', 'mutation'),
                'userBankCode' => $userBank->code,
                'nominal' => $payment['amount'],
                'type' => 'Out',
                'date' => $request->date,
                'description' => 'Pembayaran Pembelian '.$purchase->code.' (Batch '.$batchCode.') sebesar '.number_format($payment['amount'], 0, '.', ','),
                'transactionCode' => $batchCode,
                'transactionTypeCode' => self::TRANSACTION_TYPE,
            ]);

            $this->logActivity($title, $purchase, 'Update');

            if ($paymentStatus === 'Paid') {
                $fullyPaidCount++;
            } else {
                $partialCount++;
            }

            $allocatedTotal += $payment['amount'];
        }

        if ($allocatedTotal !== $totalPaymentAmount) {
            throw new \LogicException('Supplier payment allocation total is inconsistent.');
        }

        $this->applyBankMovement($userBank->code, $totalPaymentAmount, 'out');

        $batch = $this->batch->create([
            'code' => $batchCode,
            'request_key' => $requestKey,
            'payload_hash' => $payloadHash,
            'status' => 'active',
            'payment_date' => $request->date,
            'user_bank_code' => $userBank->code,
            'amount' => $totalPaymentAmount,
            'purchase_count' => count($purchaseCodes),
            'fully_paid_count' => $fullyPaidCount,
            'partial_count' => $partialCount,
            'description' => $request->description,
        ]);

        return $this->buildBatchResult($batch, false);
    }

    public function findBatchResultByRequest($request): ?array
    {
        $payments = collect($request->payments ?? [])->map(fn ($payment) => [
            'purchase_code' => trim((string) ($payment['purchase_code'] ?? '')),
            'amount' => (int) ($payment['amount'] ?? 0),
            'expected_remaining' => (int) ($payment['expected_remaining'] ?? 0),
        ])->sortBy('purchase_code', SORT_STRING)->values();

        $batch = $this->batch->newQuery()->where('request_key', trim((string) $request->requestKey))->first();

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

    private function resolveExistingBatch(SupplierPaymentBatch $batch, string $payloadHash): array
    {
        if (! hash_equals((string) $batch->payload_hash, $payloadHash)) {
            throw new \DomainException('Request key sudah digunakan untuk detail pembayaran yang berbeda.', 409);
        }

        if ($batch->status !== 'active') {
            throw new \DomainException('Pembayaran dengan request key ini sudah dibatalkan dan tidak dapat dibuat ulang.', 409);
        }

        return $this->buildBatchResult($batch, true);
    }

    private function buildBatchResult(SupplierPaymentBatch $batch, bool $idempotent): array
    {
        return [
            'batch_code' => $batch->code,
            'payment_amount' => (int) $batch->amount,
            'purchase_count' => (int) $batch->purchase_count,
            'fully_paid_count' => (int) $batch->fully_paid_count,
            'partial_count' => (int) $batch->partial_count,
            'idempotent' => $idempotent,
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Pembatalan pembayaran (batch)                                      */
    /* ------------------------------------------------------------------ */

    /**
     * Batalkan satu batch pembayaran: kembalikan saldo bank, hapus mutasi,
     * hapus riwayat batch, dan hitung ulang status tiap pembelian.
     */
    public function cancelPayment(string $batchCode, string $title): array
    {
        $batch = $this->batch->newQuery()->where('code', $batchCode)->lockForUpdate()->first();

        if (! $batch) {
            throw new \DomainException('Batch pembayaran tidak ditemukan.', 422);
        }

        if ($batch->status !== 'active') {
            throw new \DomainException('Batch pembayaran ini sudah dibatalkan sebelumnya.', 409);
        }

        $histories = $this->history->newQuery()
            ->where('batch_code', $batchCode)
            ->lockForUpdate()
            ->get();

        if ($histories->isEmpty()) {
            throw new \DomainException('Rincian pembayaran batch tidak ditemukan.', 422);
        }

        $reversedTotal = 0;

        foreach ($histories->groupBy('purchaseCode') as $purchaseCode => $purchaseHistories) {
            foreach ($purchaseHistories as $history) {
                $reversedTotal += (float) $history->amount;
                $history->forceDelete();
            }

            $this->recalculatePurchaseStatus((string) $purchaseCode);
        }

        if ($batch->user_bank_code) {
            $this->applyBankMovement($batch->user_bank_code, $reversedTotal, 'in');
        }

        $this->mutation->newQuery()->where('transactionCode', $batchCode)->forceDelete();

        $batch->status = 'cancelled';
        $batch->cancelled_at = now();
        $batch->save();

        return [
            'batch_code' => $batch->code,
            'reversed_amount' => (int) round($reversedTotal),
        ];
    }

    /**
     * Hitung ulang status pembayaran satu pembelian dari seluruh riwayatnya.
     */
    public function recalculatePurchaseStatus(string $purchaseCode): string
    {
        $purchase = $this->service->newQuery()->with('details')->where('code', $purchaseCode)->first();

        if (! $purchase) {
            return 'Unpaid';
        }

        $billing = (float) $this->billingOf($purchase);
        $paid = (float) $this->history->newQuery()
            ->where('purchaseCode', $purchaseCode)
            ->whereNull('deleted_at')
            ->sum('amount');
        $remaining = max(0, $billing - $paid);

        $paymentStatus = $paid <= 0
            ? 'Unpaid'
            : ($remaining <= 0 ? 'Paid' : 'Partial');

        $lastHistory = $this->history->newQuery()
            ->where('purchaseCode', $purchaseCode)
            ->orderByDesc('paymentDate')
            ->orderByDesc('created_at')
            ->first();

        $purchase->paidAmount = $paid;
        $purchase->paymentStatus = $paymentStatus;
        $purchase->status = $paymentStatus === 'Paid' ? 3 : 0;
        $purchase->paymentDate = $lastHistory?->paymentDate;
        $purchase->paymentCode = $lastHistory?->batch_code;
        $purchase->userBankCode = $lastHistory?->userBankCode;
        $purchase->save();

        return $paymentStatus;
    }

    /* ------------------------------------------------------------------ */
    /* Helper saldo bank                                                  */
    /* ------------------------------------------------------------------ */

    /**
     * Terapkan pergerakan saldo bank pada live_mutation.
     * Konvensi aplikasi: balance = debit − credit.
     *  - 'out' (uang keluar / pembayaran)  → credit bertambah
     *  - 'in'  (uang masuk / pembatalan)   → debit bertambah
     */
    private function applyBankMovement(string $userBankCode, float $amount, string $direction): void
    {
        $liveMutation = $this->liveMutation->newQuery()
            ->where('userBankCode', $userBankCode)
            ->lockForUpdate()
            ->first();

        if (! $liveMutation) {
            throw new \DomainException('Saldo sumber dana tidak tersedia.', 422);
        }

        $amount = (int) round($amount);
        $currentCredit = (float) $liveMutation->credit;
        $currentDebit = (float) $liveMutation->debit;

        if ($direction === 'out') {
            $currentCredit += $amount;
        } else {
            $currentDebit += $amount;
        }

        $liveMutation->credit = $currentCredit;
        $liveMutation->debit = $currentDebit;
        $liveMutation->balance = $currentDebit - $currentCredit;
        $liveMutation->save();
    }
}
