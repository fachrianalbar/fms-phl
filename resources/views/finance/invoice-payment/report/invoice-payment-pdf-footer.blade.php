@php
$totalBillingSum = 0;
$totalPaymentSum = 0;
@endphp
@foreach ($data as $invoice)
@php
$totalBilling = (float) ($invoice->invoiceAmount ?? 0) + (float) ($invoice->ppnAmount ?? 0);
$totalPayment = 0;
if (count($invoice->payments) > 0) {
foreach ($invoice->payments as $item) {
$totalPayment += $item->amount;
}
}
$totalBillingSum += $totalBilling;
$totalPaymentSum += $totalPayment;
@endphp
@endforeach

</tbody>
</table>

<table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
    <tr>
        <td style="width: 80%; text-align: right; padding: 5px;">Total Billing:</td>
        <td style="width: 20%; text-align: right; padding: 5px;">Rp {{ number_format($totalBillingSum, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td style="width: 80%; text-align: right; padding: 5px;">Total Payment:</td>
        <td style="width: 20%; text-align: right; padding: 5px;">Rp {{ number_format($totalPaymentSum, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td style="width: 80%; text-align: right; padding: 5px;">Remaining:</td>
        <td style="width: 20%; text-align: right; padding: 5px;">Rp {{ number_format($totalBillingSum - $totalPaymentSum, 0, ',', '.') }}</td>
    </tr>
</table>