<?php

namespace App\Http\Controllers;

use App\Pay\Model\Withdraw;

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
        //$order = Withdraw::find(268);

//        if (WithdrawRetry::isWithdrawFailed((new SubmitWithdrawRequest($order))->handle()->state)) {
//            PayLogger::withdraw()->error('系统自动提现失败', ['sell_bill_id' => $order->getKey()]);
//        }
//        $impl = new Transfer();
//        $config = parse_ini_string(Channel::find(6)->config);
//        $config['url'] = 'http://124.232.133.207:8200/transferAdmin/x2xtsfpay/doTransferApi.action';
//        $impl->queryState($order, $config);

    }
}
