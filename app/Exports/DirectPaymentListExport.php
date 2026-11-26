<?php

namespace App\Exports;

use App\Services\Finance\DirectPaymentService;
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

class DirectPaymentListExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected DirectPaymentService $service
    ) {}

    public function collection(): Collection
    {
        return $this->service->findPayments($this->filters);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Bayar',
            'Kode Transaksi',
            'No. Nota / Order',
            'Jumlah Order',
            'Customer',
            'Nominal',
            'Bank Sumber Dana',
            'Keterangan',
        ];
    }

    public function map($tx): array
    {
        $this->rowNumber++;
        $paymentDate = $tx->payment_date ? Carbon::parse($tx->payment_date)->format('d-m-Y') : '-';
        $code = $tx->batch_code ?: ($tx->legacy_code ?: '-');
        $notaNumbers = $tx->notas->pluck('number')->filter()->implode(', ') ?: '-';
        $customers = $tx->customers->implode(', ') ?: '-';

        $bankInfo = '-';
        if ($tx->userBank) {
            $bankName = $tx->userBank->bank->name ?? 'Bank';
            $accNo = $tx->userBank->accountNumber ?? '';
            $accName = $tx->userBank->accountName ?? '';
            $bankInfo = trim($bankName.($accNo ? ' - '.$accNo : '').($accName ? ' ('.$accName.')' : ''));
        }

        return [
            $this->rowNumber,
            $paymentDate,
            $code,
            $notaNumbers,
            (int) ($tx->order_count ?? 0),
            $customers,
            (float) ($tx->amount ?? 0),
            $bankInfo,
            $tx->description ?: '-',
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
