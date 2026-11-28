<?php

namespace App\Services\Report;

use App\Models\Warehouse\Maintenance;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Status pembayaran, jatuh tempo, dan utang pemakaian item maintenance.
 *
 * Satu maintenance bisa tertaut ke beberapa PO (maintenance_purchase) dan satu PO
 * bisa dipakai beberapa maintenance. Agar utang tidak terhitung ganda, status
 * dihitung per baris maintenance_detail: baris dicocokkan ke PO tertaut yang
 * memuat item yang sama (purchase_detail.itemCode), lalu utang baris =
 * subtotal baris x (sisa PO / nominal PO).
 */
class MaintenancePaymentStatusService
{
    public const STATUS_PAID = 'lunas';

    public const STATUS_UNPAID = 'belum';

    public const STATUS_NO_PO = 'none';

    public const STATUS_LABELS = [
        self::STATUS_PAID => 'Lunas',
        self::STATUS_UNPAID => 'Belum',
        self::STATUS_NO_PO => 'Tanpa PO',
    ];

    public const LINE_AMOUNT = 'COALESCE(maintenance_detail.price, 0) * COALESCE(maintenance_detail.qty, 0)';

    public const SUPPLIER_CODE = 'COALESCE(mps.supplierCode, item.supplierCode)';

    public const DUE_DATE = 'COALESCE(mps.nextDueDate, mps.lastDueDate)';

    public const PAYMENT_STATUS = "CASE WHEN mps.poCount IS NULL THEN 'none' WHEN mps.poRemaining > 0 THEN 'belum' ELSE 'lunas' END";

    public const LINE_DEBT = 'CASE WHEN mps.poNominal > 0 THEN ('.self::LINE_AMOUNT.') * mps.poRemaining / mps.poNominal ELSE 0 END';

    public const LINE_DUE_DEBT = 'CASE WHEN mps.poNominal > 0 THEN ('.self::LINE_AMOUNT.') * mps.poRemainingDue / mps.poNominal ELSE 0 END';

    /**
     * Agregat PO per baris maintenance_detail.
     */
    public function lineStatusSubquery(): QueryBuilder
    {
        $remaining = "CASE WHEN p.paymentStatus = 'Paid' THEN 0 ELSE GREATEST(COALESCE(p.nominal, 0) - COALESCE(p.paidAmount, 0), 0) END";

        return DB::table('maintenance_detail as md')
            ->join('maintenance as m', 'm.code', '=', 'md.maintenanceCode')
            ->join('maintenance_purchase as mp', 'mp.maintenance_id', '=', 'm.id')
            ->join('purchase as p', function (JoinClause $join) {
                $join->on('p.id', '=', 'mp.purchase_id')->whereNull('p.deleted_at');
            })
            ->whereNull('md.deleted_at')
            ->whereExists(function (QueryBuilder $query) {
                $query->select(DB::raw(1))
                    ->from('purchase_detail as pd')
                    ->whereColumn('pd.purchaseCode', 'p.code')
                    ->whereColumn('pd.itemCode', 'md.itemCode')
                    ->whereNull('pd.deleted_at');
            })
            ->groupBy('md.id')
            ->select([
                'md.id as detailId',
                DB::raw('MIN(p.supplierCode) as supplierCode'),
                DB::raw('SUM(COALESCE(p.nominal, 0)) as poNominal'),
                DB::raw("SUM({$remaining}) as poRemaining"),
                DB::raw("SUM(CASE WHEN p.dueDate <= CURRENT_DATE THEN {$remaining} ELSE 0 END) as poRemainingDue"),
                DB::raw("MIN(CASE WHEN ({$remaining}) > 0 THEN p.dueDate END) as nextDueDate"),
                DB::raw('MAX(p.dueDate) as lastDueDate'),
                DB::raw('COUNT(DISTINCT p.id) as poCount'),
            ]);
    }

    /**
     * Query dasar maintenance aktif + detail + item + status PO + supplier efektif.
     */
    public function baseQuery(bool $requireDetail = false): Builder
    {
        $query = Maintenance::query()
            ->{$requireDetail ? 'join' : 'leftJoin'}('maintenance_detail', function (JoinClause $join) {
                $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                    ->whereNull('maintenance_detail.deleted_at');
            })
            ->leftJoin('item', function (JoinClause $join) {
                $join->on('item.code', '=', 'maintenance_detail.itemCode')
                    ->whereNull('item.deleted_at');
            })
            ->leftJoinSub($this->lineStatusSubquery(), 'mps', 'mps.detailId', '=', 'maintenance_detail.id')
            ->leftJoin('supplier', function (JoinClause $join) {
                $join->on(DB::raw(self::SUPPLIER_CODE), '=', 'supplier.code')
                    ->whereNull('supplier.deleted_at');
            })
            ->where('maintenance.status', 0)
            ->whereNull('maintenance.deleted_at');

        return $query;
    }

