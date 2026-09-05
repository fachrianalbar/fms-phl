<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Bank\UserBank;
use App\Models\Finance\VendorPayment;
use App\Models\Finance\VendorPaymentHistory;
use App\Models\Mutation;
use App\Models\Operational\Order;
use App\Traits\LogActivity;

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
                'nota_date' => $group->min('created_at'),
                'date' => $firstPayment?->date,
                'amount' => $totalAmount,
                'paid_amount' => $totalPaid,
                'remaining_amount' => $totalRemaining,
                'ppn_amount' => $notaPpn,
                'pph_amount' => $notaPph,
                'payment_status' => $status,
                'user_bank_code' => $firstPayment?->user_bank_code,
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
        $orderCodes = collect($request->orderCodes ?? [])->filter()->unique()->values();
        $userBank = $this->userBank->where('code', $request->userBankCode)->first();
 
        if ($orderCodes->isEmpty()) {
            throw new \Exception('Order yang dipilih tidak valid.');
        }
 
        if (! $userBank) {
            throw new \Exception('Sumber dana (bank) tidak ditemukan.');
        }
 
        // Satu kode batch untuk satu kali submit pembayaran multi-order.
        $batchCode = GenerateCode::generateCode('FVB', true);
 
        $processedOrderCodes = [];
        $skippedOrderCodes = [];
 
        // 1. Hitung sisa tagihan saat ini untuk masing-masing order
        $orderRemainingAmounts = [];
        $totalRemaining = 0;
        foreach ($orderCodes as $orderCode) {
            $vp = $this->service->where('orderCode', $orderCode)->first();
            $order = $this->order->where('code', $orderCode)->first();
            if (!$order) {
                throw new \Exception('Order ' . $orderCode . ' tidak ditemukan.');
            }
            $rem = $vp ? (float)$vp->remaining_amount : (float)($order->vendorPrice ?? 0);
            $orderRemainingAmounts[$orderCode] = $rem;
            $totalRemaining += $rem;
        }

        $totalPaymentAmount = $request->filled('paymentAmount') ? (float) $request->paymentAmount : null;
        if ($totalPaymentAmount !== null && $totalPaymentAmount > $totalRemaining) {
            throw new \Exception('Nominal pembayaran tidak boleh melebihi sisa tagihan (Rp ' . number_format($totalRemaining, 0, ',', '.') . ').');
        }

        // 2. Alokasikan pembayaran secara merata di antara order yang belum lunas
        $allocations = [];
        foreach ($orderCodes as $orderCode) {
            $allocations[$orderCode] = 0;
        }

        if ($totalPaymentAmount !== null) {
            $remainingToAllocate = $totalPaymentAmount;
            while ($remainingToAllocate > 0.01) {
                $eligibleOrders = [];
                foreach ($orderCodes as $orderCode) {
                    $rem = $orderRemainingAmounts[$orderCode] - $allocations[$orderCode];
                    if ($rem > 0) {
                        $eligibleOrders[] = $orderCode;
                    }
                }
                
                if (empty($eligibleOrders)) {
                    break;
                }
                
                $share = $remainingToAllocate / count($eligibleOrders);
                $allocatedInThisRound = 0;
                
                foreach ($eligibleOrders as $orderCode) {
                    $rem = $orderRemainingAmounts[$orderCode] - $allocations[$orderCode];
                    $amountToAlloc = min($share, $rem);
                    $allocations[$orderCode] += $amountToAlloc;
                    $allocatedInThisRound += $amountToAlloc;
                }
                
                $remainingToAllocate -= $allocatedInThisRound;
                if ($allocatedInThisRound <= 0) {
                    break;
                }
            }
        } else {
            // Full payment for each order
            foreach ($orderCodes as $orderCode) {
                $allocations[$orderCode] = $orderRemainingAmounts[$orderCode];
            }
        }

        foreach ($orderCodes as $orderCode) {
            $order = $this->order->where('code', $orderCode)->first();
            $paymentAmount = $allocations[$orderCode];

            if ($paymentAmount <= 0) {
                $skippedOrderCodes[] = $orderCode;
                continue;
            }

            // Cek apakah sudah ada vendor payment untuk order ini
            $vendorPayment = $this->service->where('orderCode', $orderCode)->first();
 
            // Tagihan awal dari order
            $billingAmount = $vendorPayment ? (float) ($vendorPayment->amount ?? 0) : (float) ($order->vendorPrice ?? 0);
 
            $created = false;
            if (! $vendorPayment) {
                // Pertama kali: buat vendor payment baru
                // amount = tagihan awal, bukan jumlah pembayaran
                $vendorPayment = $this->service->create([
                    'date' => $request->date,
                    'amount' => $billingAmount,  // Tagihan awal
                    'paid_amount' => 0,
                    'remaining_amount' => $billingAmount,
                    'payment_status' => 'pending',
                    'description' => $request->description,
                    'orderCode' => $orderCode,
                    'code' => $batchCode,
                ]);
                $created = true;
            }
 
            // Update paid amount dan remaining amount
            $newPaidAmount = ((float) ($vendorPayment->paid_amount ?? 0)) + $paymentAmount;
            $newRemainingAmount = max(0, ((float) ($vendorPayment->amount ?? $billingAmount)) - $newPaidAmount);
 
            $paymentStatus = $newRemainingAmount <= 0 ? 'paid' : 'partial';
 
            // Update vendor payment record
            $vendorPayment->update([
                'date' => $request->date,
                'code' => $batchCode,
                'description' => $request->description,
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'payment_status' => $paymentStatus,
            ]);
 
            // Simpan payment history
            $this->paymentHistory->create([
                'vendor_payment_id' => $vendorPayment->id,
                'batch_code' => $batchCode,
                'amount' => $paymentAmount,
                'payment_date' => $request->date,
                'user_bank_code' => $request->userBankCode,
                'description' => $request->description,
            ]);
 
            // Update LiveMutation dengan CREDIT (pengeluaran uang)
            LiveMutationHelper::updateLiveMutation($userBank->code, $paymentAmount, 'credit');
 
            // Create mutation record for accounting
            $this->mutation->create([
                'code' => GenerateCode::generateCode('FMT', true),
                'userBankCode' => $request->userBankCode,
                'nominal' => $paymentAmount,
                'type' => 'Out', // Out untuk pengeluaran
                'date' => $request->date,
                'description' => 'Vendor Payment Batch ' . $batchCode . ' for Order ' . $order->code . ' with amount ' . number_format($paymentAmount, 0, '.', ','),
                'transactionCode' => $batchCode,
                'transactionTypeCode' => 'FTT251208001130', // Vendor Payment transaction type
            ]);
 
            // Update order status jika sudah full bayar
            if ($paymentStatus === 'paid') {
                $order->update([
                    'status' => 6, // Status paid
                ]);
            }
 
            $this->logActivity($title, $vendorPayment, $created ? 'Create' : 'Update');
            $processedOrderCodes[] = $orderCode;
        }
 
        if (empty($processedOrderCodes)) {
            throw new \Exception('Semua order terpilih sudah lunas.');
        }
 
        return [
            'batch_code' => $batchCode,
            'processed_count' => count($processedOrderCodes),
            'skipped_count' => count($skippedOrderCodes),
            'processed_order_codes' => $processedOrderCodes,
            'skipped_order_codes' => $skippedOrderCodes,
        ];
    }

    /**
     * Membatalkan pembayaran vendor (hard delete).
     *
     * @param string $orderCode
     * @param string $title
     * @throws \Exception
     */
    public function cancelPayment($orderCode, $title)
    {
        $vendorPayment = $this->service->where('orderCode', $orderCode)->first();

        if (! $vendorPayment) {
            throw new \Exception('Data pembayaran vendor tidak ditemukan untuk order ini.');
        }

        // Get all vendor payments sharing the same payment batch code (code column)
        // If code is empty, default to only this vendor payment
        $batchCode = $vendorPayment->code;
        if ($batchCode) {
            $paymentsInBatch = $this->service->where('code', $batchCode)->get();
        } else {
            $paymentsInBatch = collect([$vendorPayment]);
        }

        foreach ($paymentsInBatch as $payment) {
            $currentOrderCode = $payment->orderCode;
            $order = $this->order->where('code', $currentOrderCode)->first();

            // 1. Dapatkan riwayat pembayaran untuk mengembalikan saldo bank
            $histories = $payment->paymentHistory;

            foreach ($histories as $history) {
                $amount = (float) $history->amount;
                $bankCode = $history->user_bank_code;

                // Kembalikan saldo LiveMutation (kurangi credit)
                $liveMutation = \App\Models\LiveMutation::where('userBankCode', $bankCode)->first();
                if ($liveMutation) {
                    $liveMutation->credit -= $amount;
                    $liveMutation->balance = $liveMutation->debit - $liveMutation->credit;
                    $liveMutation->save();
                }

                // Hapus Mutation record (hard delete).
                // Gunakan batch_code dari history agar mutasi batch yang tepat yang dihapus
                // (satu nota bisa dibayar beberapa kali: DP lalu pelunasan).
                $mutationBatchCode = $history->batch_code ?: $payment->code;
                \App\Models\Mutation::where('transactionCode', $mutationBatchCode)
                    ->where('userBankCode', $bankCode)
                    ->where('description', 'like', '%' . $currentOrderCode . '%')
                    ->forceDelete();

                // Hapus history record (hard delete)
                $history->forceDelete();
            }

            // 2. Reset or delete VendorPayment record
            if ($payment->nota_number) {
                // If it has a nota_number, we must preserve it!
                // Reset paid_amount, remaining_amount, and payment_status to pending
                $payment->update([
                    'paid_amount' => 0,
                    'remaining_amount' => $payment->amount,
                    'payment_status' => 'pending',
                    'code' => null, // clear the batch payment code since payment is cancelled!
                ]);
            } else {
                // If no nota_number, safe to force delete
                $payment->forceDelete();
            }

            // 3. Kembalikan status Order
            if ($order) {
                $isInvoiced = \App\Models\Finance\InvoiceDetail::where('orderCode', $currentOrderCode)
                    ->whereNull('deleted_at')
                    ->exists();

                $order->update([
                    'status' => $isInvoiced ? 5 : 4,
                ]);
            }

            $this->logActivity($title, $payment, 'Cancel Payment');
        }
    }

    /**
     * Generate nomor nota berformat PREFIX/SEQUENCE/YEAR.
     * SEQUENCE = urutan yang reset setiap tahun baru.
     */
    public function generateNotaNumber($prefix)
    {
        $year = now()->format('Y');
        $pattern = $prefix . '/%/' . $year;

        // Cari nota_number tertinggi dengan prefix dan tahun ini
        $lastNota = $this->service
            ->where('nota_number', 'like', $pattern)
            ->orderByDesc('nota_number')
            ->value('nota_number');

        if ($lastNota) {
            $parts = explode('/', $lastNota);
            if (count($parts) === 3) {
                $lastSequence = (int) $parts[1];
                $nextSequence = $lastSequence + 1;
            } else {
                $nextSequence = 1;
            }
        } else {
            $nextSequence = 1;
        }

        return $prefix . '/' . str_pad($nextSequence, 5, '0', STR_PAD_LEFT) . '/' . $year;
    }

    /**
     * Assign nomor nota ke beberapa order sekaligus.
     *
     * PPN & PPh diinput manual (nominal rupiah, level nota) dan
didistribusikan
     * proporsional ke `amount` tiap order sehingga total nota = DPP + PPN −
PPh
     * dan seluruh alur pembayaran (sisa, validasi, alokasi) tetap konsisten.
     *
     * @param array $orderCodes
     * @param string $userBankCode
     * @param string $title
     * @param float|int $ppnAmount Nominal PPN manual (>= 0)
     * @param float|int $pphAmount Nominal PPh manual (>= 0)
     * @return string Nomor nota yang dihasilkan
     * @throws \Exception
     */
    public function assignNota(array $orderCodes, $userBankCode, $title, $ppnAmount = 0, $pphAmount = 0)
    {
        $orderCodes = array_values(array_unique(array_filter($orderCodes)));

        if (empty($orderCodes)) {
            throw new \Exception('Pilih minimal satu order untuk di-nota-kan.');
        }

        $ppnAmount = max(0, (float) $ppnAmount);
        $pphAmount = max(0, (float) $pphAmount);

        // Ambil semua order terpilih dengan relasi customer, company, dan fleet
        $orders = $this->order->with(['customer.company', 'fleet.company'])->whereIn('code', $orderCodes)->get();
        if ($orders->count() !== count($orderCodes)) {
            throw new \Exception('Beberapa order tidak ditemukan.');
        }

        // Validasi 1: Perusahaan kendaraan (fleet company) yang berbeda tidak boleh dalam satu nota
        $fleetCompanyCodes = $orders->map(function ($order) {
            return $order->fleet->fleetCompanyCode ?? null;
        })->filter()->unique();
        if ($fleetCompanyCodes->count() > 1) {
            throw new \Exception('Gagal: Order yang dipilih memiliki perusahaan kendaraan yang berbeda. Satu nota hanya diperbolehkan untuk perusahaan kendaraan yang sama.');
        }

        // Validasi 3: Format Perusahaan (Pribadi, PHL, WTMS) yang berbeda tidak boleh dalam satu nota
        $companyFormats = $orders->map(function ($order) {
            return strtoupper(trim((string) ($order->customer->company->format ?? '')));
        })->filter()->unique();
        if ($companyFormats->count() > 1) {
            throw new \Exception('Gagal: Order yang dipilih memiliki format perusahaan yang berbeda (' . $companyFormats->implode(', ') . '). Semua order dalam satu nota harus memiliki format perusahaan yang sama.');
        }

        // Cari vendor payment yang sudah ada untuk order-order ini
        $vendorPayments = $this->service
            ->whereIn('orderCode', $orderCodes)
            ->get();

        // Validasi: tidak boleh ada order yang sudah memiliki nota
        $alreadyNota = $vendorPayments->whereNotNull('nota_number');
        if ($alreadyNota->isNotEmpty()) {
            throw new \Exception('Order sudah memiliki nota: ' . $alreadyNota->pluck('orderCode')->implode(', '));
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
        $totalDpp = 0.0;
        foreach ($orderCodes as $orderCode) {
            $order = $orders->firstWhere('code', $orderCode);
            $vendorPayment = $vendorPayments->firstWhere('orderCode', $orderCode);
            $dpp = (float) ($vendorPayment->amount ?? $order->vendorPrice ?? 0);

            $dppByOrder[$orderCode] = $dpp;
            $totalDpp += $dpp;
        }

        // Validasi: total bayar (DPP + PPN − PPh) tidak boleh negatif
        $grandTotal = $totalDpp + $ppnAmount - $pphAmount;
        if ($grandTotal < 0) {
            throw new \Exception('Total bayar (DPP + PPN − PPh) tidak boleh negatif. Periksa kembali nominal PPh yang diinput.');
        }

        // Distribusi PPN & PPh proporsional ke tiap order (largest remainder, hasil bulat rupiah agar total persis sama dengan input manual)
        $ppnShares = $this->distributeProportionally($ppnAmount, $dppByOrder, $totalDpp);
        $pphShares = $this->distributeProportionally($pphAmount, $dppByOrder, $totalDpp);

        $logPayment = null;

        foreach ($orderCodes as $orderCode) {
            $order = $orders->firstWhere('code', $orderCode);
            $vendorPayment = $vendorPayments->firstWhere('orderCode', $orderCode);

            // Amount baru = DPP + porsi PPN − porsi PPh (integer rupiah)
            $newAmount = (int) round($dppByOrder[$orderCode] + $ppnShares[$orderCode] - $pphShares[$orderCode]);

            if ($vendorPayment) {
                // Update yang sudah ada (pertahankan riwayat pembayaran)
                $paidAmount = (float) ($vendorPayment->paid_amount ?? 0);

                $vendorPayment->update([
                    'nota_number' => $notaNumber,
                    'user_bank_code' => $userBankCode,
                    'amount' => $newAmount,
                    'remaining_amount' => max(0, $newAmount - $paidAmount),
                    'ppn_amount' => $ppnAmount,
                    'pph_amount' => $pphAmount,
                ]);
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
                    'pph_amount' => $pphAmount,
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

    public function cancelNota($orderCode, $title)
    {
        $vendorPayment = $this->service->where('orderCode', $orderCode)->first();

        if (!$vendorPayment) {
            throw new \Exception('Data pembayaran tidak ditemukan.');
        }

        $notaNumber = $vendorPayment->nota_number;

        if ($notaNumber) {
            // Ambil semua vendor payment yang memiliki nomor nota yang sama
            $paymentsInNota = $this->service->where('nota_number', $notaNumber)->get();

            // Validasi: tidak boleh ada yang sudah dibayar di grup nota ini
            $alreadyPaid = $paymentsInNota->filter(function ($vp) {
                return $vp->paid_amount > 0 || $vp->payment_status !== 'pending';
            });

            if ($alreadyPaid->isNotEmpty()) {
                throw new \Exception('Nota tidak dapat dibatalkan karena beberapa order di dalam nota ini (' . $notaNumber . ') sudah dibayar. Batalkan pembayaran terlebih dahulu.');
            }

            // Hapus semua record vendor_payment di grup nota ini secara fisik (hard delete)
            foreach ($paymentsInNota as $payment) {
                $payment->forceDelete();
            }

            // Log activity
            $this->logActivity($title, $vendorPayment, 'Cancel Nota ' . $notaNumber . ' (All associated orders reset)');
        } else {
            if ($vendorPayment->paid_amount > 0 || $vendorPayment->payment_status !== 'pending') {
                throw new \Exception('Pembayaran sudah dilakukan, tidak dapat dibatalkan.');
            }
            $vendorPayment->forceDelete();
            $this->logActivity($title, $vendorPayment, 'Cancel Unassigned Payment Record');
        }
    }
}

