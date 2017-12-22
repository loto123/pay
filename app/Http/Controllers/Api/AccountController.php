<?php

namespace App\Http\Controllers\Api;

use App\Pay\Model\Channel;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\Scene;
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


    /**
     * @param $os
     * @param $scene
     * @return mixed
     */
    public function payMethods($os, $scene)
    {
        /**
         * @var $channelBind Channel
         */
        $os = $os == 'unknown' ? $os : ['ios' => DepositMethod::OS_IOS, 'andriod' => DepositMethod::OS_ANDRIOD][$os];

        $scene = Scene::find($scene);
        if ($os && $scene) {
            $channelBind = $this->user->channel;
            $channelBind = $channelBind ? $channelBind : Channel::find(1);
            if ($channelBind->disabled) {
                //被禁用则启用备用通道
                $channelBind = $channelBind->spareChannel;
            }

            $methods = $channelBind->platform->depositMethods()->where('disabled', 0)->select('id', 'os', 'scene', 'show_label')->get();

            return $this->json(['channel' => $channelBind->getKey(), 'methods' => $methods->filter(function ($method) use ($scene, $os) {
                return in_array($scene->getKey(), $method->scene) &&  //支付场景筛选
                    ($os == 'unknown' || $method->os == DepositMethod::OS_ANY || $method->os == $os);//未知系统,或不限系统,或系统匹配
            })->mapWithKeys(function ($item) {
                return [$item['id'] => $item['show_label']];
            })]);
        } else {
            return $this->json(null, '不存在的场景或系统', 0);
        }
    }

    public function withdrawMethods()
    {
        /**
         * @var $channelBind Channel
         */
        $channelBind = $this->user->channel;
        $channelBind = $channelBind ? $channelBind : Channel::find(1);
        if ($channelBind->disabled) {
            //被禁用则启用备用通道
            $channelBind = $channelBind->spareChannel;
        }

        return $this->json($channelBind->platform->withdrawMethods()->where('disabled', 0)->select('id', 'show_label as label')->get());

    }
}