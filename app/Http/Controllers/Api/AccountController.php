<?php

namespace App\Http\Controllers\Api;

use App\Jobs\SubmitPetSell;
use App\Pay\IdConfuse;
use App\Pay\Model\BillMatch;
use App\Pay\Model\Channel;
use App\Pay\Model\Deposit;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\MasterContainer;
use App\Pay\Model\PayQuota;
use App\Pay\Model\Scene;
use App\Pay\Model\SellBill;
use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawMethod;
use App\Pay\PayLogger;
use App\Pet;
use App\Shop;
use App\ShopFund;
use App\Transfer;
use App\User;
use App\UserFund;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

/**
 *
 * @package App\Http\Controllers\Api
 */
class AccountController extends BaseController
{
    const MINIMUM_WITHDRAW = 0.01; //最低提现金额
    const MINIMUM_RECHARGE = 0.01; //最低充值金额

    /**
     * @SWG\Get(
     *   path="/account",
     *   summary="账户余额",
     *   tags={"账户"},
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="has_pay_password", type="boolean", example=0,description="是否设置了支付密码"),
     *                  @SWG\Property(property="balance", type="double", example=123.4,description="用户余额"),
     *                  @SWG\Property(property="has_pay_card", type="boolean", example=0,description="是否有结算卡"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
     *   summary="购买宠物",
     *   tags={"账户"},
     *   @SWG\Parameter(
     *     name="way",
     *     in="formData",
     *     description="充值方式",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="bill_id",
     *     in="formData",
     *     description="卖单id",
     *     required=true,
     *     type="number"
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="redirect_url", type="string", example="url",description="充值跳转链接"),
     *                  @SWG\Property(property="order_id", type="string", example="123456789",description="订单id,供客户端查询"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function charge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'numeric|min:1',
            'way' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        /* @var $user User */
        $user = $this->auth->user();

        $channel = $user->channel;
        if ($channel->disabled) {
            $channel = $user->channel->spareChannel;
        }
        $method = $channel->platform->depositMethods()->find($request->way);
        if (!$method) {
            return $this->json([], '状态异常,请刷新页面重试', 0);
        }

        $commit = false;
        $message = '';
        $data = [];
        DB::beginTransaction();

        do {
            //取得卖单
            $bill = SellBill::onSale()->where('place_by', '<>', Auth::id())->lockForUpdate()->find($request->bill_id);
            if (!$bill) {
                $message = '该宠物已售出,请重新查询';
                //dump(DB::getQueryLog());
                break;
            }
            $price = $bill->price;

            //限制充值金额
            if ($price < self::MINIMUM_RECHARGE) {
                $message = '最低价格' . self::MINIMUM_RECHARGE . '元';
                break;
            }

            if ($method->maximum_amount > 0 && $price > $method->maximum_amount) {
                $message = $method->show_label . '购买最大金额为' . $method->maximum_amount . '元';
                break;
            }


            try {
                if ($result = $user->container->initiateDeposit($price, $channel, $method, null)) {
                    //新的撮合
                    $match = new BillMatch([
                        'expired_at' => date('Y-m-d H:i:s', time() + BillMatch::BILL_PAY_TIMEOUT * 60),
                        'deposit_id' => $result['deposit_id'],
                    ]);
                    $match->sellBill()->associate($bill);
                    $match->createdBy()->associate(Auth::user());

                    //锁定卖单
                    $bill->locked = true;
                    if ($bill->save() && $match->save()) {
                        $data = ['redirect_url' => $result['pay_info']];
                        $data['order_id'] = IdConfuse::mixUpId($match->getKey(), 20, true);
                        $commit = true;
                    }
                } else {
                    return $this->json([], 'error', 0);
                }
            } catch (\Exception $e) {
                PayLogger::deposit()->error('下单接口错误', [$e->getMessage()]);
                $message = 'error';
                break;
            }
        } while (false);

