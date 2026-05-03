<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\MaintenanceDetail;
use Illuminate\Http\Request;

class MaintenanceDetailController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'nullable|string',
            'maintenanceCode' => 'required|string',
            'itemCode' => 'required|string',
            'qty' => 'required|numeric',
            'status' => 'nullable|string',
        ]);

        $detail = MaintenanceDetail::create($data);
        return response()->json($detail, 201);
    }

    public function update(Request $request, $id)
    {
        $detail = MaintenanceDetail::findOrFail($id);
        $data = $request->validate([
            'itemCode' => 'nullable|string',
            'qty' => 'nullable|numeric',
            'status' => 'nullable|string',
        ]);
        $detail->update($data);
        return response()->json($detail);
    }

    public function destroy($id)
    {
        $detail = MaintenanceDetail::findOrFail($id);
        $detail->delete();
        return response()->json(null, 204);
    }
}
