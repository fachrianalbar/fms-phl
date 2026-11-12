<?php

namespace Tests\Unit;

use App\Models\Master\CostComponent;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Services\Operational\OrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderCostSnapshotTest extends TestCase
{
    use DatabaseTransactions;

    private function service(): OrderService
    {
        return app(OrderService::class);
    }

    private function makeOrder(string $code): Order
    {
        return Order::create([
            'code' => $code,
            'shipmentNumber' => 'SHP-'.$code,
            'orderDate' => '2026-11-12',
            'routeCode' => 'RT-TEST',
            'qty' => 1,
            'price' => 0,
            'routeAmount' => 0,
            'vendorPrice' => 0,
            'vendorPriceSingle' => 0,
            'personalVendorPrice' => 0,
            'personalVendorPriceSingle' => 0,
            'status' => 0,
        ]);
    }

    private function makeComponent(string $code, string $name, $price): CostComponent
    {
        return CostComponent::create([
            'code' => $code,
            'name' => $name,
            'type' => 'cost',
            'price' => $price,
        ]);
    }

    private function submittedRowsAsEditForm(string $orderCode): array
    {
        return OrderCost::where('orderCode', $orderCode)
            ->get()
            ->map(fn (OrderCost $cost) => [
                'componentType' => $cost->componentType,
                'nominal' => (int) $cost->nominal,
                'description' => (string) ($cost->description ?? ''),
                'type' => $cost->type === 'On Charge' ? 'Ditagihkan' : 'Tidak Ditagihkan',
            ])
            ->all();
    }

    public function test_cost_rows_store_submitted_nominal_and_component_name_snapshot(): void
    {
        $component = $this->makeComponent('CC-SNAP-1', 'Biaya Tol', 150000);
        $order = $this->makeOrder('ORD-COST-SNAP-1');

        $this->service()->syncOrderCostRows(
            $order->code,
            [[
                'componentType' => $component->code,
                'nominal' => 200000,
                'description' => 'override saat order',
                'type' => 'Tidak Ditagihkan',
            ]],
            [[
                'componentType' => $component->code,
                'nominal' => 150000,
            ]],
            true,
            true,
        );

        $cost = OrderCost::where('orderCode', $order->code)->firstOrFail();

        $this->assertSame(200000, (int) $cost->nominal);
        $this->assertSame('Biaya Tol', $cost->getRawOriginal('componentName'));
        $this->assertSame(1, (int) $cost->is_route);
    }

    public function test_edit_save_preserves_nominal_when_master_price_changes(): void
    {
        $component = $this->makeComponent('CC-SNAP-2', 'Biaya Tol', 150000);
        $order = $this->makeOrder('ORD-COST-SNAP-2');

        $this->service()->syncOrderCostRows(
            $order->code,
            [[
                'componentType' => $component->code,
                'nominal' => 150000,
                'description' => '',
                'type' => 'Tidak Ditagihkan',
            ]],
            [[
                'componentType' => $component->code,
                'nominal' => 150000,
            ]],
            true,
            true,
        );

        // Harga master berubah setelah order dibuat.
        $component->update(['price' => 250000]);

        // Simulasi simpan ulang form edit tanpa mengubah nominal apa pun.
        $this->service()->syncOrderCostRows(
            $order->code,
            $this->submittedRowsAsEditForm($order->code),
            [[
                'componentType' => $component->code,
                'nominal' => 250000,
            ]],
            false,
            true,
        );

        $cost = OrderCost::where('orderCode', $order->code)->firstOrFail();

        $this->assertSame(150000, (int) $cost->nominal);
        $this->assertSame('Biaya Tol', $cost->componentName);
    }

    public function test_component_name_snapshot_survives_master_rename_and_delete(): void
    {
        $component = $this->makeComponent('CC-SNAP-3', 'Nama Tol Lama', 100000);
        $order = $this->makeOrder('ORD-COST-SNAP-3');

        $this->service()->syncOrderCostRows(
            $order->code,
            [[
                'componentType' => $component->code,
                'nominal' => 100000,
                'description' => '',
                'type' => 'Tidak Ditagihkan',
            ]],
            [[
                'componentType' => $component->code,
                'nominal' => 100000,
            ]],
            true,
            true,
        );

        $component->update(['name' => 'Nama Tol Baru']);
        $component->delete();

        $cost = OrderCost::where('orderCode', $order->code)->firstOrFail();

        $this->assertSame('Nama Tol Lama', $cost->getRawOriginal('componentName'));
        $this->assertSame('Nama Tol Lama', $cost->componentName);
        $this->assertNull($cost->costComponent);
    }

    public function test_route_change_rebuilds_cost_rows_from_new_master(): void
    {
        $component = $this->makeComponent('CC-SNAP-4', 'Biaya Tol', 150000);
        $order = $this->makeOrder('ORD-COST-SNAP-4');

        $this->service()->syncOrderCostRows(
            $order->code,
            [[
                'componentType' => $component->code,
                'nominal' => 150000,
                'description' => '',
                'type' => 'Tidak Ditagihkan',
            ]],
            [[
                'componentType' => $component->code,
                'nominal' => 150000,
            ]],
            true,
            true,
        );

        // Route diganti: baris dibangun ulang dari master route baru.
        $this->service()->syncOrderCostRows(
            $order->code,
            [[
                'componentType' => $component->code,
                'nominal' => 300000,
                'description' => '',
                'type' => 'Tidak Ditagihkan',
            ]],
            [[
                'componentType' => $component->code,
                'nominal' => 300000,
            ]],
            true,
            true,
        );

        $cost = OrderCost::where('orderCode', $order->code)->firstOrFail();

        $this->assertSame(300000, (int) $cost->nominal);
    }

    public function test_rows_removed_from_form_are_deleted(): void
    {
        $componentA = $this->makeComponent('CC-SNAP-5A', 'Biaya Tol', 100000);
        $componentB = $this->makeComponent('CC-SNAP-5B', 'Biaya Parkir', 50000);
        $order = $this->makeOrder('ORD-COST-SNAP-5');

        $routeRows = [
            ['componentType' => $componentA->code, 'nominal' => 100000],
            ['componentType' => $componentB->code, 'nominal' => 50000],
        ];

        $this->service()->syncOrderCostRows($order->code, $routeRows, $routeRows, true, true);
        $this->assertSame(2, OrderCost::where('orderCode', $order->code)->count());

        // User menghapus satu baris di form.
        $this->service()->syncOrderCostRows(
            $order->code,
            [['componentType' => $componentA->code, 'nominal' => 100000, 'description' => '', 'type' => 'Tidak Ditagihkan']],
            $routeRows,
            false,
            true,
        );

        $this->assertSame(1, OrderCost::where('orderCode', $order->code)->count());
        $this->assertDatabaseMissing('order_cost', [
            'orderCode' => $order->code,
            'componentType' => $componentB->code,
        ]);
    }
}
