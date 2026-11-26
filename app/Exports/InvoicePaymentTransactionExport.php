<?php

namespace App\Exports;

use App\Models\Finance\Invoice;
use App\Services\Finance\InvoicePaymentTransactionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicePaymentTransactionExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected InvoicePaymentTransactionService $service
    ) {}

    public function collection(): Collection
    {
        return $this->service->datatable($this->filters);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Bayar',
            'Kode Transaksi',
            'Pelanggan',
            'Bank Penerima',
            'Jumlah Faktur',
            'No. Faktur',
            'Total Claim',
            'Total Diterima',
            'Status',
            'Keterangan',
        ];
    }

    public function map($transaction): array
    {
        $this->rowNumber++;
        $paymentDate = $transaction->paymentDate
            ? Carbon::parse($transaction->paymentDate)->format('d-m-Y')
            : '-';

        $bankInfo = '-';
        if ($transaction->userBank) {
            $bankName = $transaction->userBank->bank->name ?? 'Bank';
            $accNo = $transaction->userBank->accountNumber ?? '';
            $accName = $transaction->userBank->accountName ?? '';
            $bankInfo = trim($bankName.($accNo ? ' - '.$accNo : '').($accName ? ' ('.$accName.')' : ''));
        }

        $invoices = $transaction->involvedInvoices();
        $invoiceNumbers = $invoices->map(fn ($inv) => $inv->invoiceNumber ?: $inv->code)->filter()->implode(', ') ?: '-';

        if ($invoices->isNotEmpty() && $invoices->every(fn ($inv) => (int) ($inv->status ?? Invoice::STATUS_CREATE) === Invoice::STATUS_FULL)) {
            $statusLabel = 'Selesai';
        } elseif ($invoices->isNotEmpty()) {
            $statusLabel = 'Sebagian';
        } else {
            $statusLabel = 'Draft';
        }

        return [
            $this->rowNumber,
            $paymentDate,
            $transaction->code ?: '-',
            $transaction->customer->name ?? ($transaction->customerCode ?: '-'),
            $bankInfo,
            (int) $transaction->involvedInvoiceCodes()->count(),
            $invoiceNumbers,
            (float) ($transaction->totalClaim ?? 0),
            (float) ($transaction->amount ?? 0),
            $statusLabel,
            $transaction->description ?: '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0',
            'I' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F46E5'],
                ],
            ],
        ];
    }
}
