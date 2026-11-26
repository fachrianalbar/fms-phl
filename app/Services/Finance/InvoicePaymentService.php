<?php

namespace App\Services\Finance;

use App\Models\Finance\InvoicePayment;

class InvoicePaymentService
{
    protected $service;

    public function __construct(InvoicePayment $invoicePayment)
    {
        $this->service = $invoicePayment;
    }

    /**
     * Daftar seluruh pembayaran faktur (1 baris = 1 pembayaran), terbaru dulu.
     * Digunakan oleh datatable menu Invoice Payment & export Excel/PDF.
     */
    public function datatable(array $filters = [])
    {
        $query = $this->service->with(['invoice.customer', 'invoice.claims', 'userBank.bank', 'transaction']);

        $startDate = trim((string) ($filters['startDate'] ?? ''));
        $endDate = trim((string) ($filters['endDate'] ?? ''));
        $customerCode = trim((string) ($filters['customerCode'] ?? ''));
        $customerName = trim((string) ($filters['customerName'] ?? ''));
        $userBankCode = trim((string) ($filters['userBankCode'] ?? ''));
        $invoiceNumber = trim((string) ($filters['invoiceNumber'] ?? ''));

        if ($startDate !== '') {
            $query->whereDate('paymentDate', '>=', $startDate);
        }

        if ($endDate !== '') {
            $query->whereDate('paymentDate', '<=', $endDate);
        }

        if ($customerCode !== '') {
            $query->whereHas('invoice', function ($q) use ($customerCode) {
                $q->where('customerCode', $customerCode);
            });
        }

        if ($customerName !== '') {
            $query->whereHas('invoice.customer', function ($q) use ($customerName) {
                $q->where('name', 'like', "%{$customerName}%");
            });
        }

        if ($userBankCode !== '') {
            $query->where('userBankCode', $userBankCode);
        }

        if ($invoiceNumber !== '') {
            $query->whereHas('invoice', function ($q) use ($invoiceNumber) {
                $q->where('invoiceNumber', 'like', "%{$invoiceNumber}%");
            });
        }

        return $query
            ->orderBy('paymentDate', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Label jenis pembayaran per id pembayaran: "Pembayaran" (tunggal),
     * "DP" (pertama dari beberapa), "Cicilan ke-N", dan "— Pelunasan"
     * untuk pembayaran terakhir yang melunasi faktur.
     * Konsisten dengan logika modal Riwayat Pembayaran Faktur.
     */
    public function paymentLabels($payments)
    {
        $labels = [];

        $payments
            ->sortBy([['paymentDate', 'asc'], ['created_at', 'asc']])
            ->groupBy('invoiceCode')
            ->each(function ($group) use (&$labels) {
                $invoice = $group->first()->invoice;
                $total = $group->count();
                $settled = false;

                if ($invoice) {
                    $billing = (float) (($invoice->invoiceAmount ?? 0) + ($invoice->ppnAmount ?? 0) - ($invoice->pphAmount ?? 0));
                    $totalPaid = (float) $group->sum('amount');
                    $totalClaim = (float) ($invoice->claims->sum('amount') ?? 0);
                    $settled = $billing > 0 && ($totalPaid + $totalClaim) >= $billing;
                }

                foreach ($group->values() as $i => $payment) {
                    $seq = $i + 1;

                    if ($total == 1) {
                        $label = 'Pembayaran';
                    } elseif ($seq == 1) {
                        $label = 'DP';
                    } else {
                        $label = 'Cicilan ke-'.($seq - 1);
                    }

                    if ($seq == $total && $total > 1 && $settled) {
                        $label .= ' — Pelunasan';
                    }

                    $labels[$payment->id] = $label;
                }
            });

        return $labels;
    }
}
