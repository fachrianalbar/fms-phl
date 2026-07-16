<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Master\Customer;
use App\Models\Operational\Order;
use App\Services\UniqueCodeService;
use App\Traits\LogActivity;
use Carbon\Carbon;

class InvoiceService
{
    use LogActivity;

    protected $service;

    protected $order;

    protected $invoiceDetail;

    protected $customer;

    public function __construct(Invoice $invoice, Order $order, InvoiceDetail $invoiceDetail, Customer $customer, private UniqueCodeService $uniqueCode)
    {
        $this->service = $invoice;
        $this->order = $order;
        $this->invoiceDetail = $invoiceDetail;
        $this->customer = $customer;
    }

    public function findAll()
    {
        return $this->service->with(['details', 'payments'])->orderBy('created_at', 'desc')->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with([
            'details.order.orderMaterial.material',
            'details.order.orderMaterial.unit',
            'details.order.cost',
            'details.order.customer',
            'details.order.fleet',
            'details.order.driver',
            'details.order.route.originLocation',
            'details.order.route.destinationLocation',
            'customer',
            'payments',
            'customer.pic',
        ])->first();
    }

    public function getOrder()
    {
        $usedOrderCodes = $this->invoiceDetail->newQuery()
            ->whereNull('deleted_at')
            ->select('orderCode');

        return $this->order
            ->whereNotIn('code', $usedOrderCodes)
            ->where(function ($q) {
                $q->where('status', 4)
                    ->orWhereHas('customer', function ($q2) {
                        $q2->where('isDo', 0);
                    });
            })
            ->where('status', '!=', 5) // buang semua status 5
            ->with([
                'fleet',
                'fleet.type',
                'driver',
                'customer',
                'route.originLocation',
                'route.destinationLocation',
                'orderType',
                'route.routeDetail',
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
            throw new \RuntimeException('Order berikut sudah digunakan di invoice lain: ' . implode(', ', $usedOrders));
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
            throw new \RuntimeException('Order berikut tidak sesuai dengan customer invoice: ' . implode(', ', $invalidOrders));
        }
    }

    public function store($request, $title, $selectedOrders)
    {
        $orderCodes = array_values(array_unique(array_filter((array) $selectedOrders)));
        $this->ensureOrdersAreNotInInvoice($orderCodes);
        $this->ensureOrdersBelongToCustomer($orderCodes, $request->customerCode);

        $usePpn = (bool) ($request->input('usePpn') ?? false);
        $usePph = (bool) ($request->input('usePph') ?? false);
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
        // Update invoiceAmount (subtotal), ppnAmount and pphAmount after creating invoice details
        $totals = $this->calculateInvoiceAmount($data);
        $this->service->where('id', $data->id)->update([
            'invoiceAmount' => $totals['subtotal'],
            'ppnAmount' => $totals['ppn'],
            'pphAmount' => $totals['pph'],
        ]);

        // Update invoice status after recalc
        try {
            $sumPayments = (int) $this->service->find($data->id)->payments()->sum('amount');
            $invoiceTotal = (int) $totals['total'];
            $nextStatus = Invoice::STATUS_CREATE;
            if ($invoiceTotal > 0 && $sumPayments >= $invoiceTotal) {
                $nextStatus = Invoice::STATUS_FULL;
            } elseif ($sumPayments > 0) {
                $nextStatus = Invoice::STATUS_PARTIAL;
            }
            $this->service->where('id', $data->id)->update(['status' => $nextStatus]);
        } catch (\Exception $e) {
            logger()->error('Failed to update invoice status after recalculation for invoice ' . $data->code . ': ' . $e->getMessage());
        }
        $this->logActivity($title, $data, 'Create');

        return $invoiceNumber;
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'invoiceNumber' => $request->invoiceNumber,
            'receiptNumber' => $request->receiptNumber,
            'poNumber' => $request->poNumber,
            'invoiceDate' => $request->invoiceDate,
            'overdueDate' => Carbon::parse($request->invoiceDate)->addDays(2)->toDateString(),
            'notes' => $request->notes,
            'usePpn' => (bool) ($request->input('usePpn') ?? false),
            'usePph' => (bool) ($request->input('usePph') ?? false),
        ]);

