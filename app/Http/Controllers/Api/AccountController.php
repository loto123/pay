<?php

namespace App\Http\Controllers\Api;

use App\Pay\Model\Channel;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\Scene;
use App\Pay\Model\WithdrawMethod;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

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
//        $stdClass = new \stdClass();
//        $stdClass->pay_info = 'http://www.alipay.com';
//        return $this->json($stdClass);
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
     * 提现字段信息
     */
    public function withdrawFieldsInfo()
    {
        return $this->json([
            ['fields' => [['name' => '姓名']]],
            ['fields' => [['id_card' => '身份证']]],
            ['fields' => [['main_bank' => '所属银行']], 'select' => [['1' => '	中国银行', '2' => '中国农业银行']]],
            ['fields' => [['branch_bank' => '支行名']]],
            ['fields' => [['province' => '省'], ['city' => '市'], 'select' => [['value' => '1', 'label' => '湖南', 'select' => ['1' => '长沙', '2' => '常德']], ['value' => '2', 'label' => '广东', 'select' => ['1' => '韶关', '2' => '清远']]]]],
            ['fields' => [['card_no' => '卡号']]],
            ['fields' => [['bank_mobile' => '预留手机号']]],
        ]);
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
    public function withdraw(Request $request)
    {
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
            return $this->json([], 'error'.$e->getMessage(), 0);
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
        return $this->json([
            'channel' => 1,
            'methods' => [['id' => 1, 'label' => '微信']]
        ]);
        $os = $os == 'unknown' ? $os : ['ios' => DepositMethod::OS_IOS, 'andriod' => DepositMethod::OS_ANDRIOD][$os];

        $scene = Scene::find($scene);
        if ($os && $scene) {
            $channelBind = $this->user->channel;
            if ($channelBind->disabled) {
                //被禁用则启用备用通道
                $use_spare = true;
                $channelBind = $channelBind->spareChannel;
            }

            $methods = $channelBind->platform->depositMethods()->where('disabled', 0)->select('id', 'os', 'scene', 'show_label')->get();

            return $this->json(['channel' => $channelBind->getKey(), 'methods' => $methods->filter(function ($method) use ($scene, $os) {
                return in_array($scene->getKey(), $method->scene) &&  //支付场景筛选
                    ($os == 'unknown' && $method->os == DepositMethod::OS_ANY || $method->os == $os);//未知系统且不限系统,或系统匹配
            })->mapWithKeys(function ($item) {
                return [$item['id'] => $item['show_label']];
            })]);
        } else {
            return $this->json(null, '不存在的场景或系统', 0);
        }
    }

    /**
     * 充值方式列表
     * @return mixed
     */
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

        return $this->json(['channel' => $channelBind->getKey(), 'methods' => $channelBind->platform->withdrawMethods()->where('disabled', 0)->select('id', 'show_label as label')->get(), 'banks' => [1, 2, 3]]);
    }
}