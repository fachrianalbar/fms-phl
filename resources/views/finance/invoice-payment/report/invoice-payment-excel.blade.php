<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice Payment Report Data</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="9" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    Invoice Payment Report Data</th>
            </tr>
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Transaction Code</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Invoice No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Customer Name</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Payment Date</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Receiving Bank</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Type</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Amount</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Description</th>
            </tr>
        </thead>
        <tbody>
            @php
            use Carbon\Carbon;
            $no = 1;
            @endphp
            @foreach ($data as $payment)
            @php
            $invoice = $payment->invoice;
            $bank = '-';
            if ($payment->userBank) {
                $bank = ($payment->userBank->bank->name ?? 'Bank') . ' - ' . ($payment->userBank->accountNumber ?? '');
            }
            @endphp

                <tr>
                    <td style="text-align: center">{{ $no++ }}</td>
                    <td style="text-align: center">{{ $payment->transactionCode ?: $payment->code }}</td>
                    <td style="text-align: center">{{ $invoice ? ($invoice->invoiceNumber ?: $invoice->code) : '-' }}</td>
                    <td style="text-align: center">{{ $invoice->customer->name ?? '' }}</td>
                    <td style="text-align: center">{{ $payment->paymentDate ? Carbon::parse($payment->paymentDate)->format('d-M-Y') : '-' }}</td>
                    <td style="text-align: center">{{ $bank }}</td>
                    <td style="text-align: center">{{ $labels[$payment->id] ?? 'Pembayaran' }}</td>
                    <td style="text-align: center">{{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                    <td style="text-align: center">{{ $payment->description ?: '-' }}</td>
                </tr>
                @endforeach
        </tbody>
    </table>

</body>

</html>
