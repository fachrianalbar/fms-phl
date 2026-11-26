<?php

namespace App\Services\Operational;

use App\Models\Operational\Order;
use App\Traits\LogActivity;
use Illuminate\Http\Request;

class ReturnDoService
{
    use LogActivity;

    protected $service;

    public function __construct(Order $notReturnDo)
    {
        $this->service = $notReturnDo;
    }

    public function findAll()
    {
        return $this->service
            ->whereIn('status', [4, 5])
            ->with([
                'driver',
                'customer',
                'route.originLocation',
                'route.destinationLocation',
                'material',
                'route.routeDetail',
                'fleet',
                'fleet.type',
                'fleet.company',
                'orderType',
            ])
            ->get();
    }

    public function datatable($request = null)
    {
        $filters = $request instanceof Request
            ? $request->all()
            : (is_array($request) ? $request : []);

        $query = $this->service
            ->whereIn('status', [4, 5])
            ->with([
                'driver',
                'customer',
                'route.originLocation',
                'route.destinationLocation',
                'material',
                'route.routeDetail',
                'fleet',
                'fleet.type',
                'fleet.company',
                'orderType',
                'onChargeCost.costComponent',
                'invoiceDetail.invoice',
            ]);

        $startDate = trim((string) ($filters['startDate'] ?? ''));
        $endDate = trim((string) ($filters['endDate'] ?? ''));
        if ($startDate !== '') {
            $query->whereDate('order.orderDate', '>=', $startDate);
        }
        if ($endDate !== '') {
            $query->whereDate('order.orderDate', '<=', $endDate);
        }

        $customerName = trim((string) ($filters['customerName'] ?? ''));
        if ($customerName !== '') {
            $query->whereHas('customer', function ($q) use ($customerName) {
                $q->where('name', 'like', "%{$customerName}%");
            });
        }

        $plateNumber = trim((string) ($filters['plateNumber'] ?? ''));
        if ($plateNumber !== '') {
            $query->wherePlateNumberLike($plateNumber);
        }

        $driverName = trim((string) ($filters['driverName'] ?? ''));
        if ($driverName !== '') {
            $query->whereDriverNameLike($driverName);
        }

        $invoiceStatus = trim((string) ($filters['invoiceStatus'] ?? ''));
        if ($invoiceStatus === 'uninvoiced') {
            // Belum ada faktur padahal order ini memang harus difaktur (customer isDo = 1).
            $query->whereDoesntHave('invoiceDetail.invoice')
                ->whereHas('customer', function ($q) {
                    $q->where('isDo', 1);
                });
        } elseif ($invoiceStatus === 'invoiced') {
            $query->whereHas('invoiceDetail.invoice');
        } elseif ($invoiceStatus === 'no_invoice') {
            // Order customer isDo = 0 (langsung cetak) tidak memakai faktur —
            // pembayarannya ditangani menu Pembayaran Langsung.
            $query->whereDoesntHave('invoiceDetail.invoice')
                ->whereHas('customer', function ($q) {
                    $q->where('isDo', 0);
                });
        }

        return $query
            ->orderBy('order.orderDate', 'desc')
            ->orderBy('order.created_at', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    /**
     * Rollback order status from Return DO (4) back to Not Return DO (3).
     * Clears returnDate and returnDescription.
     */
    public function rollbackStatus($id)
    {
        $this->service->where('id', $id)->update([
            'status' => 3,
            'returnDate' => null,
            'returnDescription' => null,
        ]);
    }
}
