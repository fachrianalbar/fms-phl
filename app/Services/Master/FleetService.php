<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Master\Fleet;
use App\Models\Master\FleetPicture;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class FleetService
{
    use LogActivity;

    protected $service;

    public function __construct(Fleet $fleet)
    {
        $this->service = $fleet;
    }

    public function findAll()
    {
        return $this->service->with(['type', 'brand'])->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with(['pictures'])->first();
    }

    public function store($request, $title)
    {
        $barcode = null;
        if ($request->barcode) {
            $file = $request->barcode;

            $fileExtension = $file->getClientOriginalExtension();
            $barcode = Str::random(25) . '.' . $fileExtension;

            $path = "public/fleet/barcode";

            Storage::putFileAs($path, $file, $barcode);
        }

        $vehicleRegistrationNumber = null;
        if ($request->vehicleRegistrationNumber) {
            $file = $request->vehicleRegistrationNumber;

            $fileExtension = $file->getClientOriginalExtension();
            $vehicleRegistrationNumber = Str::random(25) . '.' . $fileExtension;

            $path = "public/fleet/vehicleRegistrationNumber";

            Storage::putFileAs($path, $file, $vehicleRegistrationNumber);
        }

        $data = $this->service->create([
            'plateNumber' => $request->vehicleName,
            'year' => $request->year,
            'engineNumber' => $request->engineNumber,
            'frameNumber' => $request->frameNumber,
            'fleetBrandCode' => $request->fleetBrandCode,
            'fleetTypeCode' => $request->fleetTypeCode,
            'vehicleRegistrationNumber' => $vehicleRegistrationNumber,
            'barcode' => $barcode,
            'code' => GenerateCode::generateCode('FMSF')
        ]);

        if (isset($request->fleetPicture)) {
            if (count($request->fleetPicture) > 0) {
                foreach ($request->fleetPicture as $item) {
                    $file = $item;
                    $fileExtension = $file->getClientOriginalExtension();

                    $fleetPicture = Str::random(25) . '.' . $fileExtension;

                    $path = "public/fleet/fleetPicture";

                    Storage::putFileAs($path, $file, $fleetPicture);

                    FleetPicture::create([
                        'code' => GenerateCode::generateCode('FFP'),
                        'fleetCode' => $data->code,
                        'fleetPicture' => $fleetPicture
                    ]);
                }
            }
        }

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $data = $this->getById($id);

        foreach ($request->file('fleetPicture', []) as $idPicture => $file) {
            if ($file) {
                // Ambil data lama
                $existingPicture = FleetPicture::where('id', $idPicture)->first();

                if ($existingPicture) {
                    // Hapus file lama dari storage
                    Storage::delete('fleet/fleetPicture/' . $existingPicture->fleetPicture);

                    // Hapus data lama dari database
                    $existingPicture->delete();
                }

                // Simpan file baru
                $fileExtension = $file->getClientOriginalExtension();

                $newFileName = Str::random(25) . '.' . $fileExtension;

                $path = "public/fleet/fleetPicture";

                Storage::putFileAs($path, $file, $newFileName);

                // Insert data baru ke database
                FleetPicture::create([
                    'code' => GenerateCode::generateCode('FFP'),
                    'fleetCode' => $data->code,
                    'fleetPicture' => $newFileName,
                ]);
            }
        }

        if (isset($request->newFleetPicture)) {
            if (count($request->newFleetPicture) > 0) {
                foreach ($request->newFleetPicture as $item) {
                    $file = $item;
                    $fileExtension = $file->getClientOriginalExtension();

                    $newFleetPicture = Str::random(25) . '.' . $fileExtension;

                    $path = "public/fleet/fleetPicture";

                    Storage::putFileAs($path, $file, $newFleetPicture);

                    FleetPicture::create([
                        'code' => GenerateCode::generateCode('FFP'),
                        'fleetCode' => $data->code,
                        'fleetPicture' => $newFleetPicture,
                    ]);
                }
            }
        }

        $barcode = $data->barcode;

        if ($request->barcode) {
            $file = $request->barcode;

            $fileExtension = $file->getClientOriginalExtension();
            $barcode = Str::random(25) . '.' . $fileExtension;

            $path = "public/fleet/barcode/";
            if ($data->barcode) {
                Storage::delete($path . $data->barcode);
            }

            Storage::putFileAs($path, $file, $barcode);
        }

        $vehicleRegistrationNumber = $data->vehicleRegistrationNumber;

        if ($request->vehicleRegistrationNumber) {
            $file = $request->vehicleRegistrationNumber;
            $fileExtension = $file->getClientOriginalExtension();

            $vehicleRegistrationNumber =   Str::random(25) . '.' . $fileExtension;

            $path = "public/fleet/vehicleRegistrationNumber/";
            if ($data->vehicleRegistrationNumber) {
                Storage::delete($path . $data->vehicleRegistrationNumber);
            }

            Storage::putFileAs($path, $file, $vehicleRegistrationNumber);
        }

        $this->service->where('id', $id)->update([
            // 'plateNumber' => $request->vehicleName,
            'year' => $request->year,
            'engineNumber' => $request->engineNumber,
            'frameNumber' => $request->frameNumber,
            'fleetBrandCode' => $request->fleetBrandCode,
            'fleetTypeCode' => $request->fleetTypeCode,
            'vehicleRegistrationNumber' => $vehicleRegistrationNumber,
            'barcode' => $barcode,
        ]);





        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
