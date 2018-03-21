<?php

namespace App\Http\Controllers;

use App\Jobs\SubmitWithdrawRequest;
use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawRetry;
use App\Pay\PayLogger;

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
        /**
         * 测试提现
         */

        /**
         * @var $order Withdraw
         */
        $order = Withdraw::find(265);

        if (WithdrawRetry::isWithdrawFailed((new SubmitWithdrawRequest($order))->handle()->state)) {
            PayLogger::withdraw()->error('系统自动提现失败', ['sell_bill_id' => $order->getKey()]);
        }

    }
}
