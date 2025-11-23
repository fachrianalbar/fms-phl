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
                <th colspan="10" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    Invoice Payment Report Data</th>
            </tr>
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Invoice Code</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Invoice No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Customer Name</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Invoice Date</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Receiving Bank</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Total Billing</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Total Payment</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Last Payment Date</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
            use Carbon\Carbon;
            $no = 1;
            @endphp
            @foreach ($data as $invoice)
            @php
            $totalBilling = (float) ($invoice->invoiceAmount ?? 0) + (float) ($invoice->ppnAmount ?? 0);
            $totalPayment = 0;
            $receivingBank = '';
            $lastPaymentDate = '';

            if (count($invoice->payments) > 0) {
            foreach ($invoice->payments as $item) {
            $totalPayment += $item->amount;
            }
            $lastPayment = $invoice->payments->last();
            if ($lastPayment && $lastPayment->userBank) {
            $receivingBank = $lastPayment->userBank->bank->name . ' - ' . $lastPayment->userBank->accountNumber;
            $lastPaymentDate = Carbon::parse($lastPayment->paymentDate)->format('d-M-Y');
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
                    <td style="text-align: center">{{ $no++ }}</td>
                    <td style="text-align: center">{{ $invoice->code }}</td>
                    <td style="text-align: center">{{ $invoice->invoiceNumber }}</td>
                    <td style="text-align: center">{{ $invoice->customer->name ?? '' }}</td>
                    <td style="text-align: center">{{ Carbon::parse($invoice->invoiceDate)->format('d-M-Y') }}</td>
                    <td style="text-align: center">{{ $receivingBank }}</td>
                    <td style="text-align: center">{{ number_format($totalBilling, 0, ',', '.') }}</td>
                    <td style="text-align: center">{{ number_format($totalPayment, 0, ',', '.') }}</td>
                    <td style="text-align: center">{{ $lastPaymentDate }}</td>
                    <td style="text-align: center">{{ $status }}</td>
                </tr>
                @endforeach
        </tbody>
    </table>

</body>

</html>