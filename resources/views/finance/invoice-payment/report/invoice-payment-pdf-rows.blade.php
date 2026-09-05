@php
use Carbon\Carbon;
@endphp
@foreach($data as $payment)
@php
$invoice = $payment->invoice;
$bank = '-';
if ($payment->userBank) {
    $bank = ($payment->userBank->bank->name ?? 'Bank') . ' - ' . ($payment->userBank->accountNumber ?? '');
}
@endphp
    <tr>
        <td class="text-center">{{ $start + $loop->iteration }}</td>
        <td>{{ $payment->transactionCode ?: $payment->code }}</td>
        <td>{{ $invoice ? ($invoice->invoiceNumber ?: $invoice->code) : '-' }}</td>
        <td>{{ $invoice->customer->name ?? '-' }}</td>
        <td class="text-center">{{ $payment->paymentDate ? Carbon::parse($payment->paymentDate)->format('d-M-Y') : '-' }}</td>
        <td>{{ $bank }}</td>
        <td class="text-center">{{ $labels[$payment->id] ?? 'Pembayaran' }}</td>
        <td class="text-right">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
        <td>{{ $payment->description ?: '-' }}</td>
    </tr>
    @endforeach
