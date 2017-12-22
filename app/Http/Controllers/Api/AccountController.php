<?php

namespace App\Http\Controllers\Api;

use App\Pay\Model\DepositMethod;
use App\Pay\Model\WithdrawMethod;
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
            'way' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $user = $this->auth->user();
        /* @var $user User */
        try {
            $result = $user->container->initiateDeposit($request->amount, $user->channel, DepositMethod::find($request->way));
        } catch (\Exception $e) {
            return $this->json([], 'error', 0);
        }

        return $this->json(['redirect_url' => $result]);
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
    public function withdraw(Request $request) {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'way' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $user = $this->auth->user();

        try {
            $result = $user->container->initiateWithdraw(
                $request->amount,
                [
                    'branch_bank' => $user->pay_card->bank->name,
                    'bank_no' => $user->pay_card->bank_id,
                    'city' => '广州市',
                    'province' => '广东省',
                    'receiver_account' => $user->pay_card->card_num,
                    'receiver_name' => $user->pay_card->holder_name,
                    'to_public' => 0
                ],
                $user->channel,
                WithdrawMethod::find($request->way),
                0.1
            );
        } catch (\Exception $e) {
            return $this->json([], 'error', 0);
        }
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