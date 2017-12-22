<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Illuminate\Support\Facades\Validator;

/**
 *
 * @package App\Http\Controllers\Api
 */
class AccountController extends BaseController {

    /**
     * @SWG\Get(
     *   path="/account",
     *   summary="账户余额",
     *   tags={"账户"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $user = $this->auth->user();
        /* @var $user User */
        return $this->json([
            'balance' => (float)$user->container->balance,
            'has_pay_password' => empty($user->pay_password) ? 0 : 1,
            ]);
    }

    /**
     * @SWG\Post(
     *   path="/account/charge",
     *   summary="账户充值",
     *   tags={"账户"},
     *   @SWG\Parameter(
     *     name="way",
     *     in="formData",
     *     description="充值方式",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="amount",
     *     in="formData",
     *     description="转账金额",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function charge(Request $request) {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/account/withdraw",
     *   summary="账户提现",
     *   tags={"账户"},
     *   @SWG\Parameter(
     *     name="way",
     *     in="formData",
     *     description="提现方式",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="amount",
     *     in="formData",
     *     description="转账金额",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function withdraw() {
        return $this->json();
    }

    /**
     * @SWG\Post(
     *   path="/account/transfer",
     *   summary="转账到店铺",
     *   tags={"账户"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="formData",
     *     description="店铺id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="amount",
     *     in="formData",
     *     description="转账金额",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function transfer() {
        return $this->json();
    }
}