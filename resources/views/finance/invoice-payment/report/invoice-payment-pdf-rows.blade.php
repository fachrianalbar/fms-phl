@php
use Carbon\Carbon;
@endphp
@foreach($data as $index => $invoice)
@php
$totalBilling = (float) ($invoice->invoiceAmount ?? 0) + (float) ($invoice->ppnAmount ?? 0);
$totalPayment = 0;
$receivingBank = '';

if (count($invoice->payments) > 0) {
foreach ($invoice->payments as $item) {
$totalPayment += $item->amount;
}
$lastPayment = $invoice->payments->last();
if ($lastPayment && $lastPayment->userBank) {
$receivingBank = $lastPayment->userBank->bank->name . ' - ' . $lastPayment->userBank->accountNumber;
}
}

$totalPaid = $totalPayment;
if ($totalPaid < $totalBilling && $totalPaid> 0) {
    $status = 'Partial Payment';
    } elseif ($totalPaid >= $totalBilling && $totalPaid > 0) {
    $status = 'Full Payment';
    } else {
    $status = 'No Payment';
    }
    @endphp
    <tr>
        <td class="text-center">{{ $start + $loop->iteration }}</td>
        <td>{{ $invoice->code }}</td>
        <td>{{ $invoice->invoiceNumber }}</td>
        <td>{{ $invoice->customer->name ?? '-' }}</td>
        <td class="text-center">{{ Carbon::parse($invoice->invoiceDate)->format('d-M-Y') }}</td>
        <td>{{ $receivingBank }}</td>
        <td class="text-right">Rp {{ number_format($totalBilling, 0, ',', '.') }}</td>
        <td class="text-right">Rp {{ number_format($totalPayment, 0, ',', '.') }}</td>
        <td class="text-center">{{ $status }}</td>
    </tr>
    @endforeach