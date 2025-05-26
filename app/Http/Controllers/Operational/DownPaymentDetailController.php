<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Operational\DownPayment;
use App\Services\Operational\DownPaymentDetailService;
use App\Services\Operational\DownPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DownPaymentDetailController extends Controller
{
    protected $service;
    protected $downPaymentSvc;
    protected $title;
    protected $view;
    protected $menuSvc;

    public function __construct(DownPaymentDetailService $dpSvc, DownPaymentService $downPaymentSvc, MenuService $menuSvc)
    {
        $this->service = $dpSvc;
        $this->downPaymentSvc = $downPaymentSvc;
        $this->title = "Down Payment Detail";
        $this->view = "operational.down-payment.";
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
            'date' => 'required|date',
            'time' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        $data = DownPayment::where('code', $request->dpCode)->first();

        $total = 0;
        foreach ($data->details as $item) {
            $total += $item->price;
        }

        $total += $request->price;

        if ($total > $data->price) {
            return redirect()->route($this->view . 'index')->with('fail', 'Input price cannot be higher than down payment price');
        }
        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
            'date' => 'required|date',
            'time' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title .  ' ' . __('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id, $this->title);

        return redirect()->route($this->view . 'index')->with('success', 'Delete Data Success');
    }
}