    public function joinFleet(Builder $query, bool $inner = true): Builder
    {
        return $query
            ->{$inner ? 'join' : 'leftJoin'}('fleet', function (JoinClause $join) {
                $join->on('fleet.code', '=', 'maintenance.fleetCode')
                    ->whereNull('fleet.deleted_at');
            })
            ->leftJoin('fleet_company', function (JoinClause $join) {
                $join->on('fleet_company.code', '=', 'fleet.fleetCompanyCode')
                    ->whereNull('fleet_company.deleted_at');
            });
    }

    /**
     * Kolom agregat standar untuk halaman depan & KPI.
     *
     * @return array<int, Expression>
     */
    public function aggregateSelects(): array
    {
        return [
            DB::raw('COUNT(DISTINCT maintenance.code) as totalMaintenance'),
            DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
            DB::raw('COALESCE(SUM('.self::LINE_AMOUNT.'), 0) as totalCost'),
            DB::raw('COALESCE(SUM('.self::LINE_DUE_DEBT.'), 0) as utangJatuhTempo'),
            DB::raw('COALESCE(SUM('.self::LINE_DEBT.'), 0) as totalUtang'),
        ];
    }

    /**
     * Kolom per baris pemakaian item untuk halaman detail.
     *
     * @return array<int, mixed>
     */
    public function lineSelects(): array
    {
        return [
            'maintenance.code',
            'maintenance.date',
            'maintenance.time',
            'maintenance_detail.itemCode',
            'item.name as itemName',
            'maintenance_detail.qty',
            'maintenance_detail.price',
            'maintenance_detail.description',
            DB::raw(self::LINE_AMOUNT.' as total'),
            DB::raw(self::SUPPLIER_CODE.' as supplierCode'),
            'supplier.name as supplierName',
            DB::raw(self::DUE_DATE.' as dueDate'),
            DB::raw(self::PAYMENT_STATUS.' as paymentStatus'),
        ];
    }

    /**
     * Ringkasan KPI dari query baris yang sudah difilter.
     *
     * @return array{totalMaintenance: int, totalQty: float, totalCost: float, utangJatuhTempo: float, totalUtang: float}
     */
    public function summarize(Builder $query): array
    {
        $row = (clone $query)->select($this->aggregateSelects())->toBase()->first();

        return [
            'totalMaintenance' => (int) ($row->totalMaintenance ?? 0),
            'totalQty' => (float) ($row->totalQty ?? 0),
            'totalCost' => (float) ($row->totalCost ?? 0),
            'utangJatuhTempo' => (float) ($row->utangJatuhTempo ?? 0),
            'totalUtang' => (float) ($row->totalUtang ?? 0),
        ];
    }

    /**
     * Halaman depan Perawatan Per Armada (group per armada).
     *
     * @param  array<string, mixed>  $filters  fleetCode, fleetCompanyCode, startDate, endDate
     */
    public function fleetSummaryQuery(array $filters): Builder
    {
        $query = $this->joinFleet($this->baseQuery())
            ->select(array_merge([
                'fleet.code as fleetCode',
                'fleet.plateNumber',
                'fleet_company.name as fleetCompanyName',
                'fleet_company.type as fleetCompanyType',
            ], $this->aggregateSelects()))
            ->groupBy('fleet.code', 'fleet.plateNumber', 'fleet_company.name', 'fleet_company.type');

        if (! empty($filters['fleetCode'])) {
            $query->where('fleet.code', $filters['fleetCode']);
        }

        if (! empty($filters['fleetCompanyCode'])) {
            $query->where('fleet_company.code', $filters['fleetCompanyCode']);
        }

        $this->applyDateFilter($query, $filters['startDate'] ?? null, $filters['endDate'] ?? null);

        return $query;
    }

    /**
     * Baris pemakaian item untuk satu armada (tanpa select).
     *
     * @param  array<string, mixed>  $filters  supplierCode, startDate, endDate
     */
    public function fleetDetailQuery(string $fleetCode, array $filters): Builder
    {
        $query = $this->baseQuery(true)->where('maintenance.fleetCode', $fleetCode);

        if (! empty($filters['supplierCode'])) {
            $this->whereSupplier($query, $filters['supplierCode']);
        }

        $this->applyDateFilter($query, $filters['startDate'] ?? null, $filters['endDate'] ?? null);

        return $query;
    }

