<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Bank\ConfigBank;
use App\Services\Bank\ConfigBankService;
use App\Services\Bank\UserBankService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class ConfigBankController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;
    protected $userBankSvc;
    protected $userSvc;

    public function __construct(ConfigBankService $configBankSvc, UserBankService $userBankSvc, UserService $userSvc, MenuService $menuSvc)
    {
        $this->service = $configBankSvc;
        $this->userBankSvc = $userBankSvc;
        $this->userSvc = $userSvc;
        $this->title = "Config Bank";
        $this->view = "bank.config-bank.";
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = $this->userSvc->findAll();
        $userBank = $this->userBankSvc->findAll();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('user', $user)
            ->with('userBank', $userBank)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'userCode' => 'required',
            'userBankCode' => ['required', Rule::unique('config_bank', 'userBankCode')->whereNull('deleted_at')],
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        $configBank = ConfigBank::whereIn('userBankCode', $request->userBankCode)->get();

        if (count($configBank) > 0) {
            return redirect()->route($this->view . 'index')->with('fail', 'User bank data aiready exists');
        }

        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title . ' data was save succesfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $code)
    {
        $data = $this->service->getByUser($code);


        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $user = $this->userSvc->findAll();
        $userBank = $this->userBankSvc->findAll();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('user', $user)
            ->with('userBank', $userBank)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'userCode' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        $userBankCodes = $request->input('userBankCode');

        if ($userBankCodes) {
            $userBankCodes = array_filter($userBankCodes);

            if (count($userBankCodes) !== count(array_unique($userBankCodes))) {
                return back()->withErrors(['fail' => 'Duplicate user bank selections are not allowed.'])->withInput();
            }
        }
        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title .  ' data was update succesfully');
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

        return redirect()->back()->with('success', 'Delete Data Success');
    }

    public function destroyByUser($code)
    {
        $this->service->destroyByUser($code, $this->title);

        return redirect()->route($this->view . 'index')->with('success', 'Delete Data Success');
    }

    public function listUserBank()
    {
        return $this->service->listUserBank();
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('userCode', function ($row) {
                    $username = '';

                    if (isset($row->user->name)) {
                        $username = $row->user->username;
                    }

                    return $username;
                })
                ->addColumn('userBankData', function ($row) {
                    $result = '';

                    $i = 1;
                    if (isset($row->configBank)) {
                        $data = $row->configBank;

                        $result .= '<ul>';
                        foreach ($data as $item) {
                            $result .=            '<li>' . $i . '. ' . $item->userBank->accountNumber . ' - ' . $item->userBank->bank->name . ' - ' . $item->userBank->accountName . '</li>';
                            $i++;
                        }
                        $result .= '</li>';
                    }

                    return $result;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="' . route($this->view . 'edit', $row->code) . '"><i class="icon-pencil-alt"></i></a></li>
                                        <li class="delete"><a href="javascript:deleteData(\'' . $row->code . '\')"><i class="icon-trash"></i></a></li>
                                    </ul>';

                    return $btn;
                })
                ->rawColumns(['action', 'userBankData', 'type', 'userCode', 'userBankCode'])
                ->toJson();
        }
    }
}
