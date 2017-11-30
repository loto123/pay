<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use JWTAuth;

/**
 * @resource 测试 test
 *
 * Class TestController
 *
 * @package App\Http\Controllers\Api
 */
class TestController extends Controller {

    public function __construct() {
        $this->middleware("jwt.auth");
    }

    /**
     * 测试 api
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json(['success' => $user]);
    }

}