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
            <th style="width: 4%;">No</th>
            <th style="width: 13%;">Transaction Code</th>
            <th style="width: 13%;">Invoice No</th>
            <th style="width: 16%;">Customer Name</th>
            <th style="width: 9%;">Payment Date</th>
            <th style="width: 15%;">Receiving Bank</th>
            <th style="width: 9%;">Type</th>
            <th style="width: 10%;">Amount</th>
            <th style="width: 11%;">Description</th>
        </tr>
    </thead>
    <tbody>
