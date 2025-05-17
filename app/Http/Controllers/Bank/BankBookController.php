<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Services\Bank\BankBookService;
use App\Services\MutationService;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;


class BankBookController extends Controller
{
    protected $service;
    protected $mutationSvc;
    protected $title;
    protected $view;
    protected $menuSvc;

    public function __construct(BankBookService $bankBookSvc, MutationService $mutationSvc, MenuService $menuSvc)
    {
        $this->service = $bankBookSvc;
        $this->mutationSvc = $mutationSvc;
        $this->title = "Bank Book";
        $this->view = "bank.bank-book.";
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
     * Display the specified resource.
     */
    public function show(string $code)
    {
        $data = $this->service->findByUserBankCode($code);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $mutation = $this->mutationSvc->getByUserBankCode($code);


        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('mutation', $mutation)
            ->with('data', $data);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('debit', function ($row) {
                    return number_format($row->debit, 0, ',', '.');
                })
                ->editColumn('credit', function ($row) {
                    return number_format($row->credit, 0, ',', '.');
                })
                ->editColumn('balance', function ($row) {
                    return number_format($row->balance, 0, ',', '.');
                })
                ->editColumn('userBank.accountNumber', function ($row) {
                    $accountNumber = "";

                    if (isset($row->userBank->accountNumber)) {
                        $accountNumber = $row->userBank->accountNumber;
                    }

                    return $accountNumber;
                })

                ->editColumn('userBank.accountName', function ($row) {
                    $accountName = "";

                    if (isset($row->userBank->accountName)) {
                        $accountName = $row->userBank->accountName;
                    }

                    return $accountName;
                })

                ->editColumn('userBank.type', function ($row) {
                    $type = "";

                    if (isset($row->userBank->type)) {
                        if ($row->userBank->type == 1) {
                            $type = "Person";
                        } else if ($row->userBank->type == 2) {
                            $type = "Company";
                        }
                    }

                    return $type;
                })


                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li><a href="' . route($this->view . 'show', $row->userBankCode) . '"><i class="icon-book"></i></a></li>

                                    </ul>';

                    return $btn;
                })
                ->rawColumns(['action', 'debit', 'credit', 'balance', 'userBank.accountNumber', 'userBank.accountName', 'userBank.type'])
                ->toJson();
        }
    }
}