        $commit ? DB::commit() : DB::rollBack();
        return $this->json($data, $message, (int)$commit);
    }

    /**
     * @SWG\Post(
     *   path="/account/charge_query",
     *   summary="查询购买状态",
     *   tags={"账户"},
     *   @SWG\Parameter(
     *     name="order_id",
     *     in="formData",
     *     description="购买订单id",
     *     required=true,
     *     type="integer"
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="state", type="integer", example="1",description="状态:0待成交,1付款失败,2超时付款,3成交,4已退款,5已超时,6交割失败(宠物,余额,提现环节失败)"),
     *              )
     *          )
     *      ),
     * )
     * @param Request $request
     * @return mixed
     */
    public function charge_query(Request $request)
    {
        $id = $request->post('order_id');
        if (!$id) {
            return $this->json([], '缺少order_id', 0);
        }

        $id = IdConfuse::recoveryId($id, true);
        $buy_order = BillMatch::where([['id', $id], ['user_id', Auth::id()]])->first();
        if (!$buy_order) {
            return $this->json([], '订单不存在', 0);
        }

        return $this->json(['state' => $buy_order->state]);
    }

    /**
     * @SWG\Post(
     *   path="/account/withdraw",
     *   summary="出售宠物",
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
     *     description="出售价格",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="支付密码",
     *     required=true,
     *     type="string"
     *   ),
     *     @SWG\Parameter(
     *     name="pet_id",
     *     in="formData",
     *     description="宠物id",
     *     required=true,
     *     type="integer"
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  description="余额信息",
     *                  @SWG\Property(property="balance", type="float", example=20.00,description="可用余额"),
     *                  @SWG\Property(property="frozen", type="float", example=0.00,description="冻结余额"),
     *                  @SWG\Property(property="price", type="float", example=100.00,description="出售价格"),
     *                  @SWG\Property(property="fee", type="float", example=1.00,description="手续费"),
     *                  @SWG\Property(property="expected_arrival_time", type="string", example="2018-01-29 19:14",description="预计最晚到账时间"),
     *                  @SWG\Property(
     *                      property="receiver",
     *                      type="object",
     *                      description="收款信息,每种提现方式返回不同,以银行卡为例",
     *                      @SWG\Property(property="bank_name", type="string", description="到账银行"),
     *                      @SWG\Property(property="card_tail_number", type="string", description="银行卡尾号")
     *                  )
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|min:0',
            'way' => 'required',
            'password' => 'required',
            'pet_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $user = $this->auth->user();

        //判断当日剩余出售次数
        if ($this->remains_sell_times() <= 0) {
            return $this->json([], '超过今日允许出售次数', 0);
        }

        try {
            if (!$user->check_pay_password($request->password)) {
                return $this->json([], trans("api.error_pay_password"), 0);
            }
        } catch (\Exception $e) {
            return $this->json([], $e->getMessage(), 0);
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

        /**
         * @var $channel Channel
         */
        $channel = $user->channel;
        if ($channel->disabled) {
            $channel = $user->channel->spareChannel;
        }

        $method = $channel->platform->withdrawMethods()->find($request->way);

        if (!$method) {
            return $this->json([], '状态异常,请刷新页面重试', 0);
        }

        $min_quota = max(self::MINIMUM_WITHDRAW, config('tixian_min', 0));
        if ($request->amount < $min_quota) {
            return $this->json([], '最低价格' . $min_quota . '元', 0);
        }

        //判断限额
        $max_quota = min($method->max_quota, config('tixian_max', 0));
        if ($max_quota > 0 && $max_quota < $request->amount) {
            return $this->json([], '单笔收款最高为' . $max_quota . '元', 0);
        }


        if ($method->targetPlatform->getKey() == 0) {
            //提现到银行卡
            if (!$user->pay_card) {
                return $this->json([], trans("api.error_pay_card"), 0);
            }

            if (!$channel->platform->isCardSupport($user->pay_card)) {
                return $this->json([], '暂不支持该银行', 0);
            }

            $receiver_info = ['bank_card' => $user->pay_card];
        } else {
            //其它提现方式,待扩展...
            $receiver_info = [];
        }


        //计算手续费
        $fee = round($request->amount * $method->fee_percent / 100 + $method->fee_money, 2);

        if (bcsub($request->amount, $fee, 2) <= 0) {
            return $this->json([], '提现金额必须大于0', 0);
        }


        DB::beginTransaction();
        $commit = false;
        $error = '';

        do {
            try {
                //取得宠物
                $pet = Pet::where([
                    ['user_id', Auth::id()],
                    ['status', Pet::STATUS_HATCHED]
                ])->lockForUpdate()->find($request->pet_id);

                if (!$pet) {
                    $error = '该宠物不能出售';
                    break;
                }

                $result = $user->container->initiateWithdraw(
                    $request->amount,
                    $receiver_info,
                    $channel,
                    $method,
                    $fee
                );

                if ($result['success']) {
                    //下卖单
                    $bill = new SellBill(
                        [
                            'price' => $request->amount,
                            'valid_time' => date('Y-m-d H:i:s', time() + SellBill::VALID_MINUTES * 60),
                            'withdraw_id' => $result['withdraw_id']
                        ]
                    );

                    //锁定宠物
                    $pet->status = Pet::STATUS_LOCKED;
                    $record->no = $result['withdraw_id'];

                    $bill->pet()->associate($pet);
                    $bill->placeBy()->associate(Auth::user());


                    $pet->save();
                    $bill->save();
                    $record->save();
                    $commit = true;
                } else {
                    $error = 'error';
                    break;
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
                break;
            }
        } while (false);

        $commit ? DB::commit() : DB::rollBack();
        if (!$commit) {
            return $this->json([], $error, 0);
        }

        //加入延迟提现队列
        SubmitPetSell::dispatch($bill)->delay(Carbon::now()->addMinutes(SellBill::VALID_MINUTES))->onQueue('withdraw');

        $container = MasterContainer::find($user->container->getKey());
        $response = ['balance' => $container->balance, 'frozen' => $container->frozen_balance, 'price' => $request->amount, 'fee' => $fee, 'expected_arrival_time' => date('Y-m-d H:i', time() + 60 * 60 * 2)];
        $show_receiver_info = [];
        if ($method->targetPlatform->getKey() == 0) {
            //仅银行卡提现有
            $show_receiver_info['bank_name'] = $user->pay_card->bank->name;
            $show_receiver_info['card_tail_number'] = substr($user->pay_card->card_num, -4);
        } else {
            //..
        }
        $response['receiver'] = $show_receiver_info;
        return $this->json($response);

    }

    /**
     * 今日剩余宠物出售次数
     * @return int
     */
    private function remains_sell_times()
    {
        $max_sell_times = config('max_times_to_sell_pets', 3);
        if ($max_sell_times <= 0) {
            return 0;
        } else {
            return max(0, $max_sell_times - SellBill::where([['place_by', Auth::id()], ['created_at', '>=', date('Y-m-d 0:0:0')]])->count());
        }
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
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="支付密码",
     *     required=true,
     *     type="string"
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|min:0',
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $shop = Shop::findByEnId($request->shop_id);
        $user = $this->auth->user();
        if (!$shop || $shop->manager_id != $user->id) {
            return $this->json([], 'error', 0);
        }
        try {
            if (!$user->check_pay_password($request->password)) {
                return $this->json([], trans("api.error_pay_password"), 0);
            }
        } catch (\Exception $e) {
            return $this->json([], $e->getMessage(), 0);
        }
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
        } catch (\Exception $e) {
            Log::info("shop transfer member error:" . $e->getMessage());
            return $this->json([], 'error', 0);
        }
        return $this->json();
    }


    /**
     * @SWG\Get(
     *   path="/account/pay-methods/{os}/{scene}",
     *   summary="充值方式列表:占位符{os}表示操作系统:andriod,ios,unknown(未知), {scene}表示支付场景id，见后台 支付管理-支付场景",
     *   tags={"账户"},
     *   @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(
     *                      property="channel",
     *                      type="integer",
     *                      example=1,
     *                      description="当前支付通道"
     *                  ),
     *                  @SWG\Property(
     *                      property="scene",
     *                      type="integer",
     *                      example=1,
     *                      description="当前支付场景",
     *                  ),
     *                  @SWG\Property(
     *                      property="methods",
     *                      type="array",
     *                      description="购买方式列表",
     *                      @SWG\Items(
     *                          @SWG\Property(property="id", type="integer", description="购买方式id"),
     *                          @SWG\Property(property="label", type="string", description="展示文本"),
     *                          @SWG\Property(property="interact_form", type="string", description="客户端交互形式",example="http_redirect"),
     *                      ),
     *                  ),
     *              )
     *          )
     *      ),
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
        $os = $os == 'unknown' ? $os : ['ios' => DepositMethod::OS_IOS, 'android' => DepositMethod::OS_ANDROID][$os];

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

            $methods = $channelBind->platform->depositMethods()->where('disabled', 0)->select('id', 'os', 'scene', 'show_label', 'interact_form')->get();
            $data = array_values($methods->filter(function ($method) use ($scene, $os) {
                return in_array($scene->getKey(), $method->scene) &&  //支付场景筛选
                    ($method->os == DepositMethod::OS_ANY || $method->os == $os);//不限系统,或系统匹配
            })->map(function ($item) {
                return ['id' => $item['id'], 'label' => $item['show_label'], 'interact_form' => $item['interact_form']];
            })->all());
            //dump($data);
            return $this->json(['channel' => $channelBind->getKey(), 'scene' => $scene->getKey(), 'methods' => $data]);
        } else {
            return $this->json(null, '不存在的场景或系统', 0);
        }
    }

    /**
     * @SWG\Get(
     *   path="/account/withdraw-methods",
     *   summary="提现方式列表",
     *   tags={"账户"},
     *   @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  description="余额信息",
     *                  @SWG\Property(property="channel", type="integer", example=1,description="用户当前使用的支付通道"),
     *                  @SWG\Property(property="methods", type="array", description="提现方式列表",
     *                  @SWG\Items(
     *                  @SWG\Property(property="id", type="integer", description="收款方式id"),
     *                  @SWG\Property(property="label", type="string", description="展示文本"),
     *                  @SWG\Property(property="fee_money", type="float", example="0.3",description="手续费固定金额"),
     *                  @SWG\Property(property="fee_percent", type="float", example="1",description="手续费百分比"),
     *                  @SWG\Property(property="my_max_quota", type="float", example="100.00",description="我的最大价格"),
     *                  @SWG\Property(property="quota_list", type="array", example="100,200,300",description="价格列表",@SWG\Items()),
     *                  @SWG\Property(property="required-params", type="object", description="该方式需要的必要参数说明。每种方式不同,key/描述。仅调试模式返回"),
     *                  ),
     *                  ),
     *                  @SWG\Property(property="remains_times", type="integer", example=3,description="用户今日剩余可出售次数"),
     *              )
     *          )
     *      ),
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

        $methods = $channelBind->platform->withdrawMethods()->where('disabled', 0)->select('id', 'show_label as label', 'fee_money', 'fee_percent', 'max_quota')->get();
        if (config('app.debug')) {
            $methods->each(function (&$item) {
                $item['required-params'] = WithdrawMethod::find($item['id'])->getReceiverDescription();
            });
        }

        //提现额度
        $user_balance = (float)$this->user->container->balance;
        if (!empty($methods) && count($methods) > 0) {
            $methods->each(function (&$item) use($user_balance){
                $method_quota_list = PayQuota::getPayQuotas(1, $item->id);
                if ($method_quota_list) {
                    $item['quota_list'] = $method_quota_list;
                } else {
                    $item['quota_list'] = [];
                }

                //介于充值列表最大值和最小值之间
                if(($user_balance < config('tixian_min')) || $user_balance > config('tixian_max')) {
                    $my_max_quota = 0;
                } else {
                    $my_max_quota = $user_balance;
                }
                $item['my_max_quota'] = $my_max_quota;
                unset($item['max_quota']);
            });
        }

        return $this->json(['channel' => $channelBind->getKey(), 'methods' => $methods, 'remains_times' => $this->remains_sell_times()]);
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
     *     type="array",
     *     @SWG\Items(
     *      type="integer"
     *     )
     *   ),
     *   @SWG\Parameter(
     *     name="start",
     *     in="query",
     *     description="结束日期",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="数目",
     *     required=false,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="上一次记录的最后一条ID,默认0",
     *     required=false,
     *     type="number"
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="count", type="integer", example=20,description="总数"),
     *                  @SWG\Property(
     *                      property="data",
     *                      type="array",
     *                  @SWG\Items(
     *                  @SWG\Property(property="id", type="string", example="12345676789",description="记录id"),
     *                  @SWG\Property(property="type", type="integer", example=1,description="帐单类别 0=充值,1=提现,2=交易收入,3=交易支出,4=转账到店铺,5=店铺转入,6=交易手续费,7=提现手续费,8=大赢家茶水费"),
     *                  @SWG\Property(property="mode", type="integer", example=1,description="收入支出 0=收入 1=支出"),
     *                  @SWG\Property(property="amount", type="double", example=9.9,description="金额"),
     *                  @SWG\Property(property="created_at", type="integer", example=152000000,description="创建时间戳"),
     *                  @SWG\Property(property="fee", type="double", example=9.9,description="手续费"),
     *                  )
     *                  )
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function records(Request $request)
    {
        $data = [];
        $user = $this->auth->user();
        /* @var $user User */
        $query = UserFund::with(['charge_order', 'withdraw_order'])->where("user_id", $user->id)->where(function ($query1) {
            $query1->orWhere(function ($query2) {
                $query2->where('type', UserFund::TYPE_CHARGE)->whereHas("charge_order", function ($q) {
                    $q->where("state", Deposit::STATE_COMPLETE);
                });
            })->orWhere(function ($query3) {
                $query3->where('type', UserFund::TYPE_WITHDRAW)->whereHas("withdraw_order", function ($q) {
                    $q->where("state", Withdraw::STATE_COMPLETE);
                });
            })->whereNotIn("type", [UserFund::TYPE_CHARGE, UserFund::TYPE_WITHDRAW], 'or');
        });
        if ($request->type !== null) {
            if (is_array($request->type)) {

                $query->whereIn("type", $request->type);
            } else {
                $query->where("type", $request->type);
            }
        }
        if ($request->start) {
            $start = date("Y-m-d H:i:s", strtotime($request->start . " +1 month"));
            $query->where("created_at", '<', $start);
        }
        $count = $query->count();
        $query->orderBy('id', 'DESC')->limit($request->input('limit', 20));
        if ($request->offset) {
            $query->where("id", "<", UserFund::decrypt($request->offset));
        }
        foreach ($query->get() as $_fund) {
            $data[] = [
                'id' => $_fund->en_id(),
                'type' => (int)$_fund->type,
                'mode' => (int)$_fund->mode,
                'amount' => $_fund->amount,
                'created_at' => strtotime($_fund->created_at),
                'fee' => $_fund->withdraw_order ? $_fund->withdraw_order->system_fee : 0
            ];
        }
        return $this->json(['count' => (int)$count, 'data' => $data]);
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
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="id", type="string", example="12345676789",description="记录id"),
     *                  @SWG\Property(property="type", type="integer", example=1,description="帐单类别 0=充值,1=提现,2=交易收入,3=交易支出,4=转账到店铺,5=店铺转入,6=交易手续费,7=提现手续费,8=大赢家茶水费"),
     *                  @SWG\Property(property="mode", type="integer", example=1,description="收入支出 0=收入 1=支出"),
     *                  @SWG\Property(property="amount", type="double", example=9.9,description="金额"),
     *                  @SWG\Property(property="created_at", type="integer", example=152000000,description="创建时间戳"),
     *                  @SWG\Property(property="no", type="string", example="123123",description="交易单号"),
     *                  @SWG\Property(property="remark", type="string", example="xxxx",description="备注"),
     *                  @SWG\Property(property="balance", type="double", example=9.9,description="交易后余额"),
     *                  @SWG\Property(property="fee", type="double", example=9.9,description="手续费"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function record_detail($id)
    {
        $user = $this->auth->user();

        $fund = UserFund::findByEnId($id);
        /* @var $fund UserFund */
        if (!$fund || $fund->user_id != $user->id) {
            return $this->json([], trans("error_fund"), 0);
        }
        return $this->json([
            'id' => $fund->en_id(),
            'type' => (int)$fund->type,
            'mode' => (int)$fund->mode,
            'amount' => $fund->amount,
            'created_at' => strtotime($fund->created_at),
            'no' => $fund->no ? Transfer::encrypt($fund->no) : $fund->en_id(),
            'remark' => (string)$fund->remark,
            'balance' => $fund->balance,
            'fee' => $fund->withdraw_order ? $fund->withdraw_order->system_fee : 0

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
     *   @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="类型",
     *     required=false,
     *     type="array",
     *     @SWG\Items(
     *      type="integer"
     *     )
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="in", type="double", example=123.4,description="用户当月收入总数"),
     *                  @SWG\Property(property="out", type="double", example=123.4,description="用户当月支出总数"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function month_data(Request $request)
    {
        $validator = Validator::make($request->all(),
            ['month' => 'required|regex:/^\d{4}-\d{2}$/']
        );
        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $user = $this->auth->user();
        $query_func = function($type) use ($user, $request) {
            $query = UserFund::where("user_id", $user->id)->with(['charge_order', 'withdraw_order'])->where(function ($query1) {
                $query1->orWhere(function ($query2) {
                    $query2->where('type', UserFund::TYPE_CHARGE)->whereHas("charge_order", function ($q) {
                        $q->where("state", Deposit::STATE_COMPLETE);
                    });
                })->orWhere(function ($query3) {
                    $query3->where('type', UserFund::TYPE_WITHDRAW)->whereHas("withdraw_order", function ($q) {
                        $q->where("state", Withdraw::STATE_COMPLETE);
                    });
                })->whereNotIn("type", [UserFund::TYPE_CHARGE, UserFund::TYPE_WITHDRAW], 'or');
            })->where("created_at", ">=", date("Y-m-01", strtotime($request->month)))->where("created_at", "<", date("Y-m-01", strtotime($request->month . " +1 month")));
            if ($request->type) {
                $query->whereIn("type", $request->type);
            }
            return $query->where("mode", $type)->sum("amount");
        };

        return $this->json(['in' => $query_func(UserFund::MODE_IN), 'out' => $query_func(UserFund::MODE_OUT)]);
    }


    /**
     * @SWG\Get(
     *   path="/account/deposit_quotas",
     *   summary="充值金额列表",
     *   tags={"账户"},
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="quota_list", type="array", description="充值金额",
     *                  @SWG\Items(
     *                  @SWG\Property(property="1", type="integer", example="100"),
     *                  @SWG\Property(property="2", type="integer", example="200"),
     *                  ),
     *                )
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function depositQuotaList()
    {
        $quota_list = PayQuota::getPayQuotas(2);
        if ($quota_list) {
            return $this->json(['quota_list' => $quota_list]);
        } else {
            return $this->json([], '请求失败', 0);
        }
    }
}