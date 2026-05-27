                @if ($totalQty > 0 || $totalCost > 0)
                    <tr class="bold">
                        <td colspan="7" class="text-right">TOTAL</td>
                        <td class="text-right">{{ number_format($totalQty, 1, ',', '.') }}</td>
                        <td></td>
                        <td class="text-right">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>

</html>
