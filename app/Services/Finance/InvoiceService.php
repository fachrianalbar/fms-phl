<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Finance\InvoicePaymentClaim;
use App\Models\Master\Customer;
use App\Models\Operational\Order;
use App\Services\UniqueCodeService;
use App\Traits\LogActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class InvoiceService
{
    use LogActivity;

    protected $service;

    protected $order;

    protected $invoiceDetail;

    protected $customer;

    public function __construct(Invoice $invoice, Order $order, InvoiceDetail $invoiceDetail, Customer $customer, private UniqueCodeService $uniqueCode, protected InvoicePaymentClaim $claim)
    {
        $this->service = $invoice;
        $this->order = $order;
        $this->invoiceDetail = $invoiceDetail;
        $this->customer = $customer;
    }

    public function findAll()
    {
        return $this->service->with([
            'customer',
            'payments',
            'claims',
            'details.order.cost.costComponent',
            'details.order.route.originLocation',
            'details.order.route.destinationLocation',
            'details.order.fleet',
        ])->orderBy('created_at', 'desc')->get();
    }

    private function settledExpression(): string
    {
        return 'COALESCE((SELECT SUM(ip.amount) FROM invoice_payment ip WHERE ip.invoiceCode = invoice.code AND ip.deleted_at IS NULL), 0)
            + COALESCE((SELECT SUM(ipc.amount) FROM invoice_payment_claim ipc WHERE ipc.invoiceCode = invoice.code AND ipc.deleted_at IS NULL), 0)';
    }

    private function billingExpression(): string
    {
        return '(COALESCE(invoice.invoiceAmount, 0) + COALESCE(invoice.ppnAmount, 0) - COALESCE(invoice.pphAmount, 0))';
    }

    /**
     * Klasifikasi faktur berdasarkan pembayaran + claim aktual, bukan status tersimpan.
     */
    public function paymentStateQuery(string $state): Builder
    {
        $query = $this->service->newQuery();
        $settled = $this->settledExpression();
        $billing = $this->billingExpression();

        return match ($state) {
            'open' => $query
                ->whereRaw("{$billing} > 0")
                ->whereRaw("{$settled} < {$billing}"),
            'unpaid' => $query->whereRaw("{$settled} = 0"),
            'partial' => $query
                ->whereRaw("{$settled} > 0")
                ->whereRaw("{$settled} < {$billing}"),
            'paid' => $query
                ->whereRaw("{$billing} > 0")
                ->whereRaw("{$settled} >= {$billing}"),
            default => throw new \InvalidArgumentException("Status pembayaran faktur tidak dikenal: {$state}"),
        };
    }

    private function invoiceListingQuery(string $state): Builder
    {
        return $this->paymentStateQuery($state)->with([
            'customer',
            'payments',
            'claims',
            'details.order.cost.costComponent',
            'details.order.route.originLocation',
            'details.order.route.destinationLocation',
            'details.order.fleet',
        ]);
    }

    public function findUnpaid()
    {
        return $this->invoiceListingQuery('unpaid')
            ->orderBy('invoiceDate', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function findPartial()
    {
        return $this->invoiceListingQuery('partial')
            ->orderBy('invoiceDate', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function findPaid()
    {
        return $this->invoiceListingQuery('paid')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function determinePaymentStatus(Invoice $invoice, ?float $billing = null): int
    {
        $billing ??= (float) (($invoice->invoiceAmount ?? 0) + ($invoice->ppnAmount ?? 0) - ($invoice->pphAmount ?? 0));
        $settled = (float) $invoice->payments()->sum('amount')
            + (float) $invoice->claims()->sum('amount');

        if ($billing > 0 && $settled >= $billing) {
            return Invoice::STATUS_FULL;
        }

        return $settled > 0 ? Invoice::STATUS_PARTIAL : Invoice::STATUS_CREATE;
    }

    public function synchronizePaymentStatus(Invoice $invoice, ?float $billing = null): int
    {
        $status = $this->determinePaymentStatus($invoice, $billing);
        $this->service->newQuery()->whereKey($invoice->getKey())->update(['status' => $status]);

        return $status;
    }

    /**
     * Terapkan tarif dan basis pajak customer ke invoice yang belum memiliki
     * pembayaran/claim. Invoice yang sudah tersettle tidak boleh berubah agar
     * histori transaksi keuangan tetap konsisten.
     */
    public function synchronizeCustomerTaxSettings(Customer $customer): int
    {
        $ppnRate = (float) ($customer->ppn ?? 0);
        $pphRate = (float) ($customer->pph ?? 0);
        $pphBaseType = $customer->pphBaseType === 'route' ? 'route' : 'subtotal';
        $updated = 0;

        $invoices = $this->service->newQuery()
            ->where('customerCode', $customer->code)
            ->whereDoesntHave('payments')
            ->whereDoesntHave('claims')
            ->with([
                'details.order.cost',
                'customer',
            ])
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->forceFill([
                'ppnRate' => $ppnRate,
                'pphRate' => $pphRate,
                'pphBaseType' => $pphBaseType,
            ]);

            $totals = $this->calculateInvoiceAmount($invoice);
            $attributes = [
                'ppnRate' => $ppnRate,
                'pphRate' => $pphRate,
                'pphBaseType' => $pphBaseType,
                'invoiceAmount' => $totals['subtotal'],
                'routeAmount' => $totals['routeTotal'],
                'onChargeAmount' => $totals['onChargeTotal'],
                'ppnAmount' => $totals['ppn'],
                'pphAmount' => $totals['pph'],
                'pphBaseAmount' => $totals['pphBaseAmount'],
                'status' => Invoice::STATUS_CREATE,
            ];

            $hasChanges = collect($attributes)->contains(
                fn ($value, $attribute) => (string) $invoice->getRawOriginal($attribute) !== (string) $value
            );

            if (! $hasChanges) {
                continue;
            }

            $this->service->newQuery()->whereKey($invoice->getKey())->update($attributes);
            $updated++;
        }

        return $updated;
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with([
            'details.order.orderMaterial.material',
            'details.order.orderMaterial.unit',
            'details.order.cost.costComponent',
            'details.order.customer',
            'details.order.fleet',
            'details.order.driver',
            'details.order.route.originLocation',
            'details.order.route.destinationLocation',
            'customer',
            'payments',
            'claims',
            'customer.pic',
        ])->first();
    }

    public function getOrder()
    {
        $usedOrderCodes = $this->invoiceDetail->newQuery()
            ->whereNull('deleted_at')
            ->select('orderCode');

        // Order yang boleh difaktur: status 4 (Kembali Do) milik customer
        // isDo = 1 (tidak langsung cetak). Customer isDo = 0 (langsung cetak)
        // ditangani menu Pembayaran Langsung.
        return $this->order
            ->whereNotIn('code', $usedOrderCodes)
            ->where('status', 4)
            ->whereHas('customer', function ($q) {
                $q->where('isDo', 1);
            })
            ->with([
                'fleet',
                'fleet.type',
                'driver',
                'customer',
                'route.originLocation',
                'route.destinationLocation',
                'orderType',
                'route.routeDetail',
                'cost.costComponent',
            ])
            ->orderBy('created_at', 'desc');
    }

    protected function ensureOrdersAreNotInInvoice(array $selectedOrders): void
    {
        $orderCodes = array_values(array_unique(array_filter($selectedOrders)));

        if (empty($orderCodes)) {
            throw new \RuntimeException('Pilih minimal 1 order untuk invoice.');
        }

        $usedOrders = $this->invoiceDetail->newQuery()
            ->whereNull('deleted_at')
            ->whereIn('orderCode', $orderCodes)
            ->pluck('orderCode')
            ->toArray();

        if (! empty($usedOrders)) {
            throw new \RuntimeException('Order berikut sudah digunakan di invoice lain: '.implode(', ', $usedOrders));
        }
    }

    protected function ensureOrdersBelongToCustomer(array $orderCodes, string $customerCode): void
    {
        $invalidOrders = $this->order->newQuery()
            ->whereIn('code', $orderCodes)
            ->where('customerCode', '!=', $customerCode)
            ->pluck('code')
            ->toArray();

        if (! empty($invalidOrders)) {
            throw new \RuntimeException('Order berikut tidak sesuai dengan customer invoice: '.implode(', ', $invalidOrders));
        }
    }

    /**
     * Order customer isDo = 0 (langsung cetak / non-DO) tidak boleh difaktur —
     * pembayarannya ditangani menu Pembayaran Langsung.
     */
    protected function ensureOrdersAreDirectPayOnly(array $orderCodes): void
    {
        $directPayOrders = $this->order->newQuery()
            ->whereIn('code', $orderCodes)
            ->whereHas('customer', function ($q) {
                $q->where('isDo', 0);
            })
            ->pluck('code')
            ->toArray();

        if (! empty($directPayOrders)) {
            throw new \RuntimeException('Order customer langsung cetak tidak dapat difaktur. Gunakan menu Pembayaran Langsung: '.implode(', ', $directPayOrders));
        }
    }

    public function store($request, $title, $selectedOrders)
    {
        $orderCodes = array_values(array_unique(array_filter((array) $selectedOrders)));
        $this->ensureOrdersAreNotInInvoice($orderCodes);
        $this->ensureOrdersBelongToCustomer($orderCodes, $request->customerCode);
        $this->ensureOrdersAreDirectPayOnly($orderCodes);

        $customer = $this->customer->where('code', $request->customerCode)->firstOrFail();
        $usePpn = (bool) ($request->input('usePpn') ?? false);
        $usePph = (bool) ($request->input('usePph') ?? false);
        $ppnRate = (float) ($customer->ppn ?? 0);
        $pphRate = (float) ($customer->pph ?? 0);
        $pphBaseType = $request->input('pphBaseType', $customer->pphBaseType ?? 'subtotal');
        $invoiceNumber = $this->resolveInvoiceNumber(
            $request->invoiceNumber,
            $request->customerCode,
            $request->invoiceDate
        );

        $data = $this->service->create([
            'code' => GenerateCode::generateCode('INV'),
            'customerCode' => $request->customerCode,
            'invoiceNumber' => $invoiceNumber->resolvedCode,
            'receiptNumber' => $request->receiptNumber,
            'poNumber' => $request->poNumber,
            'invoiceDate' => $request->invoiceDate,
            'overdueDate' => Carbon::parse($request->invoiceDate)->addDays(2)->toDateString(),
            'notes' => $request->notes,
            'usePpn' => $usePpn,
            'usePph' => $usePph,
            'ppnRate' => $ppnRate,
            'pphRate' => $pphRate,
            'pphBaseType' => $pphBaseType,
            'pphBaseAmount' => 0,
            'status' => Invoice::STATUS_CREATE,
        ]);

        if (! empty($orderCodes)) {
            foreach ($orderCodes as $item) {
                $detail = $this->invoiceDetail->create([
                    'code' => GenerateCode::generateCode('INVD', true),
                    'invoiceCode' => $data->code,
                    'orderCode' => $item,
                ]);

                $this->order->where('code', $item)->update([
                    'status' => 5,
                ]);

                $this->logActivity('Invoice Detail', $detail, 'Create');
            }
        }
        // Update snapshot komponen tagihan dan pajak setelah membuat detail invoice.
        $totals = $this->calculateInvoiceAmount($data);
        $this->service->where('id', $data->id)->update([
            'invoiceAmount' => $totals['subtotal'],
            'routeAmount' => $totals['routeTotal'],
            'onChargeAmount' => $totals['onChargeTotal'],
            'ppnAmount' => $totals['ppn'],
            'pphAmount' => $totals['pph'],
            'pphBaseAmount' => $totals['pphBaseAmount'],
        ]);

        // Sinkronkan status dari pembayaran + claim aktual.
        try {
            $this->synchronizePaymentStatus($data, (float) $totals['total']);
        } catch (\Exception $e) {
            logger()->error('Failed to update invoice status after recalculation for invoice '.$data->code.': '.$e->getMessage());
        }
        $this->logActivity($title, $data, 'Create');

        return $invoiceNumber;
    }

    public function update($request, $id, $title)
    {
        $invoice = $this->getById($id);
        $this->logActivity($title, $invoice, 'Before Update');

        $taxLocked = $invoice->payments->isNotEmpty() || $invoice->claims->isNotEmpty();
        $taxAttributes = [];
        if (! $taxLocked) {
            $taxAttributes = [
                'usePpn' => (bool) ($request->input('usePpn') ?? false),
                'usePph' => (bool) ($request->input('usePph') ?? false),
                'pphBaseType' => $request->input('pphBaseType', $invoice->pphBaseType ?? 'subtotal'),
            ];
        }

        $this->service->where('id', $id)->update(array_merge([
            'invoiceNumber' => $request->invoiceNumber,
            'receiptNumber' => $request->receiptNumber,
            'poNumber' => $request->poNumber,
            'invoiceDate' => $request->invoiceDate,
            'overdueDate' => $request->overdueDate ? Carbon::parse($request->overdueDate)->toDateString() : Carbon::parse($request->invoiceDate)->addDays(30)->toDateString(),
            'notes' => $request->notes,
        ], $taxAttributes));

        // Recalculate invoice amount after update
        $data = $this->getById($id);
        $totals = $this->calculateInvoiceAmount($data);
        $this->service->where('id', $data->id)->update([
            'invoiceAmount' => $totals['subtotal'],
            'routeAmount' => $totals['routeTotal'],
            'onChargeAmount' => $totals['onChargeTotal'],
            'ppnAmount' => $totals['ppn'],
            'pphAmount' => $totals['pph'],
            'pphBaseAmount' => $totals['pphBaseAmount'],
        ]);
        $this->synchronizePaymentStatus($data, (float) $totals['total']);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        foreach ($data->details as $item) {
            $this->order->where('code', $item->orderCode)->update([
                'status' => 4,
            ]);

            $this->invoiceDetail->where('id', $item->id)->delete();

            $this->logActivity('Invoice Detail', $item, 'Delete');
        }

        $this->service->where('id', $id)->delete();
    }

    public function getOrderDetail($id)
    {
        $data = $this->getById($id);
        $orderCodeArr = $this->invoiceDetail->where('invoiceCode', $data->code)->pluck('orderCode');

        return $this->order->whereIn('code', $orderCodeArr)->with([
            // 'fleetDriver.employee',
            'fleet',
            'driver',
            'customer',
            'route.originLocation',
            'route.destinationLocation',
            'orderType',
            'route.routeDetail',
            'cost.costComponent',
        ])->orderBy('created_at', 'desc')->get();
    }

    public function storeInvoiceDetail($request, $id, $selectedOrders)
    {
        $invoice = $this->getById($id);
        $orderCodes = array_values(array_unique(array_filter((array) $selectedOrders)));

        $this->ensureOrdersAreNotInInvoice($orderCodes);
        $this->ensureOrdersBelongToCustomer($orderCodes, $invoice->customerCode);
        $this->ensureOrdersAreDirectPayOnly($orderCodes);

        if (! empty($orderCodes)) {

            foreach ($orderCodes as $item) {
                $detail = $this->invoiceDetail->create([
                    'code' => GenerateCode::generateCode('INVD', true),
                    'invoiceCode' => $invoice->code,
                    'orderCode' => $item,
                ]);

                $this->order->where('code', $item)->update([
                    'status' => 5,
                ]);

                $this->logActivity('Invoice Detail', $detail, 'Create');
            }
            // Update snapshot komponen tagihan dan pajak setelah menambah detail.
            $totals = $this->calculateInvoiceAmount($invoice);
            $this->service->where('id', $invoice->id)->update([
                'invoiceAmount' => $totals['subtotal'],
                'routeAmount' => $totals['routeTotal'],
                'onChargeAmount' => $totals['onChargeTotal'],
                'ppnAmount' => $totals['ppn'],
                'pphAmount' => $totals['pph'],
                'pphBaseAmount' => $totals['pphBaseAmount'],
            ]);

            // Update invoice status after recalc
            try {
                $invoiceModel = $this->service->find($invoice->id);
                $sumPayments = (int) $invoiceModel->payments()->sum('amount');
                $sumClaims = (int) $this->claim->where('invoiceCode', $invoice->code)->whereNull('deleted_at')->sum('amount');
                $settled = $sumPayments + $sumClaims;
                $invoiceTotal = (int) $totals['total'];
                $nextStatus = Invoice::STATUS_CREATE;
                if ($invoiceTotal > 0 && $settled >= $invoiceTotal) {
                    $nextStatus = Invoice::STATUS_FULL;
                } elseif ($settled > 0) {
                    $nextStatus = Invoice::STATUS_PARTIAL;
                }
                $this->service->where('id', $invoice->id)->update(['status' => $nextStatus]);
            } catch (\Exception $e) {
                logger()->error('Failed to update invoice status after adding details for invoice '.$invoice->code.': '.$e->getMessage());
            }
        }
    }

    public function destroyInvoiceDetail($id)
    {
        $order = $this->order->where('id', $id)->first();

        $this->order->where('id', $id)->update([
            'status' => 4,
        ]);

        $data = $this->invoiceDetail->where('orderCode', $order->code)->first();

        $this->logActivity('Invoice Detail', $data, 'Delete');

        $this->invoiceDetail->where('orderCode', $order->code)->delete();

        // Update snapshot komponen tagihan dan pajak setelah menghapus detail.
        $invoice = $this->getById($data->invoiceCode ?? null);
        if ($invoice) {
            $totals = $this->calculateInvoiceAmount($invoice);
            $this->service->where('id', $invoice->id)->update([
                'invoiceAmount' => $totals['subtotal'],
                'routeAmount' => $totals['routeTotal'],
                'onChargeAmount' => $totals['onChargeTotal'],
                'ppnAmount' => $totals['ppn'],
                'pphAmount' => $totals['pph'],
                'pphBaseAmount' => $totals['pphBaseAmount'],
            ]);

            // Update invoice status after recalc
            try {
                $invoiceModel = $this->service->find($invoice->id);
                $sumPayments = (int) $invoiceModel->payments()->sum('amount');
                $sumClaims = (int) $this->claim->where('invoiceCode', $invoice->code)->whereNull('deleted_at')->sum('amount');
                $settled = $sumPayments + $sumClaims;
                $invoiceTotal = (int) $totals['total'];
                $nextStatus = Invoice::STATUS_CREATE;
                if ($invoiceTotal > 0 && $settled >= $invoiceTotal) {
                    $nextStatus = Invoice::STATUS_FULL;
                } elseif ($settled > 0) {
                    $nextStatus = Invoice::STATUS_PARTIAL;
                }
                $this->service->where('id', $invoice->id)->update(['status' => $nextStatus]);
            } catch (\Exception $e) {
                logger()->error('Failed to update invoice status after removing details for invoice '.$invoice->code.': '.$e->getMessage());
            }
        }
    }

    /**
     * Hitung invoice dari tarif rute, biaya On Charge, dan snapshot pajak invoice.
     */
    public function calculateInvoiceAmount($invoiceOrId)
    {
        $invoice = $invoiceOrId instanceof Invoice ? $invoiceOrId : $this->getById($invoiceOrId);

        if (! $invoice) {
            return 0;
        }

        $routeTotal = 0;
        $onChargeTotal = 0;

        foreach ($invoice->details as $detail) {
            $order = $detail->order ?? null;
            if (! $order) {
                $order = $this->order->where('code', $detail->orderCode)->with('cost')->first();
            }
            if (! $order) {
                continue;
            }

            $routeTotal += (float) ($order->routeAmount ?? 0);

            if (isset($order->cost)) {
                foreach ($order->cost as $cost) {
                    if (isset($cost->type) && strtolower($cost->type) === 'on charge') {
                        $onChargeTotal += (float) ($cost->nominal ?? 0);
                    }
                }
            }
        }

        $subtotal = $routeTotal + $onChargeTotal;
        $ppnRate = (float) ($invoice->ppnRate ?? $invoice->customer?->ppn ?? 0);
        $pphRate = (float) ($invoice->pphRate ?? $invoice->customer?->pph ?? 0);
        $pphBaseType = in_array($invoice->pphBaseType, ['route', 'subtotal'], true)
            ? $invoice->pphBaseType
            : 'subtotal';
        $pphBaseAmount = $pphBaseType === 'route' ? $routeTotal : $subtotal;

        $ppn = $invoice->usePpn ? $subtotal * ($ppnRate / 100) : 0;
        $pph = $invoice->usePph ? $pphBaseAmount * ($pphRate / 100) : 0;
        $total = (int) round($subtotal + $ppn - $pph);

        return [
            'routeTotal' => (int) round($routeTotal),
            'onChargeTotal' => (int) round($onChargeTotal),
            'subtotal' => (int) round($subtotal),
            'ppn' => (int) round($ppn),
            'pph' => (int) round($pph),
            'pphBaseAmount' => (int) round($pphBaseAmount),
            'total' => $total,
        ];
    }

    public function invoiceNumberFormat($id, $invoiceDate = null)
    {
        $customer = $this->customer->where('id', $id)->with('company')->first();

        // Gunakan invoiceDate jika ada, jika tidak gunakan tanggal hari ini
        $dateToUse = $invoiceDate ? Carbon::parse($invoiceDate) : now();
        $currentYear = $dateToUse->year;
        $currentMonth = str_pad($dateToUse->month, 2, '0', STR_PAD_LEFT);

        // Ambil invoiceNumber terakhir milik customer yang bersangkutan di bulan dan tahun dari invoiceDate
        $invoices = $this->service
            ->withTrashed()
            ->where('customerCode', $customer->code)
            ->whereYear('invoiceDate', $currentYear)
            ->whereMonth('invoiceDate', $dateToUse->month)
            ->get();

        // Default increment = 1 jika belum ada invoice sebelumnya
        $lastNumber = 0;

        // Format: INV/{FORMAT-COMPANY}/{CODE-CUSTOMER}/{NO-URUT}/{BULAN}/{TAHUN}
        foreach ($invoices as $invoice) {
            if (preg_match('/INV\/'.preg_quote($customer->company->format, '/').'\/'.preg_quote($customer->code, '/').'\/(\d{5})\//', $invoice->invoiceNumber, $matches)) {
                $lastNumber = max($lastNumber, (int) $matches[1]);
            }
        }

        $increment = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        $companyFormat = $customer->company->format ?? 'DEFAULT';

        return 'INV/'.$companyFormat.'/'.$customer->code.'/'.$increment.'/'.$currentMonth.'/'.$currentYear;
    }

    public function recalculate($invoiceId)
    {
        $invoice = $this->getById($invoiceId);

        if (! $invoice) {
            return null;
        }

        // Delete all invoice payments for this invoice
        $invoice->payments()->delete();

        // Hapus juga claim pengurang tagihan agar status invoice konsisten
        $invoice->claims()->delete();

        // Tarif dan basis pajak tetap memakai snapshot invoice, bukan master customer terkini.
        $totals = $this->calculateInvoiceAmount($invoice);

        // Update invoice: reset status to CREATE and update amounts
        $this->service->where('id', $invoiceId)->update([
            'status' => Invoice::STATUS_CREATE, // Reset to CREATE status
            'invoiceAmount' => $totals['subtotal'],
            'routeAmount' => $totals['routeTotal'],
            'onChargeAmount' => $totals['onChargeTotal'],
            'ppnAmount' => $totals['ppn'],
            'pphAmount' => $totals['pph'],
            'pphBaseAmount' => $totals['pphBaseAmount'],
        ]);

        $this->logActivity('Invoice', $invoice, 'Recalculate Amount and Cancel Payments');

        return [
            'invoiceAmount' => $totals['subtotal'],
            'ppnAmount' => $totals['ppn'],
            'pphAmount' => $totals['pph'],
            'total' => $totals['total'],
        ];
    }

    public function updateInvoiceNumber($id, $newInvoiceNumber)
    {
        $invoice = $this->getById($id);
        if (! $invoice) {
            throw new \InvalidArgumentException('Invoice tidak ditemukan');
        }

        // Validate format: INV/FORMAT/CUSTOMER/SEQ/MONTH/YEAR
        if (! preg_match('/^INV\/([^\/]+)\/([^\/]+)\/(\d{5})\/(\d{2})\/(\d{4})$/', $newInvoiceNumber, $matches)) {
            throw new \InvalidArgumentException('Format nomor invoice tidak valid. Harus seperti: INV/PHL/MLB/00018/06/2026');
        }

        $companyFormat = $matches[1];
        $customerCode = $matches[2];
        $targetSequence = (int) $matches[3];
        $month = $matches[4];
        $year = $matches[5];

        // If the number hasn't changed, do nothing
        if ($invoice->invoiceNumber === $newInvoiceNumber) {
            return $invoice;
        }

        // Fetch other invoices of the same customer in the same month and year
        $otherInvoices = $this->service
            ->where('customerCode', $customerCode)
            ->where('id', '!=', $id)
            ->whereYear('invoiceDate', $year)
            ->whereMonth('invoiceDate', (int) $month)
            ->get();

        // Map and parse the sequence numbers of other invoices
        $invoicesToShift = [];
        foreach ($otherInvoices as $other) {
            if (preg_match('/^INV\/([^\/]+)\/([^\/]+)\/(\d{5})\/(\d{2})\/(\d{4})$/', $other->invoiceNumber, $m)) {
                $seq = (int) $m[3];
                if ($seq >= $targetSequence) {
                    $invoicesToShift[] = [
                        'invoice' => $other,
                        'sequence' => $seq,
                    ];
                }
            }
        }

        // Sort descending by sequence to avoid duplicates during update
        usort($invoicesToShift, function ($a, $b) {
            return $b['sequence'] <=> $a['sequence'];
        });

        // Shift each sequence up by 1
        foreach ($invoicesToShift as $item) {
            $nextSeq = str_pad($item['sequence'] + 1, 5, '0', STR_PAD_LEFT);
            $newNum = "INV/{$companyFormat}/{$customerCode}/{$nextSeq}/{$month}/{$year}";

            $item['invoice']->update([
                'invoiceNumber' => $newNum,
            ]);

            $this->logActivity('Invoice', $item['invoice'], 'Shift Invoice Number due to Conflict');
        }

        $resolved = $this->resolveInvoiceNumber(
            $newInvoiceNumber,
            $customerCode,
            "{$year}-{$month}-01",
            $id
        );

        // Finally, update the target invoice
        $this->service->where('id', $id)->update([
            'invoiceNumber' => $resolved->resolvedCode,
        ]);

        $updatedInvoice = $this->getById($id);
        $this->logActivity('Invoice', $updatedInvoice, 'Update Invoice Number Manually');

        return $resolved;
    }

    public function getSuggestedInvoiceNumber($id)
    {
        $invoice = $this->getById($id);
        if (! $invoice) {
            throw new \InvalidArgumentException('Invoice tidak ditemukan');
        }

        $customer = $invoice->customer;
        $dateToUse = Carbon::parse($invoice->invoiceDate);
        $currentYear = $dateToUse->year;
        $currentMonth = str_pad($dateToUse->month, 2, '0', STR_PAD_LEFT);

        // Find the maximum sequence number in the DB for this customer/month/year
        $invoices = $this->service
            ->withTrashed()
            ->where('customerCode', $invoice->customerCode)
            ->whereYear('invoiceDate', $currentYear)
            ->whereMonth('invoiceDate', $dateToUse->month)
            ->get();

        $maxNumber = 0;
        foreach ($invoices as $inv) {
            if (preg_match('/^INV\/([^\/]+)\/([^\/]+)\/(\d{5})\/(\d{2})\/(\d{4})$/', $inv->invoiceNumber, $matches)) {
                $seq = (int) $matches[3];
                if ($seq > $maxNumber) {
                    $maxNumber = $seq;
                }
            }
        }

        $nextNumber = str_pad($maxNumber + 1, 5, '0', STR_PAD_LEFT);
        $companyFormat = $customer->company->format ?? 'DEFAULT';

        return 'INV/'.$companyFormat.'/'.$customer->code.'/'.$nextNumber.'/'.$currentMonth.'/'.$currentYear;
    }

    private function resolveInvoiceNumber(string $number, string $customerCode, string $invoiceDate, ?string $ignoreId = null)
    {
        $date = Carbon::parse($invoiceDate);

        return $this->uniqueCode->resolve(
            model: Invoice::class,
            field: 'invoiceNumber',
            requestedCode: $number,
            digits: 5,
            scope: fn ($query) => $query
                ->where('customerCode', $customerCode)
                ->whereYear('invoiceDate', $date->year)
                ->whereMonth('invoiceDate', $date->month),
            ignoreId: $ignoreId,
        );
    }
}
