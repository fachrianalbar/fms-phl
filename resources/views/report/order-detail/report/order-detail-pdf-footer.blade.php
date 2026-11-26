            @php
                $summaryTotals = $totals ?? [];
                if (empty($summaryTotals) && isset($data) && is_iterable($data)) {
                    $service = app(\App\Services\Report\OrderDetailReportService::class);
                    $items = is_array($data) ? $data : collect($data)->all();
                    $prepared = ! empty($items) && $items[0] instanceof \App\Models\Operational\Order
                        ? $service->prepareExportRows($items)
                        : $items;
                    $summaryTotals = $service->computeGrandTotals($prepared);
                }
            @endphp
            <tr>
                <th colspan="10" class="text-right">TOTAL</th>
                <th class="text-right">{{ number_format((float) ($summaryTotals['omset'] ?? 0), 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format((float) ($summaryTotals['income_total'] ?? 0), 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format((float) ($summaryTotals['commission_total'] ?? 0), 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format((float) ($summaryTotals['salary_total'] ?? 0), 0, ',', '.') }}</th>
                <th></th>
                <th class="text-right">{{ number_format((float) ($summaryTotals['total_cost'] ?? 0), 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format((float) ($summaryTotals['gross_profit'] ?? 0), 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format((float) ($summaryTotals['tax'] ?? 0), 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format((float) ($summaryTotals['net_profit'] ?? 0), 0, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>
</body>

</html>
