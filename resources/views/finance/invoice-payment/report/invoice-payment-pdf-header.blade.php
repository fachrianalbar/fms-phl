<table style="width:100%; border-bottom: 2px solid #000; margin-bottom: 10px;">
    <tr>
        <td style="width: 80%;">
            <h3>{{ $title ?? 'Invoice Payment Report' }}</h3>
            <p>Generated on: {{ $date->format('d F Y H:i:s') }}</p>
        </td>
        <td style="text-align: right; width: 20%;">
            <p>&nbsp;</p>
        </td>
    </tr>
</table>

<table class="bordered">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 12%;">Invoice Code</th>
            <th style="width: 12%;">Invoice No</th>
            <th style="width: 20%;">Customer Name</th>
            <th style="width: 10%;">Invoice Date</th>
            <th style="width: 20%;">Receiving Bank</th>
            <th style="width: 10%;">Total Billing</th>
            <th style="width: 10%;">Total Payment</th>
            <th style="width: 10%;">Status</th>
        </tr>
    </thead>
    <tbody>