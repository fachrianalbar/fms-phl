            @php
            $grandTotalSales = 0;
            $grandTotalIncome = 0;
            $grandTotalCost = 0;
            $grandTotalProfit = 0;
            foreach ($data as $item) {
                $sales = $item->routeAmount;
                $grandTotalSales += $sales;

                $incomeTotal = 0;
                $totalCost = 0;
                if ($item->cost) {
                    foreach ($item->cost as $cost) {
                        if (strtolower($cost->type) === 'on charge') {
                            $incomeTotal += $cost->nominal;
                        } else {
                            $totalCost += $cost->nominal;
                        }
                    }
                }
                $grandTotalIncome += $incomeTotal;
                $grandTotalCost += $totalCost;

                $profit = $sales + $incomeTotal - $totalCost;
                $grandTotalProfit += $profit;
            }
            @endphp
            <tr>
                <th colspan="8" class="text-right">TOTAL</th>
                <th class="text-right">{{ number_format($grandTotalSales, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($grandTotalIncome, 0, ',', '.') }}</th>
                <th></th>
                <th class="text-right">{{ number_format($grandTotalCost, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($grandTotalProfit, 0, ',', '.') }}</th>
            </tr>
            </tbody>
            </table>
            </body>

            </html>