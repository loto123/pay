<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use JWTAuth;

/**
 *
 * @package App\Http\Controllers\Api
 */
class ShopController extends Controller {

    public function __construct() {
        $this->middleware("jwt.auth");
    }

    /**
     * @SWG\Post(
     *   path="/shop/create",
     *   summary="创建店铺",
     *   operationId="getCustomerRates",
     *   tags={"店铺"},
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="店铺名",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Filter results based on query string value.",
     *     required=false,
     *     enum={"active", "expired", "scheduled"},
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json(['success' => $user]);
    }

    /**
     * 收益方式列表
     *
     * @return \Illuminate\Http\Response
     */
    public function types() {
        return response()->json(['code' => 0, 'msg' => '', 'data' => [
            [0 => '大赢家', 1 => '小赢家']
        ]]);
    }

}