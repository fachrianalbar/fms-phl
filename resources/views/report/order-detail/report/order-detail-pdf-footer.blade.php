            @php
            $grandTotalSales = 0;
            $grandTotalCost = 0;
            $grandTotalProfit = 0;
            foreach ($data as $item) {
                // `routeAmount` stored as total for the order
                $sales = $item->routeAmount;
            $grandTotalSales += $sales;

            $totalCost = 0;
            if ($item->cost) {
            foreach ($item->cost as $cost) {
            $totalCost += $cost->nominal;
            }
            }
            $grandTotalCost += $totalCost;

            $profit = $sales - $totalCost;
            $grandTotalProfit += $profit;
            }
            @endphp
            <tr>
                <th colspan="8" class="text-right">TOTAL</th>
                <th class="text-right">{{ number_format($grandTotalSales, 0, ',', '.') }}</th>
                <th></th>
                <th class="text-right">{{ number_format($grandTotalCost, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($grandTotalProfit, 0, ',', '.') }}</th>
            </tr>
            </tbody>
            </table>
            </body>

            </html>