    /**
     * Halaman depan Perawatan Per Suplier (group per supplier efektif).
     *
     * @param  array<string, mixed>  $filters  supplierCode, startDate, endDate
     */
    public function supplierSummaryQuery(array $filters): Builder
    {
        $query = $this->baseQuery(true)
            ->select(array_merge([
                'supplier.code as supplierCode',
                'supplier.name as supplierName',
            ], $this->aggregateSelects()))
            ->whereNotNull('supplier.code')
            ->groupBy('supplier.code', 'supplier.name');

        if (! empty($filters['supplierCode'])) {
            $query->where('supplier.code', $filters['supplierCode']);
        }

        $this->applyDateFilter($query, $filters['startDate'] ?? null, $filters['endDate'] ?? null);

        return $query;
    }

    /**
     * Baris pemakaian item untuk satu supplier, termasuk armada & perusahaan (tanpa select).
     *
     * @param  array<string, mixed>  $filters  fleetCompanyCode, fleetCode, startDate, endDate
     */
    public function supplierDetailQuery(string $supplierCode, array $filters): Builder
    {
        $query = $this->joinFleet($this->baseQuery(true), false)
            ->where('supplier.code', $supplierCode);

        if (! empty($filters['fleetCompanyCode'])) {
            $query->where('fleet_company.code', $filters['fleetCompanyCode']);
        }

        if (! empty($filters['fleetCode'])) {
            $query->where('maintenance.fleetCode', $filters['fleetCode']);
        }

        $this->applyDateFilter($query, $filters['startDate'] ?? null, $filters['endDate'] ?? null);

        return $query;
    }

    public function orderLines(Builder $query): Builder
    {
        return $query
            ->orderByDesc('maintenance.date')
            ->orderByDesc('maintenance.time')
            ->orderBy('maintenance.code')
            ->orderBy('maintenance_detail.itemCode');
    }

    public function applyDateFilter(Builder|QueryBuilder $query, ?string $startDate, ?string $endDate): void
    {
        if ($startDate && $endDate && $startDate === $endDate) {
            $query->whereDate('maintenance.date', '=', $startDate);

            return;
        }

        if ($startDate) {
            $query->whereDate('maintenance.date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('maintenance.date', '<=', $endDate);
        }
    }

    public function whereSupplier(Builder $query, string $supplierCode): void
    {
        $query->whereRaw(self::SUPPLIER_CODE.' = ?', [$supplierCode]);
    }

    /**
     * Filter global search kolom status (Lunas / Belum / Tanpa PO).
     */
    public function filterPaymentStatus(Builder $query, string $keyword): void
    {
        $keyword = mb_strtolower(trim($keyword));

        if ($keyword === '') {
            return;
        }

        if (in_array($keyword, ['-', 'tanpa', 'po', 'tanpa po'], true) || str_starts_with('tanpa po', $keyword)) {
            $query->whereNull('mps.poCount');

            return;
        }

        if (str_starts_with('lunas', $keyword)) {
            $query->whereNotNull('mps.poCount')->where('mps.poRemaining', '<=', 0);

            return;
        }

        if (str_starts_with('belum', $keyword)) {
            $query->where('mps.poRemaining', '>', 0);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    /**
     * Filter global search kolom tanggal jatuh tempo (d-m-Y / Y-m-d / tahun).
     */
    public function filterDueDate(Builder $query, string $keyword): void
    {
        $keyword = trim($keyword);

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $keyword, $m) === 1) {
            $query->whereRaw('DATE('.self::DUE_DATE.') = ?', [sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1])]);

            return;
        }

        $query->whereRaw('DATE_FORMAT('.self::DUE_DATE.", '%d-%m-%Y') LIKE ?", ["%{$keyword}%"]);
    }

    /**
     * Filter global search angka berformat Indonesia ("1.500.000", "Rp 150.000", "2,5").
     */
    public function filterNumber(Builder $query, string $column, string $keyword): void
    {
        $normalized = str_replace(',', '.', preg_replace('/[^\d,]/', '', $keyword) ?? '');

        if ($normalized === '' || ! is_numeric($normalized)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereRaw("CAST({$column} AS CHAR) LIKE ?", ["{$normalized}%"]);
    }

    public static function statusLabel(?string $status): string
    {
        return self::STATUS_LABELS[$status ?? self::STATUS_NO_PO] ?? '-';
    }

    /**
     * Badge HTML status untuk DataTable.
     */
    public static function statusBadge(?string $status): string
    {
        $class = match ($status) {
            self::STATUS_PAID => 'bg-success-subtle text-success',
            self::STATUS_UNPAID => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };

        return '<span class="badge '.$class.' px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">'
            .e(self::statusLabel($status)).'</span>';
    }
}
