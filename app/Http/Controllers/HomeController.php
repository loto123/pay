<?php

namespace App\Http\Controllers;

use App\Pay\IdConfuse;

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
        for ($i = 1; $i < 100; $i++) {
            echo $i, ' recovery: ', IdConfuse::mixUpId($i, 8, true), '<br/>';
        }
    }
}
