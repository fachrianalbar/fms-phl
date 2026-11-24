<?php

namespace App\Exports;

use App\Support\ReceivableAging;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReceivableAgingReport implements FromView, ShouldAutoSize
{
    use Exportable;

    public const STATUS_OUTSTANDING = 'outstanding';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_ALL = 'all';

    protected $request;

    /**
     * @param  mixed  $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $request = $this->request;
        $customerCode = $request->filled('customerCode') ? $request->customerCode : null;
        $status = self::normalizeStatus($request->status ?? null);
        $rows = self::customerRows($request->startDate, $request->endDate, $customerCode, $status);

        return view('report.receivable-aging.report.aging-excel')
            ->with('rows', $rows)
            ->with('customerName', optional($rows->first())->customerName)
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate)
            ->with('status', $status);
    }

    public static function normalizeStatus(?string $status): string
    {
        return in_array($status, [self::STATUS_OUTSTANDING, self::STATUS_OVERDUE, self::STATUS_ALL], true)
            ? $status
            : self::STATUS_OUTSTANDING;
    }

    /**
     * Satu baris per faktur dengan angka billing, pembayaran, claim, sisa, dan umur.
     *
     * @return Collection<int, object>
     */
    public static function invoiceRows(
        ?string $startDate,
        ?string $endDate,
        ?string $customerCode = null,
        ?string $status = self::STATUS_OUTSTANDING
    ): Collection {
        $status = self::normalizeStatus($status);
        $billing = self::billingExpression();
        $paid = 'COALESCE(payments.paidAmount, 0)';
        $claim = 'COALESCE(claims.claimAmount, 0)';
        $remaining = "GREATEST({$billing} - {$paid} - {$claim}, 0)";

        $query = DB::table('invoice')
            ->leftJoin('customer', 'customer.code', '=', 'invoice.customerCode')
            ->leftJoinSub(self::paymentAggregate(), 'payments', 'payments.invoiceCode', '=', 'invoice.code')
            ->leftJoinSub(self::claimAggregate(), 'claims', 'claims.invoiceCode', '=', 'invoice.code')
            ->whereNull('invoice.deleted_at')
            ->select([
                'invoice.id',
                'invoice.code',
                'invoice.invoiceNumber',
                'invoice.invoiceDate',
                'invoice.overdueDate',
                'invoice.customerCode',
                DB::raw('COALESCE(customer.name, invoice.customerCode) as customerName'),
                DB::raw('COALESCE(customer.dueDateDuration, 0) as dueDateDuration'),
                DB::raw("{$billing} as billingAmount"),
                DB::raw("{$paid} as paidAmount"),
                DB::raw("{$claim} as claimAmount"),
                DB::raw("{$remaining} as remainingAmount"),
            ])
            ->orderBy('invoice.invoiceDate')
            ->orderBy('invoice.code');

        if ($customerCode) {
            $query->where('invoice.customerCode', $customerCode);
        }

        if ($status === self::STATUS_OUTSTANDING || $status === self::STATUS_OVERDUE) {
            $query->whereRaw("{$remaining} > 0");
        }

        self::applyDateFilter($query, $startDate, $endDate);

        $today = Carbon::today();

        $rows = $query->get()->map(function ($row) use ($today) {
            $row->customerCode = $row->customerCode ?: '';
            $row->customerName = $row->customerName ?: ($row->customerCode ?: '-');
            $row->billingAmount = (float) $row->billingAmount;
            $row->paidAmount = (float) $row->paidAmount;
            $row->claimAmount = (float) $row->claimAmount;
            $row->remainingAmount = (float) $row->remainingAmount;

            $dueDate = ReceivableAging::effectiveDueDate(
                $row->overdueDate,
                $row->invoiceDate,
                (int) $row->dueDateDuration,
                $today
            );

            $row->dueDate = $dueDate;
            $row->daysToDue = ReceivableAging::daysToDue($dueDate, $today);
            $row->overdueDays = max(-$row->daysToDue, 0);
            $row->bucket = ReceivableAging::bucket($row->daysToDue);
            $row->bucketLabel = ReceivableAging::bucketLabel($row->bucket);

            return $row;
        });

        if ($status === self::STATUS_OVERDUE) {
            $rows = $rows->filter(fn ($row) => $row->remainingAmount > 0 && $row->daysToDue < 0);
        }

        return $rows->values();
    }

    /**
     * Agregat per customer dari baris faktur yang sama dengan halaman detail.
     *
     * @return Collection<int, object>
     */
    public static function customerRows(
        ?string $startDate,
        ?string $endDate,
        ?string $customerCode = null,
        ?string $status = self::STATUS_OUTSTANDING
    ): Collection {
        return self::invoiceRows($startDate, $endDate, $customerCode, $status)
            ->groupBy('customerCode')
            ->map(function (Collection $rows, $customerCode) {
                $summary = (object) [
                    'customerCode' => $customerCode,
                    'customerName' => $rows->first()->customerName,
                    'invoiceCount' => $rows->count(),
                    'unpaidCount' => 0,
                    'totalBilling' => 0.0,
                    'totalPaid' => 0.0,
                    'totalClaim' => 0.0,
                    'totalRemaining' => 0.0,
                    'notDueAmount' => 0.0,
                    'dueSoonAmount' => 0.0,
                    'dueSoonCount' => 0,
                    'overdueAmount' => 0.0,
                    'overdueCount' => 0,
                    'aging0To30' => 0.0,
                    'aging31To60' => 0.0,
                    'aging61To90' => 0.0,
                    'agingOver90' => 0.0,
                    'maxOverdueDays' => 0,
                ];

                foreach ($rows as $row) {
                    $summary->totalBilling += $row->billingAmount;
                    $summary->totalPaid += $row->paidAmount;
                    $summary->totalClaim += $row->claimAmount;
                    $summary->totalRemaining += $row->remainingAmount;

                    if ($row->remainingAmount <= 0) {
                        continue;
                    }

                    $summary->unpaidCount++;

                    switch ($row->bucket) {
                        case ReceivableAging::NOT_DUE:
                            $summary->notDueAmount += $row->remainingAmount;
                            break;
                        case ReceivableAging::DUE_SOON:
                            $summary->dueSoonAmount += $row->remainingAmount;
                            $summary->dueSoonCount++;
                            break;
                        case ReceivableAging::D0_30:
                            $summary->aging0To30 += $row->remainingAmount;
                            break;
                        case ReceivableAging::D31_60:
                            $summary->aging31To60 += $row->remainingAmount;
                            break;
                        case ReceivableAging::D61_90:
                            $summary->aging61To90 += $row->remainingAmount;
                            break;
                        case ReceivableAging::OVER_90:
                            $summary->agingOver90 += $row->remainingAmount;
                            break;
                    }

                    if ($row->daysToDue < 0) {
                        $summary->overdueAmount += $row->remainingAmount;
                        $summary->overdueCount++;
                        $summary->maxOverdueDays = max($summary->maxOverdueDays, $row->overdueDays);
                    }
                }

                return $summary;
            })
            ->sortBy(fn ($row) => mb_strtolower((string) $row->customerName))
            ->values();
    }

    /**
     * Detail faktur untuk satu customer, diurutkan berdasarkan tanggal faktur
     * lalu sisa umur (paling lama lebih dulu).
     *
     * @return Collection<int, object>
     */
    public static function invoiceRowsForCustomer(
        string $customerCode,
        ?string $startDate,
        ?string $endDate,
        ?string $status = self::STATUS_OUTSTANDING
    ): Collection {
        return self::invoiceRows($startDate, $endDate, $customerCode, $status)
            ->sortBy([
                fn ($a, $b) => strcmp((string) $a->invoiceDate, (string) $b->invoiceDate),
                fn ($a, $b) => $a->daysToDue <=> $b->daysToDue,
            ])
            ->values();
    }

    private static function paymentAggregate(): QueryBuilder
    {
        return DB::table('invoice_payment')
            ->whereNull('deleted_at')
            ->whereNotNull('invoiceCode')
            ->groupBy('invoiceCode')
            ->selectRaw('invoiceCode')
            ->selectRaw('COALESCE(SUM(amount), 0) as paidAmount');
    }

    private static function claimAggregate(): QueryBuilder
    {
        return DB::table('invoice_payment_claim')
            ->whereNull('deleted_at')
            ->whereNotNull('invoiceCode')
            ->groupBy('invoiceCode')
            ->selectRaw('invoiceCode')
            ->selectRaw('COALESCE(SUM(amount), 0) as claimAmount');
    }

    private static function billingExpression(): string
    {
        return '(COALESCE(invoice.invoiceAmount, 0) + COALESCE(invoice.ppnAmount, 0) - COALESCE(invoice.pphAmount, 0))';
    }

    private static function applyDateFilter(QueryBuilder $query, ?string $startDate, ?string $endDate): void
    {
        if ($startDate && $endDate && $startDate === $endDate) {
            $query->whereDate('invoice.invoiceDate', '=', $startDate);

            return;
        }

        if ($startDate) {
            $query->whereDate('invoice.invoiceDate', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('invoice.invoiceDate', '<=', $endDate);
        }
    }
}
