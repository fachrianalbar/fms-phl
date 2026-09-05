@php
$totalPaymentSum = 0;
@endphp
@foreach ($data as $payment)
@php
$totalPaymentSum += (float) $payment->amount;
@endphp
@endforeach

</tbody>
</table>

<table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
    <tr>
        <td style="width: 80%; text-align: right; padding: 5px;">Total Pembayaran ({{ number_format(count($data)) }}x):</td>
        <td style="width: 20%; text-align: right; padding: 5px;">Rp {{ number_format($totalPaymentSum, 0, ',', '.') }}</td>
    </tr>
</table>
