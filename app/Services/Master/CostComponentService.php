<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Data\RouteDetail;
use App\Models\Master\CostComponent;
use App\Models\Master\CostComponentPriceLog;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\Auth;

class CostComponentService
{
    use LogActivity;

    protected $service;

    public function __construct(CostComponent $costComponent)
    {
        $this->service = $costComponent;
    }

    public function findAll()
    {
        return $this->service->orderBy('name', 'asc')->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function getByCode($code)
    {
        return $this->service->where('code', $code)->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'name' => $request->name,
            // 'type' => $request->type,
            'price' => $request->price,
            'code' => GenerateCode::generateCode('TCC'),
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $costComponent = $this->getById($id);
        $oldPrice = $costComponent->price;
        $newPrice = $request->price;

        $this->logActivity($title, $costComponent, 'Before Update');

        // Update cost component
        $this->service->where('id', $id)->update([
            'name' => $request->name,
            // 'type' => $request->type,
            'price' => $newPrice,
        ]);

        // If price changed, sync to route_detail and log the change
        if ($oldPrice != $newPrice) {
            // Update amount di route_detail yang memiliki componentCode ini
            RouteDetail::where('componentCode', $costComponent->code)
                ->update(['amount' => $newPrice]);

            // Insert log perubahan harga
            CostComponentPriceLog::create([
                'costComponentCode' => $costComponent->code,
                'costComponentName' => $costComponent->name,
                'oldPrice' => $oldPrice,
                'newPrice' => $newPrice,
                'changedBy' => Auth::check() ? Auth::user()->name : 'System',
                'notes' => 'Price updated from '.($oldPrice ?: 0).' to '.($newPrice ?: 0),
            ]);
        }

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }

    public function bulkUpdatePrice($request, $title)
    {
        $ids = $request->ids;
        $type = $request->type; // 'increase' or 'decrease'
        $percentage = (float) $request->percentage;

        if (empty($ids) || $percentage <= 0) {
            throw new \InvalidArgumentException('Invalid parameters for bulk update.');
        }

        $multiplier = $type === 'increase' ? (1 + ($percentage / 100)) : (1 - ($percentage / 100));

        $components = $this->service->whereIn('id', $ids)->get();

        foreach ($components as $component) {
            // Validasi: harga harus ada (lebih dari 0)
            if (empty($component->price) || $component->price <= 0) {
                throw new \InvalidArgumentException("Komponen Biaya '{$component->name}' belum memiliki harga dasar. Silakan tentukan harga dasar terlebih dahulu sebelum melakukan penyesuaian persentase.");
            }

            $oldPrice = $component->price;
            $newPrice = round($oldPrice * $multiplier, 2);

            $this->logActivity($title, $component, 'Before Bulk Update Price');

            // Update cost component
            $component->update([
                'price' => $newPrice,
            ]);

            // Sync to route_detail
            RouteDetail::where('componentCode', $component->code)
                ->update(['amount' => $newPrice]);

            // Insert log perubahan harga
            CostComponentPriceLog::create([
                'costComponentCode' => $component->code,
                'costComponentName' => $component->name,
                'oldPrice' => $oldPrice,
                'newPrice' => $newPrice,
                'changedBy' => Auth::check() ? Auth::user()->name : 'System',
                'notes' => 'Bulk Price updated from '.($oldPrice ?: 0).' to '.($newPrice ?: 0).' ('.$type.' '.$percentage.'%)',
            ]);

            $this->logActivity($title, $component->fresh(), 'After Bulk Update Price');
        }
    }
}
