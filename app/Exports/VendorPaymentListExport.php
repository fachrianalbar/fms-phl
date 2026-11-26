<?php

namespace App\Exports;

use App\Services\Finance\VendorPaymentService;
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

class VendorPaymentListExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected VendorPaymentService $service
    ) {}

    public function collection(): Collection
    {
        return $this->service->findPayments($this->filters);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Pembayaran',
            'Kode Transaksi',
            'No. Nota',
            'Jumlah Order',
            'Vendor',
            'Nominal',
            'Bank Sumber Dana',
            'Keterangan',
        ];
    }

    public function map($transaction): array
    {
        $this->rowNumber++;
        $paymentDate = $transaction->payment_date
            ? Carbon::parse($transaction->payment_date)->format('d-m-Y')
            : '-';
        $code = $transaction->batch_code ?: ($transaction->legacy_code ?: '-');
        $notaNumbers = $transaction->notas->pluck('number')->filter()->implode(', ') ?: '-';
        $vendors = $transaction->vendors->implode(', ') ?: '-';

        $bankInfo = '-';
        if ($transaction->userBank) {
            $bankName = $transaction->userBank->bank->name ?? 'Bank';
            $accountNumber = $transaction->userBank->accountNumber ?? '';
            $accountName = $transaction->userBank->accountName ?? '';
            $bankInfo = trim($bankName.($accountNumber ? ' - '.$accountNumber : '').($accountName ? ' ('.$accountName.')' : ''));
        }

        return [
            $this->rowNumber,
            $paymentDate,
            $code,
            $notaNumbers,
            (int) ($transaction->order_count ?? 0),
            $vendors,
            (float) ($transaction->amount ?? 0),
            $bankInfo,
            $transaction->description ?: '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
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
