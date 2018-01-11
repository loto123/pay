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
        for ($i = 0; $i < 10; $i++) {
            for ($k = 0; $k < 10; $k++) {
                dump(sprintf('%u', $i ^ $k));
            }
        }
        //return IdConfuse::mixUpDepositId(132, 8, true);
    }
}
