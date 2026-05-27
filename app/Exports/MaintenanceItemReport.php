<?php

namespace App\Exports;

use App\Models\Warehouse\MaintenanceDetail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class MaintenanceItemReport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    protected $details;

    public function __construct($detailsOrRequest)
    {
        if ($detailsOrRequest instanceof Request) {
            $query = MaintenanceDetail::query()
                ->select('maintenance_detail.*')
                ->join('maintenance', 'maintenance.code', '=', 'maintenance_detail.maintenanceCode')
                ->leftJoin('fleet', 'fleet.code', '=', 'maintenance.fleetCode')
                ->leftJoin('warehouse', 'warehouse.code', '=', 'maintenance.warehouseCode')
                ->leftJoin('item', 'item.code', '=', 'maintenance_detail.itemCode')
                ->with([
                    'maintenance',
                    'maintenance.fleet',
                    'maintenance.warehouse',
                    'item'
                ])
                ->whereNull('maintenance.deleted_at')
                ->whereNull('maintenance_detail.deleted_at');

            if ($detailsOrRequest->filled('startDate')) {
                $query->whereDate('maintenance.date', '>=', $detailsOrRequest->startDate);
            }

            if ($detailsOrRequest->filled('endDate')) {
                $query->whereDate('maintenance.date', '<=', $detailsOrRequest->endDate);
            }

            if ($detailsOrRequest->filled('fleetCode')) {
                $query->where('maintenance.fleetCode', $detailsOrRequest->fleetCode);
            }

            if ($detailsOrRequest->filled('warehouseCode')) {
                $query->where('maintenance.warehouseCode', $detailsOrRequest->warehouseCode);
            }

            if ($detailsOrRequest->filled('itemCode')) {
                $query->where('maintenance_detail.itemCode', $detailsOrRequest->itemCode);
            }

            $this->details = $query->orderBy('maintenance.date', 'desc')
                ->orderBy('maintenance.time', 'desc')
                ->get();
        } else {
            $this->details = $detailsOrRequest;
        }
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0.0',
            'I' => '#,##0',
            'J' => '#,##0',
        ];
    }

    public function title(): string
    {
        return 'Maintenance Item Report';
    }

    public function view(): View
    {
        return view('report.maintenance-item.report.maintenance-item-excel')
            ->with('details', $this->details);
    }
}
