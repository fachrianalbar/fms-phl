<?php

namespace Tests\Unit;

use App\Models\Master\Employee;
use App\Models\Operational\Order;
use App\Models\Operational\OrderDriver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderDriverSnapshotTest extends TestCase
{
    use DatabaseTransactions;

    private function createOrder(array $attributes): Order
    {
        return Order::create(array_merge([
            'orderDate' => '2026-11-12',
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

    public function test_order_snapshots_driver_name_on_creation(): void
    {
        $driver = Employee::create([
            'code' => 'EMP-SNAP-1',
            'name' => 'Budi Santoso',
        ]);

        $order = $this->createOrder([
            'code' => 'ORD-DRIVER-SNAP-1',
            'driverCode' => $driver->code,
        ]);

        $this->assertSame('Budi Santoso', $order->driverName);
        $this->assertDatabaseHas('order', [
            'code' => 'ORD-DRIVER-SNAP-1',
            'driverCode' => $driver->code,
            'driverName' => 'Budi Santoso',
        ]);
    }

    public function test_order_driver_name_is_preserved_when_master_employee_is_edited(): void
    {
        $driver = Employee::create([
            'code' => 'EMP-SNAP-2',
            'name' => 'Supir Lama',
        ]);

        $order = $this->createOrder([
            'code' => 'ORD-DRIVER-SNAP-2',
            'driverCode' => $driver->code,
        ]);

        $driver->update(['name' => 'Supir Baru']);

        $orderFresh = Order::where('code', 'ORD-DRIVER-SNAP-2')->first();
        $this->assertSame('Supir Lama', $orderFresh->getRawOriginal('driverName'));
        $this->assertSame('Supir Lama', $orderFresh->driverName);
    }

    public function test_order_driver_history_keeps_snapshot_name(): void
    {
        $driver = Employee::create([
            'code' => 'EMP-SNAP-3',
            'name' => 'Supir Awal',
        ]);

        $order = $this->createOrder([
            'code' => 'ORD-DRIVER-SNAP-3',
            'driverCode' => $driver->code,
        ]);

        $history = OrderDriver::create([
            'code' => 'ODR-SNAP-3',
            'orderCode' => $order->code,
            'driverCode' => $driver->code,
            'description' => 'Penugasan awal',
        ]);

        $driver->update(['name' => 'Supir Ganti Nama']);

        $historyFresh = OrderDriver::where('code', 'ODR-SNAP-3')->first();
        $this->assertSame('Supir Awal', $historyFresh->getRawOriginal('driverName'));
        $this->assertSame('Supir Awal', $historyFresh->driverName);
    }

    public function test_changing_driver_updates_snapshot_and_keeps_old_history(): void
    {
        $driverA = Employee::create([
            'code' => 'EMP-SNAP-4A',
            'name' => 'Supir A',
        ]);
        $driverB = Employee::create([
            'code' => 'EMP-SNAP-4B',
            'name' => 'Supir B',
        ]);

        $order = $this->createOrder([
            'code' => 'ORD-DRIVER-SNAP-4',
            'driverCode' => $driverA->code,
        ]);

        $historyA = OrderDriver::create([
            'code' => 'ODR-SNAP-4A',
            'orderCode' => $order->code,
            'driverCode' => $driverA->code,
        ]);

        $order->update(['driverCode' => $driverB->code]);
        $orderFresh = Order::where('code', 'ORD-DRIVER-SNAP-4')->first();

        $this->assertSame('Supir B', $orderFresh->driverName);
        $this->assertSame('Supir A', $historyA->fresh()->driverName);
    }
}
