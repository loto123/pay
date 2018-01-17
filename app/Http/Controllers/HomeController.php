<?php

namespace App\Http\Controllers;

use App\Notifications\ConfirmExecuteResult;

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
        dump(ConfirmExecuteResult::fail('执行失败'));
    }
}