        // Recalculate invoice amount after update
        $data = $this->getById($id);
        $totals = $this->calculateInvoiceAmount($data);
        $this->service->where('id', $data->id)->update([
            'invoiceAmount' => $totals['subtotal'],
            'ppnAmount' => $totals['ppn'],
            'pphAmount' => $totals['pph'],
        ]);

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
            'fleetDriver.fleet',
            // 'fleetDriver.employee',
            'fleet',
            'driver',
            'customer',
            'route.originLocation',
            'route.destinationLocation',
            'route.originLocation',
            'orderType',
            'route.routeDetail',
        ])->orderBy('created_at', 'desc')->get();
    }

    public function storeInvoiceDetail($request, $id, $selectedOrders)
    {
        $invoice = $this->getById($id);
        $orderCodes = array_values(array_unique(array_filter((array) $selectedOrders)));

        $this->ensureOrdersAreNotInInvoice($orderCodes);
        $this->ensureOrdersBelongToCustomer($orderCodes, $invoice->customerCode);

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
            // update invoice and ppn amounts after adding details
            $totals = $this->calculateInvoiceAmount($invoice);
            $this->service->where('id', $invoice->id)->update([
                'invoiceAmount' => $totals['subtotal'],
                'ppnAmount' => $totals['ppn'],
                'pphAmount' => $totals['pph'],
            ]);

            // Update invoice status after recalc
            try {
                $sumPayments = (int) $this->service->find($invoice->id)->payments()->sum('amount');
                $invoiceTotal = (int) $totals['total'];
                $nextStatus = Invoice::STATUS_CREATE;
                if ($invoiceTotal > 0 && $sumPayments >= $invoiceTotal) {
                    $nextStatus = Invoice::STATUS_FULL;
                } elseif ($sumPayments > 0) {
                    $nextStatus = Invoice::STATUS_PARTIAL;
                }
                $this->service->where('id', $invoice->id)->update(['status' => $nextStatus]);
            } catch (\Exception $e) {
                logger()->error('Failed to update invoice status after adding details for invoice ' . $invoice->code . ': ' . $e->getMessage());
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

        // update invoice amount after removing detail
        $invoice = $this->getById($data->invoiceCode ?? null);
        if ($invoice) {
            $totals = $this->calculateInvoiceAmount($invoice);
            $this->service->where('id', $invoice->id)->update([
                'invoiceAmount' => $totals['subtotal'],
                'ppnAmount' => $totals['ppn'],
                'pphAmount' => $totals['pph'],
            ]);

            // Update invoice status after recalc
            try {
                $sumPayments = (int) $this->service->find($invoice->id)->payments()->sum('amount');
                $invoiceTotal = (int) $totals['total'];
                $nextStatus = Invoice::STATUS_CREATE;
                if ($invoiceTotal > 0 && $sumPayments >= $invoiceTotal) {
                    $nextStatus = Invoice::STATUS_FULL;
                } elseif ($sumPayments > 0) {
                    $nextStatus = Invoice::STATUS_PARTIAL;
                }
                $this->service->where('id', $invoice->id)->update(['status' => $nextStatus]);
            } catch (\Exception $e) {
                logger()->error('Failed to update invoice status after removing details for invoice ' . $invoice->code . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Calculate invoice total based on details, allowances, tonase bonus, order costs and customer ppn.
     */
    public function calculateInvoiceAmount($invoiceOrId)
    {
        $invoice = $invoiceOrId instanceof \App\Models\Finance\Invoice ? $invoiceOrId : $this->getById($invoiceOrId);

        if (! $invoice) {
            return 0;
        }

        $subtotal = 0;

        foreach ($invoice->details as $detail) {
            // Prefer using loaded relation to avoid extra queries
            $order = ($detail->order ?? null);
            if (! $order) {
                $order = $this->order->where('code', $detail->orderCode)->with('cost')->first();
            }
            if (! $order) {
                continue;
            }

            // `routeAmount` is stored as total for the order (unit price * qty), use it directly
            $routeAmount = (float) ($order->routeAmount ?? 0);
            $subtotal += $routeAmount;

            // add On Charge order costs
            $onChargeCost = 0;
            if (isset($order->cost)) {
                foreach ($order->cost as $c) {
                    if (isset($c->type) && strtolower($c->type) === 'on charge') {
                        $onChargeCost += (int) $c->nominal;
                    }
                }
            }
            $subtotal += $onChargeCost;
        }

        $ppn = 0;
        $usePpn = $invoice->usePpn ?? true; // default true if not set
        if ($usePpn && $invoice->customer && isset($invoice->customer->ppn)) {
            $ppn = $subtotal * ($invoice->customer->ppn / 100);
        }

        $pph = 0;
        $usePph = $invoice->usePph ?? (isset($invoice->customer->pph) && $invoice->customer->pph > 0);
        if ($usePph && $invoice->customer && isset($invoice->customer->pph)) {
            $pph = $subtotal * ($invoice->customer->pph / 100);
        }

        $total = (int) round($subtotal + $ppn - $pph);

        return [
            'subtotal' => (int) round($subtotal),
            'ppn' => (int) round($ppn),
            'pph' => (int) round($pph),
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
            if (preg_match('/INV\/' . preg_quote($customer->company->format, '/') . '\/' . preg_quote($customer->code, '/') . '\/(\d{5})\//', $invoice->invoiceNumber, $matches)) {
                $lastNumber = max($lastNumber, (int) $matches[1]);
            }
        }

        $increment = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        $companyFormat = $customer->company->format ?? 'DEFAULT';

        return 'INV/' . $companyFormat . '/' . $customer->code . '/' . $increment . '/' . $currentMonth . '/' . $currentYear;
    }

    public function recalculate($invoiceId)
    {
        $invoice = $this->getById($invoiceId);

        if (! $invoice) {
            return null;
        }

        // Delete all invoice payments for this invoice
        $invoice->payments()->delete();

        // Sync usePpn and usePph to customer defaults if they are not already set (or always sync to customer if customer has them)
        $usePpn = $invoice->usePpn || (isset($invoice->customer->ppn) && $invoice->customer->ppn > 0);
        $usePph = $invoice->usePph || (isset($invoice->customer->pph) && $invoice->customer->pph > 0);

        $this->service->where('id', $invoiceId)->update([
            'usePpn' => $usePpn,
            'usePph' => $usePph,
        ]);

        $invoice->usePpn = $usePpn;
        $invoice->usePph = $usePph;

        // Calculate new amounts
        $totals = $this->calculateInvoiceAmount($invoice);

        // Update invoice: reset status to CREATE and update amounts
        $this->service->where('id', $invoiceId)->update([
            'status' => Invoice::STATUS_CREATE, // Reset to CREATE status
            'invoiceAmount' => $totals['subtotal'],
            'ppnAmount' => $totals['ppn'],
            'pphAmount' => $totals['pph'],
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
                'invoiceNumber' => $newNum
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
            'invoiceNumber' => $resolved->resolvedCode
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

        return 'INV/' . $companyFormat . '/' . $customer->code . '/' . $nextNumber . '/' . $currentMonth . '/' . $currentYear;
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
