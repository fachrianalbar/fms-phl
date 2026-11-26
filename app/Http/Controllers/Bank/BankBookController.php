<?php

namespace App\Http\Controllers\Bank;

use App\Exports\BankBookExport;
use App\Http\Controllers\Controller;
use App\Models\Bank\UserBank;
use App\Services\Bank\BankBookService;
use App\Services\MenuService;
use App\Services\MutationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

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
        $this->title = 'Bank Book';
        $this->view = 'bank.bank-book.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userBanks = UserBank::with('bank')->orderBy('accountName')->get();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('userBanks', $userBanks)
            ->with('title', $this->title);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $code)
    {
        $data = $this->service->findByUserBankCode($code);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $mutation = $this->mutationSvc->getByUserBankCode($code);

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('mutation', $mutation)
            ->with('data', $data);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll($request->all());

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
                    $accountNumber = '';

                    if (isset($row->userBank->accountNumber)) {
                        $accountNumber = $row->userBank->accountNumber;
                    }

                    return $accountNumber;
                })

                ->editColumn('userBank.accountName', function ($row) {
                    $accountName = '';

                    if (isset($row->userBank->accountName)) {
                        $accountName = $row->userBank->accountName;
                    }

                    return $accountName;
                })

                ->editColumn('userBank.type', function ($row) {
                    $type = '';

                    if (isset($row->userBank->type)) {
                        if ($row->userBank->type == 1) {
                            $type = 'Person';
                        } elseif ($row->userBank->type == 2) {
                            $type = 'Company';
                        }
                    }

                    return $type;
                })

                ->addColumn('action', function ($row) {
                    $btn = '<td>
                                    <a href="'.route($this->view.'show', $row->userBankCode).'"
                                class="btn btn-icon btn-sm bg-primary-subtle me-1"
                                data-bs-toggle="tooltip" title="Edit">
                                    <i class="mdi mdi-book-account fs-14 text-primary"></i>
                                </a>
                            </td>';

                    return $btn;
                })
                ->rawColumns(['action', 'debit', 'credit', 'balance', 'userBank.accountNumber', 'userBank.accountName', 'userBank.type'])
                ->toJson();
        }
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new BankBookExport($request->all(), $this->service),
            'Buku-Bank-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $books = $this->service->findAll($request->all());

        $filterSummary = [];
        if ($request->filled('startDate') || $request->filled('endDate')) {
            $start = $request->filled('startDate') ? Carbon::parse($request->startDate)->format('d-m-Y') : 'Awal';
            $end = $request->filled('endDate') ? Carbon::parse($request->endDate)->format('d-m-Y') : 'Sekarang';
            $filterSummary[] = 'Periode Mutasi: '.$start.' s/d '.$end;
        }
        if ($request->filled('userBankCode')) {
            $bank = UserBank::with('bank')->where('code', $request->userBankCode)->first();
            $bankLabel = $bank
                ? trim(($bank->bank->name ?? 'Bank').($bank->accountNumber ? ' - '.$bank->accountNumber : '').($bank->accountName ? ' ('.$bank->accountName.')' : ''))
                : $request->userBankCode;
            $filterSummary[] = 'Rekening Bank: '.$bankLabel;
        }
        if ($request->filled('mutationType')) {
            $filterSummary[] = 'Tipe Mutasi: '.($request->mutationType === 'In' ? 'Masuk (In)' : 'Keluar (Out)');
        }

        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'default_font' => 'sans-serif',
        ]);

        $mpdf->WriteHTML(view('bank.bank-book.report.bank-book-pdf', [
            'books' => $books,
            'title' => 'Laporan Buku Bank',
            'filterSummary' => $filterSummary,
            'printedAt' => now(),
        ])->render());

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Buku-Bank-'.now()->format('YmdHis').'.pdf"');
    }
}
