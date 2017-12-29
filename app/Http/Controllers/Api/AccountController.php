<?php

namespace App\Http\Controllers\Api;

use App\Pay\Model\Channel;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\Scene;
use App\Pay\Model\WithdrawMethod;
use App\Shop;
use App\ShopFund;
use App\User;
use App\UserFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            'has_pay_card' => $user->pay_card()->count() > 0 ? 1 : 0,
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
        $record = new UserFund();
        $record->user_id = $user->id;
        $record->type = UserFund::TYPE_CHARGE;
        $record->mode = UserFund::MODE_IN;
        $record->amount = $request->amount;
        $record->balance = $user->container->balance + $request->amount;
        $record->status = UserFund::STATUS_SUCCESS;
        /* @var $user User */
        try {
            $record->save();
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
     *   @SWG\Parameter(
     *     name="passwrod",
     *     in="formData",
     *     description="支付密码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'way' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $user = $this->auth->user();
        if (!Hash::check($request->password, $user->pay_password)) {
            return $this->json([], trans("api.error_pay_password"), 0);
        }
        if (!$user->pay_card) {
            return $this->json([], trans("api.error_pay_card"), 0);
        }
        if ($user->container->balance < $request->amount) {
            return $this->json([], trans("api.error_balance"), 0);
        }
        $record = new UserFund();
        $record->user_id = $user->id;
        $record->type = UserFund::TYPE_WITHDRAW;
        $record->mode = UserFund::MODE_OUT;
        $record->amount = $request->amount;
        $record->balance = $user->container->balance - $request->amount;
        $record->status = UserFund::STATUS_SUCCESS;
        try {
            $record->save();
            $result = $user->container->initiateWithdraw(
                $request->amount,
                [
                    'bank_card' => $user->pay_card
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
    public function transfer(Request $request) {
        $shop = Shop::findByEnId($request->shop_id);
        $user = $this->auth->user();
        if ($user->container->balance < $request->amount) {
            return $this->json([], trans("api.error_user_balance"), 0);
        }
        $record = new UserFund();
        $record->user_id = $user->id;
        $record->type = UserFund::TYPE_TRANSFER;
        $record->mode = UserFund::MODE_OUT;
        $record->amount = $request->amount;
        $record->balance = $user->container->balance - $request->amount;
        $record->status = UserFund::STATUS_SUCCESS;
        $shop_record = new ShopFund();
        $shop_record->shop_id = $shop->id;
        $shop_record->type = ShopFund::TYPE_TRANAFER_IN;
        $shop_record->mode = ShopFund::MODE_IN;
        $shop_record->amount = $request->amount;
        $shop_record->balance = $shop->container->balance + $request->amount;
        $shop_record->status = ShopFund::STATUS_SUCCESS;
        try {
            $record->save();
            $shop_record->save();
            $user->container->transfer($shop->container, $request->amount, 0, false, false);
        } catch (\Exception $e){
            Log::info("shop transfer member error:".$e->getMessage());
            return $this->json([], 'error', 0);
        }
        return $this->json();
    }


    /**
     * @SWG\Get(
     *   path="/account/pay-method",
     *   summary="充值方式列表",
     *   tags={"账户"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function payMethods($os, $scene)
    {
        /**
         * @var $channelBind Channel
         */
//        return $this->json([
//            'channel' => 1,
//            'methods' => [['id' => 1, 'label' => '微信']]
//        ]);
        $os = $os == 'unknown' ? $os : ['ios' => DepositMethod::OS_IOS, 'andriod' => DepositMethod::OS_ANDRIOD][$os];

        $scene = Scene::find($scene);
        if ($os && $scene) {
            $channelBind = $this->user->channel;
            if ($channelBind->disabled) {
                //被禁用则启用备用通道
                $channelBind = $channelBind->spareChannel;
            }

            if (!$channelBind) {
                return $this->json(null, '没有可用支付通道', 0);
            }

            $methods = $channelBind->platform->depositMethods()->where('disabled', 0)->select('id', 'os', 'scene', 'show_label')->get();
            //dump($methods);
            return $this->json(['channel' => $channelBind->getKey(), 'methods' => $methods->filter(function ($method) use ($scene, $os) {
                return in_array($scene->getKey(), $method->scene) &&  //支付场景筛选
                    ($method->os == DepositMethod::OS_ANY || $method->os == $os);//不限系统,或系统匹配
            })->map(function ($item) {
                return ['id' => $item['id'], 'label' => $item['show_label']];
            })]);
        } else {
            return $this->json(null, '不存在的场景或系统', 0);
        }
    }

    /**
     * @SWG\Get(
     *   path="/account/withdraw-method",
     *   summary="提现方式列表",
     *   tags={"账户"},
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
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

        if (!$channelBind) {
            return $this->json(null, '没有可用支付通道', 0);
        }

        $methods = $channelBind->platform->withdrawMethods()->where('disabled', 0)->select('id', 'show_label as label')->get();
        if (config('app.debug')) {
            $methods->each(function (&$item) {
                $item['required-params'] = WithdrawMethod::find($item['id'])->getReceiverDescription();
            });
        }

        return $this->json(['channel' => $channelBind->getKey(), 'methods' => $methods]);
    }

    /**
     * @SWG\Get(
     *   path="/account/records",
     *   summary="帐单明细",
     *   tags={"账户"},
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="类型",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="start",
     *     in="query",
     *     description="结束日期",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="size",
     *     in="query",
     *     description="数目",
     *     required=false,
     *     type="number"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function records(Request $request) {
        $data = [];
        $user = $this->auth->user();
        /* @var $user User */
        foreach ($user->funds()->orderBy('id',  'DESC')->paginate($request->size) as $_fund) {
            $data[] = [
                'id' => $_fund->en_id(),
                'type' => (int)$_fund->type,
                'mode' => (int)$_fund->mode,
                'amount' => $_fund->amount,
                'created_at' => strtotime($_fund->created_at)
            ];
        }
        return $this->json(['data' => $data]);
    }

    /**
     * @SWG\Get(
     *   path="/account/records/detail/{id}",
     *   summary="帐单详情",
     *   tags={"账户"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="帐单id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function record_detail($id) {
        $user = $this->auth->user();

        $fund = UserFund::findByEnId($id);
        if (!$fund || $fund->user_id != $user->id) {
            return $this->json([], trans("error_fund"), 0);
        }
        return $this->json([
            'id' => $fund->en_id(),
            'type' => (int)$fund->type,
            'mode' => (int)$fund->mode,
            'amount' => $fund->amount,
            'created_at' => strtotime($fund->created_at),
            'no' => (string)$fund->no,
            'remark' => (string)$fund->remark,
            'balance' => $fund->balance
        ]);
    }

    /**
     * @SWG\Get(
     *   path="/account/records/month",
     *   summary="帐单月数据",
     *   tags={"账户"},
     *   @SWG\Parameter(
     *     name="month",
     *     in="formData",
     *     description="月(2017-12形式)",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function month_data(Request $request) {
        return $this->json(['in' => 0, 'out' => 0]);
    }
}