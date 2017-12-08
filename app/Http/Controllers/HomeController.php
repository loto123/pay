<?php

namespace App\Http\Controllers;

use App\Pay\Model\MasterContainerFactory;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //为A生成钱包
        $walletA = MasterContainerFactory::get();

        //取得B的钱包
        $walletB = MasterContainerFactory::get(2);

        //A通过可用余额向B转账100元,费用为0.5元
        $walletA->transfer($walletB, 100, 1, 0.5, false, false);

        //A发起一笔新的结算,返回一个结算容器
        $settlement = $walletA->newSettlement();

        //B向结算支付了20元
        $transfer = $walletB->transfer($settlement, 20, 2, 0, false, false);

        //结算容器向第三个主容器转账10元,系统收费0.1元
        $settlement->transfer(MasterContainerFactory::get(3), 10, 3, 0.1, false, false);

        //提取结算余额到A余额
        $settlement->extract();

        //撤回付款,因为结算容器已被提取,撤回会返回TRANSFER::CHARGE_BACK_OUT_OF_BALANCE
        $transfer->chargeback();

        //A钱包冻结10元
        $walletA->freeze(5);

        //A钱包解冻5元
        $walletA->unfreeze(5);



        return view('home');
    }
}
