<?php

namespace App\Http\Controllers;

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
        $deposit = new Deposit([
            ['id' => 123,
                'amount' => 1,
                'master_container' => 1
            ]
        ]);
        $method = DepositMethod::find(8);
        $deposit->channel()->associate(Channel::find(6));
        $deposit->method()->associate($method);
        dump($method->deposit($deposit, 0));
    }
}
