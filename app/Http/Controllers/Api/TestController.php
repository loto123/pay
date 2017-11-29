<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use JWTAuth;

class TestController extends Controller {

    public function __construct() {
        $this->middleware("jwt.auth");
    }

    /**
     * test api
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json(['success' => $user]);
    }

}