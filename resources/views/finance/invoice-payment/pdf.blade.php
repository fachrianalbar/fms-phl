<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Calibri, sans-serif;
            font-size: 10pt;
            line-height: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .bordered,
        .bordered td,
        .bordered th {
            border: 1px solid black;
        }

        .bordered th,
        .bordered td {
            padding: 5px;
            text-align: left;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .mt-20 {
            margin-top: 20px;
        }

        th {
            font-weight: normal;
        }
    </style>
</head>

<body>

    <htmlpageheader name="page-header">
        @include('finance.invoice.pdf.header.phl')
    </htmlpageheader>

    <div style="margin-top: 10px;">
        <p class="bold">{{ $title }} Report</p>
        <p>Generated on: {{ $date->format('d F Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 10%">Invoice Code</th>
                <th style="width: 12%">Invoice No</th>
                <th style="width: 15%">Customer Name</th>
                <th style="width: 10%">Invoice Date</th>
                <th style="width: 15%">Receiving Bank</th>
                <th style="width: 12%;" class="text-right">Total Billing</th>
                <th style="width: 12%;" class="text-right">Total Payment</th>
                <th style="width: 10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
            $totalBillingSum = 0;
            $totalPaymentSum = 0;
            $no = 1;
            @endphp

            @foreach ($data as $invoice)
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

            $totalBillingSum += $totalBilling;
            $totalPaymentSum += $totalPayment;

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
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $invoice->code }}</td>
                    <td>{{ $invoice->invoiceNumber }}</td>
                    <td>{{ $invoice->customer->name ?? '-' }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($invoice->invoiceDate)->format('d-M-Y') }}</td>
                    <td>{{ $receivingBank }}</td>
                    <td class="text-right">Rp {{ number_format($totalBilling, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalPayment, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if ($status === 'Full Payment')
                        <span
                            style="background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px;">{{ $status }}</span>
                        @elseif ($status === 'Partial Payment')
                        <span
                            style="background-color: #ffc107; color: black; padding: 2px 6px; border-radius: 3px;">{{ $status }}</span>
                        @else
                        <span
                            style="background-color: #6c757d; color: white; padding: 2px 6px; border-radius: 3px;">{{ $status }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
        </tbody>
    </table>


    <div class="summary">
        <div class="summary-item">
            <span>Total Billing:</span>
            <span>Rp {{ number_format($totalBillingSum, 0, ',', '.') }}</span>
        </div>
        <div class="summary-item">
            <span>Total Payment:</span>
            <span>Rp {{ number_format($totalPaymentSum, 0, ',', '.') }}</span>
        </div>
        <div class="summary-item">
            <span>Remaining:</span>
            <span>Rp {{ number_format($totalBillingSum - $totalPaymentSum, 0, ',', '.') }}</span>
        </div>
    </div>
</body>

</html>