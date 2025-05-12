<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Services\Data\RouteDetailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RouteDetailController extends Controller
{
    protected $service;
    protected $title;
    protected $view;

    public function __construct(RouteDetailService $routeDetailSvc)
    {
        $this->service = $routeDetailSvc;
        $this->title = "Route Detail";
        $this->view = "data.route.";
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $routeData = json_decode($request->routeData);

        $validator = Validator::make($request->all(), [
            'componentCode' => 'required',
            'componentType' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'edit', $routeData->id)->with('fail', $validator->errors()->all()[0]);
        }

        if ($request->componentType == 'Percentage') {
            if ($request->percentage > 100) {
                return redirect()->route($this->view . 'edit', $routeData->id)->with('fail', 'Percentage cannot be more than 100%');
            }
        }

        if ($request->componentType == 'Amount' && $routeData->routeTypeCode == 'TRIP') {
            if ((int)$request->amount > $routeData->price) {
                return redirect()->route($this->view . 'edit', $routeData->id)->with('fail', 'Amount cannot be higher than route price');
            }
        }

        foreach ($routeData->route_detail as $item) {
            if ($item->componentCode == $request->componentCode) {
                return redirect()->route($this->view . 'edit', $routeData->id)->with('fail', 'Cost component is aiready exist');
            }
        }

        $totalPrice = $request->totalPrice;

        if ($request->componentType == 'Amount') {
            $totalPrice += $request->amount;
        }

        if ($request->componentType == 'Percentage') {
            $totalPrice += $routeData->price * ($request->percentage / 100);
        }

        if ($totalPrice > $routeData->price && $routeData->routeTypeCode == 'TRIP') {
            return redirect()->route($this->view . 'edit', $routeData->id)->with('fail', 'Cost component cannot be higher than route price');
        }

        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'edit', $routeData->id)->with('success', $this->title . ' data was save succesfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'edit', $routeData->id)->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->service->getById($id);
        $this->service->destroy($id, $this->title);

        return redirect()->route($this->view . 'edit', $data->route->id)->with('success', 'Delete Data Success');
    }
}
