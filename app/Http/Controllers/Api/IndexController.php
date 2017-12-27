<?php

namespace App\Http\Controllers\Api;

use App\Pay\Model\Channel;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\Scene;
use App\Pay\Model\WithdrawMethod;
use App\User;
use App\UserFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

/**
 *
 * @package App\Http\Controllers\Api
 */
class IndexController extends BaseController {

    /**
     * @SWG\Get(
     *   path="/index",
     *   summary="首页",
     *   tags={"首页"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $user = $this->auth->user();
        /* @var $user User */
        return $this->json([
            'avatar' => $user->avatar,
            'balance' => $user->container->balance,
            'new_message' => 0
        ]);
    }

}