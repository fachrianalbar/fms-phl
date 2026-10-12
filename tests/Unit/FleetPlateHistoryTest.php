<?php

namespace Tests\Unit;

use App\Models\Master\Fleet;
use App\Models\Master\FleetPlateHistory;
use App\Services\Master\FleetService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class FleetPlateHistoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_updating_fleet_plate_number_creates_history_record(): void
    {
        $fleet = Fleet::create([
            'code' => 'FLEET-HIST-TEST-1',
            'plateNumber' => 'B 1234 OLD',
            'year' => 2024,
        ]);

        $service = app(FleetService::class);

        $request = new Request([
            'plateNumber' => 'B 5678 NEW',
            'year' => 2024,
            'plate_change_reason' => 'Perpanjangan STNK 5 tahunan',
            'plate_change_date' => '2026-10-23',
        ]);

        $service->update($request, $fleet->id, 'Fleet');

        // Refresh master fleet: nopol sudah berubah
        $fleetFresh = $fleet->fresh();
        $this->assertSame('B 5678 NEW', $fleetFresh->plateNumber);

        // Record history harus tercatat
        $this->assertDatabaseHas('fleet_plate_histories', [
            'fleet_id' => $fleet->id,
            'fleet_code' => $fleet->code,
            'old_plate_number' => 'B 1234 OLD',
            'new_plate_number' => 'B 5678 NEW',
            'reason' => 'Perpanjangan STNK 5 tahunan',
        ]);

        // Cek via relation
        $this->assertCount(1, $fleetFresh->plateHistories);
        $history = $fleetFresh->plateHistories->first();
        $this->assertSame('B 1234 OLD', $history->old_plate_number);
        $this->assertSame('B 5678 NEW', $history->new_plate_number);
        $this->assertSame('Perpanjangan STNK 5 tahunan', $history->reason);
    }

    public function test_updating_fleet_without_changing_plate_number_does_not_create_history(): void
    {
        $fleet = Fleet::create([
            'code' => 'FLEET-HIST-TEST-2',
            'plateNumber' => 'B 9999 SAME',
            'year' => 2023,
        ]);

        $service = app(FleetService::class);

        // Hanya edit tahun tanpa ganti nopol
        $request = new Request([
            'plateNumber' => 'B 9999 SAME',
            'year' => 2025,
        ]);

        $service->update($request, $fleet->id, 'Fleet');

        $fleetFresh = $fleet->fresh();
        $this->assertSame(2025, (int) $fleetFresh->year);
        $this->assertSame('B 9999 SAME', $fleetFresh->plateNumber);

        // Tidak boleh ada history baru
        $this->assertDatabaseMissing('fleet_plate_histories', [
            'fleet_id' => $fleet->id,
        ]);
        $this->assertCount(0, $fleetFresh->plateHistories);
    }

    public function test_plate_history_endpoint_returns_json(): void
    {
        $fleet = Fleet::create([
            'code' => 'FLEET-HIST-TEST-3',
            'plateNumber' => 'B 7777 ACT',
        ]);

        FleetPlateHistory::create([
            'fleet_id' => $fleet->id,
            'fleet_code' => $fleet->code,
            'old_plate_number' => 'B 1111 PREV',
            'new_plate_number' => 'B 7777 ACT',
            'change_date' => '2026-10-20',
            'reason' => 'Mutasi Nopol',
            'changed_by' => 'Admin Test',
        ]);

        $user = \App\Models\User::first();
        $response = $this->actingAs($user)->getJson(route('master.fleets.plate-histories', $fleet->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'fleet' => [
                'code' => 'FLEET-HIST-TEST-3',
                'current_plate' => 'B 7777 ACT',
            ],
        ]);
        $this->assertCount(1, $response->json('histories'));
        $this->assertSame('B 1111 PREV', $response->json('histories.0.old_plate_number'));
        $this->assertSame('B 7777 ACT', $response->json('histories.0.new_plate_number'));
    }
}
