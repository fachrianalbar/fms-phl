<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Services\Bank\BankAccountService;
use App\Services\Bank\UserBankService;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class UserBankController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $bankSvc;

    public function __construct(UserBankService $userBankSvc, BankAccountService $bankSvc, MenuService $menuSvc)
    {
        $this->service = $userBankSvc;
        $this->bankSvc = $bankSvc;
        $this->title = 'User Bank';
        $this->view = 'bank.user-bank.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bank = $this->bankSvc->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('bank', $bank)
            ->with('stats', $this->service->getStats());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'accountNumber' => ['required', Rule::unique('user_bank', 'accountNumber')->whereNull('deleted_at')],
            'accountName' => 'required',
            'type' => 'required',
            'bankCode' => 'required',
            'balance' => 'required',
            'rekening_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()[0]], 422);
        }

        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);

            DB::commit();

            return response()->json(['message' => $this->title.' '.__('general.data_was_save_successfully')]);
        } catch (\Throwable $th) {
            DB::connection()->rollBack();

            return response()->json(['message' => 'Line : '.$th->getLine().'<br>'.$th->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return response()->json(['message' => __('general.data_not_found')], 404);
        }

        $validator = Validator::make($request->all(), [
            'accountNumber' => ['required', Rule::unique('user_bank', 'accountNumber')->ignore($data->id)->whereNull('deleted_at')],
            'accountName' => 'required',
            'type' => 'required',
            'bankCode' => 'required',
            'rekening_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()[0]], 422);
        }

        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return response()->json(['message' => $this->title.' '.__('general.data_was_update_succesfully')]);
        } catch (\Throwable $th) {
            DB::connection()->rollBack();

            return response()->json(['message' => 'Line : '.$th->getLine().'<br>'.$th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return response()->json(['message' => __('general.data_not_found')], 404);
        }

        try {
            $this->service->destroy($id, $this->title);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Line : '.$th->getLine().'<br>'.$th->getMessage()], 500);
        }

        return response()->json(['message' => __('general.delete_data_success')]);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAllFiltered($request->input('status', 'all'));

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('bank_name', function ($row) {
                    return '<span class="cell-ellipsis" title="'.e($row['bank_name']).'">'.e($row['bank_name']).'</span>';
                })
                ->editColumn('accountNumber', function ($row) {
                    return '<span class="font-monospace fs-12">'.e($row['accountNumber']).'</span>';
                })
                ->editColumn('accountName', function ($row) {
                    return '<span class="cell-ellipsis" title="'.e($row['accountName']).'">'.e($row['accountName']).'</span>';
                })
                ->editColumn('type', function ($row) {
                    if ((int) $row['type'] === 1) {
                        return '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-account-outline me-1"></i>Person</span>';
                    }

                    return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-domain me-1"></i>Company</span>';
                })
                ->editColumn('rekening_type', function ($row) {
                    if (strtolower($row['rekening_type'] ?? '') === 'internal') {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-home-city-outline me-1"></i>Internal</span>';
                    }

                    return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-bank-outline me-1"></i>External</span>';
                })
                ->editColumn('balance', function ($row) {
                    return '<span class="font-monospace text-dark fs-12">Rp '.number_format((float) $row['balance'], 0, ',', '.').'</span>';
                })
                ->addColumn('action', function ($row) {
                    $edit = '<a href="javascript:void(0)"
                        class="btn btn-icon btn-sm bg-primary-subtle me-1 btn-open-edit"
                        data-bs-toggle="tooltip" title="Edit"
                        data-id="'.e($row['id']).'"
                        data-bankcode="'.e($row['bankCode'] ?? '').'"
                        data-accountnumber="'.e($row['accountNumber'] ?? '').'"
                        data-accountname="'.e($row['accountName'] ?? '').'"
                        data-type="'.e($row['type'] ?? '').'"
                        data-rekeningtype="'.e($row['rekening_type'] ?? '').'">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>';

                    $delete = '<a href="javascript:void(0)"
                        class="btn btn-icon btn-sm bg-danger-subtle btn-open-delete"
                        data-bs-toggle="tooltip" title="Delete"
                        data-id="'.e($row['id']).'"
                        data-accountname="'.e($row['accountName'] ?? '').'">
                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                        </a>';

                    return $edit.$delete;
                })
                ->rawColumns(['action', 'bank_name', 'accountNumber', 'accountName', 'type', 'rekening_type', 'balance'])
                ->toJson();
        }
    }

    /**
     * Statistik agregat untuk KPI cards & filter pills (AJAX).
     */
    public function stats(): JsonResponse
    {
        return response()->json($this->service->getStats());
    }

    public function getCompanyBanks()
    {
        try {
            $banks = $this->service->findCompany();

            if ($banks->isEmpty()) {
                return response()->json([]);
            }

            $formattedBanks = $banks->map(function ($bank) {
                // Try to get bank name from relation, fallback to empty string
                $bankName = '';
                if ($bank && $bank->bank && isset($bank->bank->name)) {
                    $bankName = $bank->bank->name;
                }

                return [
                    'code' => $bank->code ?? '',
                    'bank_name' => $bankName,
                    'account_number' => $bank->accountNumber ?? '',
                    'account_name' => $bank->accountName ?? '',
                    'balance' => (float) ($bank->liveMutation?->balance ?? 0),
                ];
            });

            return response()->json($formattedBanks);
        } catch (\Exception $e) {
            logger()->error('Error in getCompanyBanks: '.$e->getMessage());

            return response()->json([]);
        }
    }
}
