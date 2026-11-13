<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Item;
use App\Services\UniqueCodeService;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\DB;

class ItemService
{
    use LogActivity;

    protected $service;

    public function __construct(Item $item, private UniqueCodeService $uniqueCode)
    {
        $this->service = $item;
    }

    public function findAll()
    {
        // Tanpa orderBy default: urutan mengikuti request DataTables agar sorting
        // per kolom (code, name, type, unit, price) benar-benar berlaku.
        // `select('item.*')` mencegah kolom item tertimpa kolom relasi saat Yajra
        // melakukan left join untuk sorting relasi (mis. sort by unit.name).
        return $this->service
            ->select('item.*')
            ->with(['unit', 'category', 'warehouse', 'supplier']);
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $code = $this->uniqueCode->resolve(
            model: Item::class,
            field: 'code',
            requestedCode: $request->input('code'),
        );

        $data = $this->payload($request);
        $data['code'] = $code->resolvedCode;

        $result = $this->service->create($data);

        $this->logActivity($title, $result, 'Create');

        return $code;
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update($this->payload($request));

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $item = $this->getById($id);

        if (! $item) {
            throw new \RuntimeException('Data item tidak ditemukan.');
        }

        $this->ensureNotInUse($item);

        $this->logActivity($title, $item, 'Delete');

        DB::transaction(function () use ($item) {
            $item->delete();

            // Bersihkan baris stock yang tersisa agar tidak menjadi data orphan.
            DB::table('stock')
                ->where('itemCode', $item->code)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);
        });
    }

    /**
     * Kolom yang boleh diisi dari form. `code` sengaja tidak termasuk agar
     * relasi berbasis code (stock, purchase detail, dll) tidak putus.
     */
    private function payload($request): array
    {
        return $request->only([
            'name',
            'brandName',
            'categoryCode',
            'warehouseCode',
            'unitCode',
            'supplierCode',
            'type',
            'price',
        ]);
    }

    /**
     * Cegah item dihapus bila sudah dipakai transaksi/stock supaya data
     * pembelian, pemeliharaan, dan stock tidak menjadi orphan.
     */
    private function ensureNotInUse(Item $item): void
    {
        $relations = [
            'maintenance_detail' => 'transaksi pemeliharaan',
            'purchase_detail' => 'transaksi pembelian',
            'stock_transaction' => 'transaksi stock',
        ];

        $usedIn = [];

        foreach ($relations as $table => $label) {
            $exists = DB::table($table)
                ->where('itemCode', $item->code)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                $usedIn[] = $label;
            }
        }

        if ($usedIn !== []) {
            throw new \DomainException(
                'Item '.$item->code.' tidak dapat dihapus karena sudah digunakan pada '.implode(', ', $usedIn).'.'
            );
        }
    }
}
