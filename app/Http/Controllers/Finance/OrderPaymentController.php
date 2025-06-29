<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Master\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OrderPaymentController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;

    public function __construct($orderPaymentSvc, MenuService $menuSvc)
    {
        $this->service = $orderPaymentSvc;
        $this->title = "Order Payment";
        $this->menuSvc = $menuSvc->getByName("Order Payment");
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = "finance.order-payment.";
    }

    public function index()
    {
        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }
}
