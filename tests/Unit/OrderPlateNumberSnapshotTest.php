<?php

namespace Tests\Unit;

use App\Models\Master\Fleet;
use App\Models\Operational\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderPlateNumberSnapshotTest extends TestCase
{
    use DatabaseTransactions;

    private function createOrder(array $attributes): Order
    {
        return Order::create(array_merge([
            'orderDate' => '2026-10-23',
            'routeCode' => 'RT-TEST',
            'qty' => 1,
            'price' => 0,
            'vendorPrice' => 0,
            'vendorPriceSingle' => 0,
            'personalVendorPrice' => 0,
            'personalVendorPriceSingle' => 0,
            'status' => 0,
        ], $attributes));
    }

    public function test_order_snapshots_plate_number_on_creation(): void
    {
        $fleet = Fleet::create([
            'code' => 'FLEET-SNAP-TEST-1',
            'plateNumber' => 'B 1111 TEST',
        ]);

        $order = $this->createOrder([
            'code' => 'ORD-SNAP-TEST-1',
            'fleetCode' => $fleet->code,
        ]);

        $this->assertSame('B 1111 TEST', $order->plateNumber);
        $this->assertDatabaseHas('order', [
            'code' => 'ORD-SNAP-TEST-1',
            'fleetCode' => $fleet->code,
            'plateNumber' => 'B 1111 TEST',
        ]);
    }

    public function test_order_plate_number_is_preserved_when_master_fleet_is_edited(): void
    {
        $fleet = Fleet::create([
            'code' => 'FLEET-SNAP-TEST-2',
            'plateNumber' => 'B 2222 OLD',
        ]);

        $order = $this->createOrder([
            'code' => 'ORD-SNAP-TEST-2',
            'fleetCode' => $fleet->code,
        ]);

        $this->assertSame('B 2222 OLD', $order->plateNumber);

        // Edit plat nomor di master fleet
        $fleet->update(['plateNumber' => 'B 8888 NEW']);
        $this->assertSame('B 8888 NEW', $fleet->fresh()->plateNumber);

        // Refresh order dari database: plat nomor di order tetap plat nomor lama (snapshot)
        $orderFresh = Order::where('code', 'ORD-SNAP-TEST-2')->first();
        $this->assertSame('B 2222 OLD', $orderFresh->getRawOriginal('plateNumber'));
        $this->assertSame('B 2222 OLD', $orderFresh->plateNumber);
    }

    public function test_order_plate_number_falls_back_to_fleet_if_snapshot_is_null(): void
    {
        $fleet = new Fleet([
            'code' => 'FLEET-SNAP-TEST-3',
            'plateNumber' => 'B 3333 FALLBACK',
        ]);

        $order = new Order([
            'code' => 'ORD-SNAP-TEST-3',
            'fleetCode' => 'FLEET-SNAP-TEST-3',
            'plateNumber' => null,
        ]);
        $order->setRelation('fleet', $fleet);

        $this->assertSame('B 3333 FALLBACK', $order->plateNumber);
    }

    public function test_plate_number_snapshot_is_preserved_through_not_return_do_and_return_do_flow(): void
    {
        $fleet = Fleet::create([
            'code' => 'FLEET-SNAP-FLOW',
            'plateNumber' => 'B 9999 FLOW',
        ]);

        // 1. Order dibuat di operational/order (Status 0)
        $order = $this->createOrder([
            'code' => 'ORD-SNAP-FLOW-1',
            'fleetCode' => $fleet->code,
            'status' => 0,
        ]);
        $this->assertSame('B 9999 FLOW', $order->plateNumber);

        // Plat di master fleet diedit orang lain
        $fleet->update(['plateNumber' => 'B 7777 CHANGED']);

        // 2. Order diselesaikan ke operational/not-return-do (Status 3)
        $order->update(['status' => 3]);
        $orderFresh = Order::where('code', 'ORD-SNAP-FLOW-1')->first();
        $this->assertSame('B 9999 FLOW', $orderFresh->getRawOriginal('plateNumber'));
        $this->assertSame(3, $orderFresh->status);

        // 3. Order dikonfirmasi kembali ke operational/return-do (Status 4)
        $orderFresh->update([
            'status' => 4,
            'returnDate' => now()->format('Y-m-d'),
            'returnDescription' => 'Returned',
        ]);
        $orderReturned = Order::where('code', 'ORD-SNAP-FLOW-1')->first();
        $this->assertSame('B 9999 FLOW', $orderReturned->getRawOriginal('plateNumber'));
        $this->assertSame(4, $orderReturned->status);
    }
}
