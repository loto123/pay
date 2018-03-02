<?php

namespace App\Http\Controllers;

use App\Pay\Impl\Heepay\WechatH5;
use App\Pay\Model\Deposit;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $wepay = new WechatH5();
        $deposit = new Deposit();
        $deposit->out_batch_no = 'abcdefg';
        $deposit->amount = 120.01;
        $wepay->benefitShare(['allot_data' => 'huanghongzhao@supernano.com^1^F'], $deposit);
        echo 'hello';
    }
}
