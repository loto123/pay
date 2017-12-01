<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use JWTAuth;

/**
 * @resource 店铺
 *
 * Class ShopController
 *
 * @package App\Http\Controllers\Api
 */
class ShopController extends Controller {

    public function __construct() {
        $this->middleware("jwt.auth");
    }

    /**
     * 创建店铺
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json(['success' => $user]);
    }

}