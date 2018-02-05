<?php

namespace App\Http\Controllers\Api;

use App\Pay\Model\PayFactory;
use App\Pet;
use App\PetRecord;
use App\Profit;
use App\Shop;
use App\TipRecord;
use App\Transfer;
use App\TransferRecord;
use App\TransferUserRelation;
use App\UserFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use JWTAuth;
use Skip32;
use Validator;

class TransferController extends BaseController
{
//    public function __construct()
//    {
//        $this->middleware("jwt.auth");
//    }

    /**
     * @SWG\Post(
     *   path="/transfer/create",
     *   summary="发起交易",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="formData",
     *     description="店铺ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="price",
     *     in="formData",
     *     description="单价",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="comment",
     *     in="formData",
     *     description="备注",
     *     required=false,
     *     type="string"
     *   ),
     *    @SWG\Parameter(
     *     name="joiner",
     *     in="formData",
     *     description="参与交易人",
     *     required=false,
     *     type="array",
     *     @SWG\Items(
     *             type="string"
     *      )
     *   ),
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
     *                  @SWG\Property(property="id", type="string", example="1234567",description="交易id"),
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
    public function create(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(),
            [
                'shop_id' => 'bail|required',
                'price' => 'bail|required|numeric|between:0.1,99999',
                'comment' => 'bail|max:200',
                'joiner' => 'bail|array',
            ],
            [
                'required' => trans('trans.required'),
                'numeric' => trans('trans.numeric'),
                'between' => trans('trans.between'),
                'max' => trans('trans.comment.max')
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $shop = Shop::findByEnId($request->shop_id);
        if (!$shop) {
            return $this->json([], trans('trans.shop_not_exist'), 0);
        }
        if ($shop->status == 2) {
            return $this->json([], trans('trans.shop_is_frozen'), 0);
        }
        if ($shop->active == 0) {
            return $this->json([], trans('trans.shop_not_allow_transfer'), 0);
        }
//        $wallet = PayFactory::MasterContainer();
        $wallet = $shop->container->newSettlement();
        $wallet->save();
        $transfer = new Transfer();
        $transfer->shop_id = $shop->id;
        $transfer->user_id = $user->id;
        $transfer->price = $request->price;
        $transfer->comment = $request->input('comment', '');
//        if ($shop->type == 0) {
//            $transfer->tip_type = 1;
//            $transfer->tip_amount = $shop->type_value;
//        }
//        if ($shop->type == 1) {
//            $transfer->tip_type = 2;
//            $transfer->tip_percent = $shop->type_value * 100;
//        }
        $transfer->tip_type = 2;
        $transfer->tip_percent = $shop->fee;
        $transfer->fee_percent = config('platform_fee_percent');
        $transfer->container_id = $wallet->id;
        //交易关系包含自己
        $joiners = $request->input('joiner', []);
        foreach ($joiners as $key => $value) {
            $joiners[$key] = Skip32::decrypt("0123456789abcdef0123", $value);
        }
        array_push($joiners, $user->id);
        if ($transfer->save()) {
            //保存交易关系
            foreach ($joiners as $item) {
                if (!$transfer->joiner()->where('user_id', $item)->exists()) {
                    $relation = new TransferUserRelation();
                    $relation->transfer_id = $transfer->id;
                    $relation->user_id = $item;
                    $relation->save();
                }
            }
            return $this->json(['id' => $transfer->en_id()], trans('trans.save_success'), 1);
        } else {
            return $this->json([], trans('trans.save_failed'), 0);
        }
    }

    /**
     * @SWG\GET(
     *   path="/transfer/show",
     *   summary="交易详情",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="transfer_id",
     *     in="formData",
     *     description="交易ID",
     *     required=true,
     *     type="string"
     *   ),
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
     *                  @SWG\Property(property="id", type="string", example="1234567",description="交易红包id"),
     *                  @SWG\Property(property="shop_id", type="string", example="1234567", description="店铺ID"),
     *                  @SWG\Property(property="price", type="double", example=9.9, description="单价"),
     *                  @SWG\Property(property="amount", type="double", example=9.9, description="红包余额"),
     *                  @SWG\Property(property="comment", type="string", example="大吉大利，恭喜发财", description="备注"),
     *                  @SWG\Property(property="status", type="int", example="1", description="1 待结算 2 已平账 3 已关闭"),
     *                  @SWG\Property(property="allow_cancel", type="boolean", example=true, description="是否允许撤销"),
     *                  @SWG\Property(property="allow_remind", type="boolean", example=false, description="是否允许提醒好友"),
     *                  @SWG\Property(
     *                      property="user",
     *                      type="object",
     *                      description="发起交易红包人信息",
     *                      @SWG\Property(property="id", type="string", example="1234567",description="发起交易红包人id"),
     *                      @SWG\Property(property="name", type="string", example="1234567", description="发起交易红包人昵称"),
     *                      @SWG\Property(property="avatar",type="string", example="url", description="发起交易红包人头像"),
     *                  ),
     *                  @SWG\Property(
     *                      property="record",
     *                      type="array",
     *                      description="交易记录",
     *                      @SWG\Items(
     *                          @SWG\Property(property="id", type="string", example="1234567",description="交易记录ID"),
     *                          @SWG\Property(property="amount", type="double", example=9.9, description="交易金额"),
     *                          @SWG\Property(property="eggs", type="int", example=9, description="产生宠物蛋数量"),
     *                          @SWG\Property(property="avatar",type="string", example="url", description="实际交易金额"),
     *                          @SWG\Property(property="stat", type="int", example="1", description="状态 0 未知 1 付钱 2 提钱 3 撤回"),
     *                          @SWG\Property(property="created_at", type="string", example="2017-12-22 10:19:23",description="交易记录时间"),
     *                          @SWG\Property(
     *                              property="user",
     *                              type="object",
     *                              description="发起交易人信息",
     *                              @SWG\Property(property="id", type="string", example="1234567",description="发起交易人id"),
     *                              @SWG\Property(property="name", type="string", example="1234567", description="发起交易人昵称"),
     *                              @SWG\Property(property="avatar",type="string", example="url", description="发起交易人头像"),
     *                          )
     *                      )
     *                  ),
     *                  @SWG\Property(
     *                      property="joiner",
     *                      type="array",
     *                      description="交易参与人列表",
     *                      @SWG\Items(
     *                          @SWG\Property(
     *                              property="user",
     *                              type="object",
     *                              description="交易参与人信息",
     *                              @SWG\Property(property="id", type="string", example="1234567",description="交易参与人id"),
     *                              @SWG\Property(property="name", type="string", example="1234567", description="交易参与人昵称"),
     *                              @SWG\Property(property="avatar",type="string", example="url", description="交易参与人头像"),
     *                          )
     *                      )
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
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required'
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $transferObj = Transfer::findByEnId($request->transfer_id);
        if (!$transferObj) {
            return $this->json([], trans('trans.trans_not_exist'), 0);
        }

        $user = JWTAuth::parseToken()->authenticate();

//        if (!$transferObj->shop->shop_user()->where('user_id', $user->id)->exists()) {
//            return $this->json([], trans('trans.trans_permission_deny'), 0);
//        }

        $transfer = Transfer::where('id', $transferObj->id)->withCount('joiner')->with(['user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }, 'record' => function ($query) {
            $query->select('id', 'transfer_id', 'user_id', 'amount', 'real_amount', 'stat', 'created_at')->orderBy('created_at', 'DESC');
        }, 'record.user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }, 'joiner' => function ($query) {
            $query->select('transfer_id', 'user_id');
        }, 'joiner.user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }])->select('id', 'shop_id', 'user_id', 'price', 'amount', 'comment', 'status')->first();

        //装填响应数据
        //是否允许撤销交易
        $transfer->allow_cancel = false;
        if (!$transfer->record()->exists() && !$transfer->tips()->exists() && $transfer->user->id == $user->id) {
            $transfer->allow_cancel = true;
        }
        //是否允许提醒好友
        $transfer->allow_remind = false;
        if ($transferObj->shop->shop_user()->where('user_id', $user->id)->exists()) {
            $transfer->allow_remind = true;
        }
        $transfer->id = $transfer->en_id();
        $transfer->allow_reward = false;
        if (config('shop_fee_status')) {
            $transfer->allow_reward = true;
        }
        $transfer->shop_id = $transfer->shop->en_id();
        $transfer->user->id = $transfer->user->en_id();
        foreach ($transfer->record as $key => $record) {
            $transfer->record[$key]->allow_cancel = false;
            if ($transfer->record[$key]->stat == 2 && $transfer->record[$key]->user_id == $user->id) {
                $transfer->record[$key]->allow_cancel = true;
            }
            $transfer->record[$key]->user->id = $record->user->en_id();
            $transfer->record[$key]->eggs = $record->pet_record()->count();
            unset($transfer->record[$key]->transfer_id);
            unset($transfer->record[$key]->user_id);
        }
        foreach ($transfer->joiner as $key => $item) {
            $transfer->joiner[$key]->user->id = $item->user->en_id();
            unset($transfer->joiner[$key]->transfer_id);
            unset($transfer->joiner[$key]->user_id);
        }
        unset($transfer->user_id);
        unset($transfer->shop);
        return $this->json($transfer, 'ok', 1);
    }

    /**
     * @SWG\Post(
     *   path="/transfer/validate",
     *   summary="验证交易数据(放钱的)",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="transfer_id",
     *     in="formData",
     *     description="交易ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="points",
     *     in="formData",
     *     description="积分",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function valid(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
                'points' => 'bail|required|integer|between:1,99999',
//                'action' => ['bail', 'required', Rule::in(['put', 'get'])],
            ],
            [
                'required' => trans('trans.required'),
                'integer' => trans('trans.integer'),
                'between' => trans('trans.between'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return $this->json([], trans('trans.trans_not_exist'), 0);
        }
        if ($transfer->status == 3) {
            return $this->json([], trans('trans.trans_already_closed'), 0);
        }
        if ($user->balance < ($request->points * $transfer->price)) {
            return $this->json([], trans('trans.user_not_enough_money'), 0);
        }
        return $this->json([], 'ok', 1);
    }

    /**
     * @SWG\Post(
     *   path="/transfer/realget",
     *   summary="提钱实际获取",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="transfer_id",
     *     in="formData",
     *     description="交易ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="points",
     *     in="formData",
     *     description="积分",
     *     required=true,
     *     type="integer"
     *   ),
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
     *                  @SWG\Property(property="amount", type="double", example=9.9,description="交易获得"),
     *                  @SWG\Property(property="real_amount", type="double", example=9.9, description="实际获得"),
     *                  @SWG\Property(property="fee_total", type="double", example=9.9, description="手续费")
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
    public function realGet(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
                'points' => 'bail|required|integer|between:1,99999',
            ],
            [
                'required' => trans('trans.required'),
                'integer' => trans('trans.integer'),
                'between' => trans('trans.between'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return $this->json([], trans('trans.trans_not_exist'), 0);
        }
        if ($transfer->status == 3) {
            return $this->json([], trans('trans.trans_already_closed'), 0);
        }
        $amount = bcmul($request->points, $transfer->price, 2);
        $tips = 0;
        if ($transfer->tip_percent > 0 && config('shop_fee_status')) {
            $tips = bcdiv(bcmul($transfer->tip_percent, $amount, 2), 100, 2);
        }
        //收手续费
        $fee_amount = 0;
        if ($transfer->fee_percent) {
            $fee_amount = bcdiv(bcmul($amount, $transfer->fee_percent, 2), 100, 2);
        }
        $real_amount = bcsub(bcsub($amount, $tips, 2), $fee_amount, 2);
        $fee_total = bcadd($fee_amount, $tips, 2);
        return $this->json(['amount' => $amount, 'real_amount' => $real_amount, 'fee_total' => $fee_total], 'ok', 1);
    }


    /**
     * @SWG\Post(
     *   path="/transfer/trade",
     *   summary="交易",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="transfer_id",
     *     in="formData",
     *     description="交易ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="points",
     *     in="formData",
     *     description="积分",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="action",
     *     in="formData",
     *     description="action value:put(付钱) or get(拿钱)",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="pay_password",
     *     in="formData",
     *     description="支付密码 当action=put时必须存在此参数",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function trade(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
                'points' => 'bail|required|integer|between:1,99999',
                'action' => ['bail', 'required', Rule::in(['put', 'get'])],
                'pay_password' => 'required_if:action,put',
            ],
            [
                'required' => trans('trans.required'),
                'integer' => trans('trans.integer'),
                'between' => trans('trans.between'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return $this->json([], trans('trans.trans_not_exist'), 0);
        }
        if (!$transfer->shop->shop_user()->where('user_id', $user->id)->exists()) {
            return $this->json([], trans('trans.trans_permission_deny'), 0);
        }
        if ($transfer->status == 3) {
            return $this->json([], trans('trans.trans_already_closed'), 0);
        }
        DB::beginTransaction();
        try {
            $record = new TransferRecord();
            $record->transfer_id = $transfer->id;
            $record->user_id = $user->id;
            $record->amount = bcmul($request->points, $transfer->price, 2);
            $record->points = $request->points;
            $record->fee_amount = 0;
            //放钱
            if ($request->action == 'put') {
                if ($user->balance < $record->amount) {
                    return $this->json([], trans('trans.user_not_enough_money'), 0);
                }
                //验证支付密码
                try {
                    $user->check_pay_password($request->input('pay_password'));
                } catch (\Exception $e) {
                    return $this->json([], $e->getMessage(), 0);
                }
                $record->stat = 1;
                //用户减钱
//                $user->balance = $user->balance - $record->real_amount;
                //容器转账
//                $user_container = PayFactory::MasterContainer($user->container->id);
//                $transfer_container = PayFactory::MasterContainer($transfer->container->id);
//                $pay_transfer = $user_container->transfer($transfer_container, $record->amount, 0, 0, 0);
                $pay_transfer = $user->container->transfer($transfer->container, $record->amount, 0, 0, 0);
                if (!$pay_transfer) {
                    DB::rollBack();
                    return $this->json([], trans('trans.trade_failed'), 0);
                }
                //账单明细
                $found = new UserFund();
                $found->user_id = $user->id;
                $found->status = UserFund::STATUS_SUCCESS;
                $found->type = UserFund::TYPE_TRADE_OUT;
                $found->mode = UserFund::MODE_OUT;
                $found->amount = $record->amount;
                $found->save();

                //红包加钱
                $transfer->amount = bcadd($transfer->amount, $record->amount, 2);
                $record->real_amount = bcmul($record->amount, -1, 2);
                $record->amount = bcmul($record->amount, -1, 2);
            }
            //拿钱
            if ($request->action == 'get') {
                if ($transfer->amount < $record->amount) {
                    return $this->json([], trans('trans.not_enough_money'), 0);
                }
                $record->stat = 2;
                //收茶水费
                $tips = 0;
                $profit_shares = [];
//                if ($transfer->tip_type == 2 && $transfer->tip_percent > 0) {
                if ($transfer->tip_percent > 0 && config('shop_fee_status')) {
                    $tips = bcdiv(bcmul($transfer->tip_percent, $record->amount, 2), 100, 2);
                    //红包茶水费金额增加
                    $transfer->tip_amount = bcadd($transfer->tip_amount, $tips, 2);
                    //生成茶水费记录
                    $tip = new TipRecord();
                    $tip->shop_id = $transfer->shop_id;
                    $tip->transfer_id = $transfer->id;
                    $tip->user_id = $user->id;
                    $tip->amount = $tips;
                    //增加店铺余额
//                    $shop = Shop::find($transfer->shop_id);
//                    $shop->frozen_balance = $shop->frozen_balance + $tips;
//                    $shop->save();
                    //分润
                    if ($transfer->shop && $transfer->shop->container) {
//                        $receiver = PayFactory::MasterContainer($transfer->shop->container->id);
                        $receiver = $transfer->shop->container;
                        if ($tip->amount > 0) {
                            $profit_shares[] = PayFactory::profitShare($receiver, $tip->amount, true);
                        }
                    }
                }
                //收手续费
                $proxy_fee = 0;
                if ($transfer->fee_percent) {
                    //手续费
                    $record->fee_amount = bcdiv(bcmul($record->amount, $transfer->fee_percent, 2), 100, 2);
                    //红包手续费金额
                    $transfer->fee_amount = bcadd($transfer->fee_amount, $record->fee_amount, 2);
                    //代理分润
                    if ($user->parent && $user->parent->percent) {
//                        $user_receiver = PayFactory::MasterContainer($user->parent->container->id);
                        //分润至代理分润账户
                        $user_receiver = $user->parent->proxy_container;
                        if ($user_receiver) {
                            $proxy_fee = bcdiv(bcmul(strval($record->fee_amount), strval($user->parent->percent), 2), '100', 2);
                            if ($proxy_fee > 0) {
                                $profit_shares[] = PayFactory::profitShare($user_receiver, $proxy_fee, true);
                            }
                        }
                    }
                }
                //实际获得
                $record->real_amount = bcsub(bcsub($record->amount, $record->fee_amount, 2), $tips, 2);
                //用户加钱
//                $user->balance = $user->balance + $record->real_amount;
                //红包减钱
                $transfer->amount = bcsub($transfer->amount, $record->amount, 2);
                //容器转账
//                $user_container = PayFactory::MasterContainer($user->container->id);
//                $transfer_container = PayFactory::MasterContainer($transfer->container->id);
//                $pay_transfer = $transfer_container->transfer($user_container, $record->amount - $tips, $record->fee_amount - $proxy_fee, 0, 0, $profit_shares);
                $pay_transfer = $transfer->container->transfer($user->container, $record->amount, bcsub($record->fee_amount, $proxy_fee, 2), 0, 0, $profit_shares);
                if (!$pay_transfer) {
                    Log::error('拿钱失败,容器转账失败', [$transfer->container->getKey(), $user->container->getKey(), bcsub($record->amount, $tips, 2), bcsub($record->fee_amount, $proxy_fee, 2), json_encode($profit_shares)]);
                    DB::rollBack();
                    return $this->json([], trans('trans.trade_failed'), 0);
                }

                //账单明细
                $found = new UserFund();
                $found->user_id = $user->id;
                $found->status = UserFund::STATUS_SUCCESS;
                $found->type = UserFund::TYPE_TRADE_IN;
                $found->mode = UserFund::MODE_IN;
                $found->amount = $record->real_amount;
                $found->save();
                //账单明细
                $found = new UserFund();
                $found->user_id = $user->id;
                $found->status = UserFund::STATUS_SUCCESS;
                $found->type = UserFund::TYPE_TRADE_FEE;
                $found->mode = UserFund::MODE_OUT;
                $found->amount = bcadd($record->fee_amount, $tips, 2);
                $found->save();
            }
//            $user->save();
            //判断红包状态
            if ($transfer->amount == 0) {
                $transfer->status = 2;
            } else {
                $transfer->status = 1;
            }
            $transfer->save();
            if (isset($pay_transfer) && $pay_transfer) {
                $record->pay_transfer_id = $pay_transfer->id;
            }
            $record->save();
            //保存茶水费记录
            if (isset($tip) && $tip) {
                $tip->record_id = $record->id;
                $tip->save();
            }
            //保存交易关系
            if (!$transfer->joiner()->where('user_id', $user->id)->exists()) {
                $relation = new TransferUserRelation();
                $relation->transfer_id = $transfer->id;
                $relation->user_id = $user->id;
                $relation->save();
            }
            DB::commit();
            return $this->json([], trans('trans.trade_success'), 1);
        } catch (\Exception $e) {
            Log::error('店铺交易失败,异常', $e->getTrace());
            DB::rollBack();
        }
        return $this->json([], trans('trans.trade_failed'), 0);
    }

    /**
     * @SWG\Post(
     *   path="/transfer/withdraw",
     *   summary="撤回",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="record_id",
     *     in="formData",
     *     description="交易记录ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'record_id' => 'bail|required'
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $record = TransferRecord::find($request->record_id);
        if (!$record) {
            return $this->json([], trans('trans.record_not_exist'), 0);
        }
        if ($record->stat != 2) {
            return $this->json([], trans('trans.record_withdraw_error'), 0);
        }
        $user = JWTAuth::parseToken()->authenticate();
        if ($record->user_id != $user->id) {
            return $this->json([], trans('trans.record_withdraw_user_error'), 0);
        }
        $transfer = $record->transfer;
        if (!$transfer) {
            return $this->json([], trans('trans.trans_not_exist'), 0);
        }
        if ($transfer->status == 3) {
            return $this->json([], trans('trans.trans_already_closed'), 0);
        }
        if ($user->balance < $record->amount) {
            return $this->json([], trans('trans.user_not_enough_money'), 0);
        }
        DB::beginTransaction();
        try {
            //容器撤回
            $pay_transfer = $record->pay_transfer()->first();
            if ($pay_transfer->chargeback() != 1) {
                return $this->json([], trans('trans.withdraw_failed'), 0);
            }
            //交易记录变为撤回状态
            $record->stat = 3;
            $record->save();
            //扣除红包手续费
            if ($record->fee_amount > 0) {
                //红包手续费减少
                $transfer->fee_amount = bcsub($transfer->fee_amount, $record->fee_amount, 2);
            }
            //扣除商店茶水费
            $tip = $record->tip;
            if ($tip) {
//                $shop = $transfer->shop;
//                if (!$shop->isEmpty()) {
//                    return $this->json([], trans('trans.record_withdraw_error_3'),0);
//                }
//                if ($shop->frozen_balance < $tip->amount) {
//                    return $this->json([], trans('trans.record_withdraw_error_2'),0);
//                }
//                $shop->frozen_balance = $shop->frozen_balance - $tip->amount;
//                $shop->save();
                //删除茶水费记录
                TipRecord::where('id', $tip->id)->delete();
                //红包茶水费减少
                $transfer->tip_amount = bcsub($transfer->tip_amount, $tip->amount, 2);
            }
            //用户余额增加
//                $user->balance = $user->balance + $record->amount;
//                $user->save();
            //红包余额增加
            $transfer->amount = bcadd($transfer->amount, $record->amount, 2);
            if ($transfer->amount > 0) {
                $transfer->status = 1;
            }
            $transfer->save();
            //账单明细
            $found = new UserFund();
            $found->user_id = $user->id;
            $found->status = UserFund::STATUS_SUCCESS;
            $found->type = UserFund::TYPE_TRADE_BACK;
            $found->mode = UserFund::MODE_OUT;
            $found->amount = $record->amount;
            $found->save();
            DB::commit();
            return $this->json([], trans('trans.withdraw_success'), 1);
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return $this->json([], trans('trans.withdraw_failed'), 0);
    }

    /**
     * @SWG\Post(
     *   path="/transfer/notice",
     *   summary="通知好友",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="transfer_id",
     *     in="formData",
     *     description="交易ID",
     *     required=true,
     *     type="string"
     *   ),
     *    @SWG\Parameter(
     *     name="friend_id",
     *     in="formData",
     *     description="参与交易人",
     *     required=false,
     *     type="array",
     *     @SWG\Items(
     *             type="string"
     *      )
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function notice(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
                'friend_id' => 'bail|required|array'
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return $this->json([], trans('trans.trans_not_exist'), 0);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if(!$transfer->shop->shop_user()->where('user_id', $user->id)->exists()) {
            return $this->json([], trans('trans.notice_not_allow'), 0);
        }

        if ($transfer->status == 3) {
            return $this->json([], trans('trans.trans_already_closed'), 0);
        }
        if (TransferUserRelation::where('transfer_id', $transfer->id)->where('user_id', $request->friend_id)->exists()) {
            return $this->json([], trans('trans.notice_already_exists'), 0);
        }
        DB::beginTransaction();
        try {
            foreach ($request->friend_id as $value) {
                $real_id = Skip32::decrypt("0123456789abcdef0123", $value);
                if (!$transfer->joiner()->where('user_id', $real_id)->exists()) {
                    $relation = new TransferUserRelation();
                    $relation->transfer_id = $transfer->id;
                    $relation->user_id = $real_id;
                    $relation->save();
                }
            }
            DB::commit();
            return $this->json([], trans('trans.notice_success'), 1);
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return $this->json([], trans('trans.notice_failed'), 0);
    }

    /**
     * @SWG\GET(
     *   path="/transfer/feerecord",
     *   summary="茶水费记录",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="transfer_id",
     *     in="formData",
     *     description="交易ID",
     *     required=true,
     *     type="string"
     *   ),
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
     *                  @SWG\Property(property="id", type="string", example="1234567",description="交易红包id"),
     *                  @SWG\Property(property="shop_id", type="string", example="1234567", description="店铺ID"),
     *                  @SWG\Property(property="price", type="double", example=9.9, description="单价"),
     *                  @SWG\Property(property="amount", type="double", example=9.9, description="红包余额"),
     *                  @SWG\Property(property="comment", type="string", example="大吉大利，恭喜发财", description="备注"),
     *                  @SWG\Property(property="status", type="int", example="1", description="1 待结算 2 已平账 3 已关闭"),
     *                  @SWG\Property(
     *                      property="user",
     *                      type="object",
     *                      description="发起交易红包人信息",
     *                      @SWG\Property(property="id", type="string", example="1234567",description="发起交易红包人id"),
     *                      @SWG\Property(property="name", type="string", example="1234567", description="发起交易红包人昵称"),
     *                      @SWG\Property(property="avatar",type="string", example="url", description="发起交易红包人头像"),
     *                  ),
     *                  @SWG\Property(
     *                      property="tips",
     *                      type="array",
     *                      description="茶水费记录",
     *                      @SWG\Items(
     *                          @SWG\Property(property="amount", type="double", example=9.9, description="缴纳茶水费金额"),
     *                          @SWG\Property(property="created_at", type="string", example="2017-12-22 10:19:23",description="缴纳茶水费时间"),
     *                          @SWG\Property(
     *                              property="user",
     *                              type="object",
     *                              description="茶水费缴纳人信息",
     *                              @SWG\Property(property="id", type="string", example="1234567",description="茶水费缴纳人id"),
     *                              @SWG\Property(property="name", type="string", example="1234567", description="茶水费缴纳人昵称"),
     *                              @SWG\Property(property="avatar",type="string", example="url", description="茶水费缴纳人头像"),
     *                          )
     *                      )
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
    public function feeRecord(Request $request)
    {
        if (!config('shop_fee_status')) {
            return $this->json([], trans('trans.reward_is_turned_off'), 0);
        }

        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $transferObj = Transfer::findByEnId($request->transfer_id);
        if (!$transferObj) {
            return $this->json([], trans('trans.trans_not_exist'), 0);
        }

        $transfer = Transfer::where('id', $transferObj->id)->with(['user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }, 'tips' => function ($query) {
            $query->select('transfer_id', 'user_id', 'amount', 'created_at')->where('record_id', 0)->orderBy('created_at', 'DESC');
        }, 'tips.user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }])->select('id', 'user_id', 'price', 'amount', 'comment', 'status')->first();

        //装填响应数据
        $transfer->id = $transfer->en_id();
        $transfer->user->id = $transfer->user->en_id();
        foreach ($transfer->tips as $key => $record) {
            $transfer->tips[$key]->user->id = $record->user->en_id();
            unset($transfer->tips[$key]->transfer_id);
            unset($transfer->tips[$key]->user_id);
        }
        unset($transfer->user_id);

        return $this->json($transfer, 'ok', 1);
    }

    /**
     * @SWG\Post(
     *   path="/transfer/payfee",
     *   summary="缴纳茶水费",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="transfer_id",
     *     in="formData",
     *     description="交易ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="fee",
     *     in="formData",
     *     description="茶水费金额",
     *     required=true,
     *     type="integer"
     *   ),
     * *   @SWG\Parameter(
     *     name="action",
     *     in="formData",
     *     description=" 0 验证  1 支付",
     *     required=true,
     *     type="integer"
     *   ),
     * *    @SWG\Parameter(
     *     name="pay_password",
     *     in="formData",
     *     description="支付密码 当action=1时必须存在此参数",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function payFee(Request $request)
    {
        if (!config('shop_fee_status')) {
            return $this->json([], trans('trans.reward_is_turned_off'), 0);
        }

        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
                'fee' => 'bail|required|numeric|between:1,99999',
                'action' => ['bail', 'required', Rule::in([0, 1])],
                'pay_password' => 'required_if:action,1',
            ],
            [
                'required' => trans('trans.required'),
                'between' => trans('trans.between')
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return $this->json([], trans('trans.trans_not_exist'), 0);
        }
        if ($transfer->status == 3) {
            return $this->json([], trans('trans.trans_already_closed'), 0);
        }
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->balance < $request->fee) {
            return $this->json([], trans('trans.user_not_enough_money'), 0);
        }
        if ($request->action) {
            //验证支付密码
            try {
                $user->check_pay_password($request->input('pay_password'));
            } catch (\Exception $e) {
                return $this->json([], $e->getMessage(), 0);
            }
            DB::beginTransaction();
            try {
                //容器转账
//                $user_container = PayFactory::MasterContainer($user->container->id);
//                $shop_container = PayFactory::MasterContainer($transfer->shop->container->id);
//                $pay_transfer = $user_container->transfer($shop_container, $request->fee, 0, 0, 0);
                $pay_transfer = $user->container->transfer($transfer->shop->container, $request->fee, 0, 0, 0);
                if (!$pay_transfer) {
                    return $this->json([], trans('trans.trade_failed'), 0);
                }
                //减用户余额
//                $user->balance = $user->balance - $request->fee;
//                $user->save();
                //增加交易红包茶水费总额 交易红包茶水费状态改为已结清
                $transfer->tip_amount = bcadd($transfer->tip_amount, $request->fee, 2);
                $transfer->tip_status = 1;
                $transfer->save();
                //增加店铺余额
//                $shop = Shop::find($transfer->shop_id);
//                $shop->frozen_balance = $shop->frozen_balance + $request->fee;
//                $shop->save();
                //增加茶水费记录
                $record = new TipRecord();
                $record->shop_id = $transfer->shop_id;
                $record->transfer_id = $transfer->id;
                $record->user_id = $user->id;
                $record->amount = $request->fee;
                $record->record_id = 0;
                $record->status = 1;
                $record->save();
                //账单明细
                $found = new UserFund();
                $found->user_id = $user->id;
                $found->status = UserFund::STATUS_SUCCESS;
                $found->type = UserFund::TYPE_TIPS;
                $found->mode = UserFund::MODE_OUT;
                $found->amount = $record->amount;
                $found->save();
                DB::commit();
                return $this->json([], trans('trans.pay_fee_success'), 1);
            } catch (\Exception $e) {
                DB::rollBack();
            }
            return $this->json([], trans('trans.pay_fee_failed'), 0);
        }
        return $this->json([], 'ok', 1);
    }

    /**
     * @SWG\GET(
     *   path="/transfer/record",
     *   summary="交易记录",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="status",
     *     in="formData",
     *     description="交易状态 0, 1 待结算, 2 已平账, 3 已关闭",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="formData",
     *     description="每页条数",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="formData",
     *     description="起始位置(初始默认0或者不传该参数 后续传最后一条数据的id)",
     *     type="string"
     *   ),
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
     *                  @SWG\Property(property="count", type="integer", example="10",description="总数"),
     *                  @SWG\Property(
     *                      property="data",
     *                      type="array",
     *                      description="交易记录",
     *                      @SWG\Items(
     *                          @SWG\Property(property="id", type="string", example="1234567",description="交易记录ID"),
     *                          @SWG\Property(property="transfer_id", type="string", example="1234567",description="交易红包ID"),
     *                          @SWG\Property(property="shop_name", type="string", example="XX的店",description="交易红包所属店铺名称"),
     *                          @SWG\Property(property="amount", type="double", example=9.9, description="交易金额"),
     *                          @SWG\Property(property="eggs", type="int", example=9, description="获得宠物蛋数量"),
     *                          @SWG\Property(property="created_at", type="string", example="2017-12-22 10:19:23",description="参与交易时间"),
     *                          @SWG\Property(property="makr", type="integer", example="1",description="是否标记 0 未标记 1 已标记"),
     *                      )
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
    public function record(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'status' => ['bail', 'required', Rule::in([0, 1, 2, 3])],
                'limit' => 'bail|integer',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $status = $request->status;
        $user = JWTAuth::parseToken()->authenticate();
        $count = $user->involved_transfer()->whereHas('transfer', function ($query) use ($status) {
            $query->where('status', $status);
        })->count();
        $query = $user->involved_transfer()->whereHas('transfer', function ($query) use ($status) {
            $query->where('status', $status);
        })->with(['transfer' => function ($query) {
            $query->select('id', 'shop_id');
        },
//        }])
//        }, 'transfer.record' => function ($query) {
//            $query->sum('amount');
//        },
            'transfer.shop' => function ($query) {
                $query->select('id', 'name');
            }])
            ->select('id', 'transfer_id', 'created_at', 'mark')->orderBy('created_at', 'DESC');
        if ($request->limit) {
            $query->limit($request->limit);
        }
        if ($request->offset) {
            $query->where('id', '<', $request->offset);
        }
        $list = $query->get();
        $data = [];
        foreach ($list as $key => $item) {
            $data[$key]['id'] = $item->id;
            $data[$key]['transfer_id'] = $item->transfer ? $item->transfer->en_id() : 0;
            $data[$key]['shop_name'] = $item->transfer && $item->transfer->shop ? $item->transfer->shop->name : '';
            $data[$key]['created_at'] = date('Y-m-d H:i:s', strtotime($item->created_at));
            $data[$key]['amount'] = $item->transfer ? $item->transfer->record()->where('user_id', $user->id)
                ->where(function ($query) {
                    $query->where('stat', 1)->orWhere('stat', 2);
                })->sum('amount') : 0;
            $data[$key]['eggs'] = PetRecord::whereIn('order', $item->transfer->record()->where('user_id', $user->id)
                ->where(function ($query) {
                    $query->where('stat', 1)->orWhere('stat', 2);
                })->pluck('id'))->count();
//                ->where('stat', 1)->orWhere('stat', 2)
            $data[$key]['makr'] = $item->mark;
        }
        return $this->json(['data' => $data, 'count' => $count], 'ok', 1);
    }

    /**
     * @SWG\GET(
     *   path="/transfer/shop",
     *   summary="商店交易记录",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="formData",
     *     description="店铺ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="status",
     *     in="formData",
     *     description="交易状态 0, 1 待结算, 2 已平账, 3 已关闭",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="formData",
     *     description="每页条数",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="formData",
     *     description="起始位置(初始默认0或者不传该参数 后续传最后一条数据的id)",
     *     type="integer"
     *   ),
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
     *                  @SWG\Property(property="count", type="integer", example="10",description="总数"),
     *                  @SWG\Property(
     *                      property="data",
     *                      type="array",
     *                      description="交易记录",
     *                      @SWG\Items(
     *                          @SWG\Property(property="id", type="string", example="1234567",description="交易红包id"),
     *                          @SWG\Property(property="shop_id", type="string", example="1234567", description="店铺ID"),
     *                          @SWG\Property(property="amount", type="double", example=9.9, description="红包余额"),
     *                          @SWG\Property(property="tip_amount", type="double", example=9.9, description="收益"),
     *                          @SWG\Property(property="created_at", type="string", example="2017-12-22 10:19:23",description="交易红包创建时间"),
     *                          @SWG\Property(
     *                              property="user",
     *                              type="object",
     *                              description="发起交易红包人信息",
     *                              @SWG\Property(property="id", type="string", example="1234567",description="发起交易红包人id"),
     *                              @SWG\Property(property="name", type="string", example="1234567", description="发起交易红包人昵称"),
     *                              @SWG\Property(property="avatar",type="string", example="url", description="发起交易红包人头像"),
     *                          )
     *                      )
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
    public function shop(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'shop_id' => 'bail|required',
                'status' => ['bail', 'required', Rule::in([0, 1, 2, 3])],
                'limit' => 'bail|integer',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $status = $request->status;
//        $user = JWTAuth::parseToken()->authenticate();
        $shop = Shop::findByEnId($request->shop_id);
        if (!$shop) {
            return $this->json([], trans('trans.shop_not_exist'), 0);
        }
        $count = $shop->transfer()->where('status', $status)->count();
        $query = $shop->transfer()->with(['user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }])->where('status', $status)->select('id', 'user_id', 'amount', 'tip_amount', 'created_at')->orderBy('created_at', 'DESC');
        if ($request->limit) {
            $query->limit($request->limit);
        }
        if ($request->offset) {
            $query->where('id', '<', Transfer::decrypt($request->offset));
        }
        $list = $query->get();
        //装填响应数据
        foreach ($list as $key => $value) {
            $list[$key]->id = $value->en_id();
            $list[$key]->shop_id = $request->shop_id;
            $list[$key]->user->id = $value->user->en_id();
            unset($list[$key]->user_id);
        }
        return $this->json(['data' => $list, 'count' => $count], 'ok', 1);
    }

    /**
     * @SWG\Post(
     *   path="/transfer/mark",
     *   summary="标记",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="mark",
     *     in="formData",
     *     description="标记的交易记录ID 数组",
     *     required=true,
     *     type="array",
     *     @SWG\Items(
     *             type="integer",
     *             format="int32"
     *      )
     *   ),
     *   @SWG\Parameter(
     *     name="dismark",
     *     in="formData",
     *     description="取消标记的交易记录ID 数组",
     *     required=true,
     *     type="array",
     *     @SWG\Items(
     *             type="integer",
     *             format="int32"
     *      )
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function mark(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'mark' => 'bail|required_without:dismark|array',
                'dismark' => 'bail|required_without:mark|array'
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        TransferUserRelation::whereIn('id', $request->mark)->update(['mark' => 1]);
        TransferUserRelation::whereIn('id', $request->dismark)->update(['mark' => 0]);

        return $this->json([], trans('trans.mark_success'), 1);
    }

    /**
     * @SWG\Post(
     *   path="/transfer/close",
     *   summary="关闭交易",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="shop_id",
     *     in="formData",
     *     description="店铺ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="transfer_id",
     *     in="formData",
     *     description="交易ID",
     *     required=false,
     *     type="array",
     *     @SWG\Items(
     *             type="string"
     *      )
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function close(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'shop_id' => 'bail|required',
                'transfer_id' => 'bail|array',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

//        $transfer = Transfer::findByEnId($request->transfer_id);
//        if (!$transfer) {
//            return $this->json([], trans('trans.trans_not_exist'),0);
//        }
//        if ($transfer->status == 3) {
//            return $this->json([], trans('trans.trans_already_closed'),0);
//        }
//        if ($transfer->status != 2) {
//            return $this->json([], trans('trans.trans_closed_error'),0);
//        }
        $user = JWTAuth::parseToken()->authenticate();
        $shop = Shop::findByEnId($request->shop_id);
        if (!$shop) {
            return $this->json([], trans('trans.shop_not_exist'), 0);
        }
        if ($shop->manager_id != $user->id) {
            return $this->json([], trans('trans.trans_closed_error_shop_manager'), 0);
        }
        $query = Transfer::where('shop_id', $shop->id)->where('status', 2);
        if (isset($request->transfer_id) && $request->transfer_id) {
            $tmpIds = [];
            foreach ($request->transfer_id as $key => $value) {
                $tmpIds[] = Skip32::decrypt("0123456789abcdef0123", $value);
            }
            $query->whereIn('id', $tmpIds);
        }
        $list = $query->get();
        if (!$list || $list->isEmpty()) {
            return $this->json([], trans('trans.not_need_trans_closed'), 0);
        }
        $success = 0;
        foreach ($list as $transfer) {
            DB::beginTransaction();
            try {
                $transfer->status = 3;
                if ($transfer->save()) {
//                    $shop_container = PayFactory::MasterContainer($transfer->shop->container->id);

//                $shop = $transfer->shop;
//                if ($shop) {
//                    $shop->frozen_balance = $shop->frozen_balance - $transfer->tip_amount;
//                    $shop->balance = $shop->balance + $transfer->tip_amount;
//                    $shop->save();
//                }

//                    $records = $transfer->record()->where('stat', 2)->get();
                    $records = $transfer->record()->with('user')->where('stat', '<>', 3)->get();
                    //交易产生的茶水费
                    $tip_amount = 0;
                    foreach ($records as $key => $value) {
                        //宠物蛋
                        if(!$value->user->pet_records()->where('transfer_id',$transfer->id)->exists()) {
                            $value->user->batch_create_pet(rand(1, 4), Pet::TYPE_EGG, PetRecord::TYPE_TRANSFER, $value->id, $transfer->id);
                        }
                        if ($value->stat == 2) {
                            //茶水费记录到账
                            $tipModel = $value->tip;
                            $tip_amount = bcadd($tip_amount, $tipModel->amount, 2);
                            $tipModel->status = 1;
                            $tipModel->save();
                            //公司分润 代理分润 运营分润
                            $profit = new Profit();
                            $profit->record_id = $value->id;
                            $profit->user_id = $value->user_id;
                            $profit->fee_percent = $transfer->fee_percent;
                            $profit->proxy = 0;
                            $profit->operator = 0;
                            $profit->proxy_percent = 0;
                            $profit->proxy_amount = 0;
                            $profit->fee_amount = 0;
                            if ($value->user->parent && $value->user->parent->status == 0 && $value->user->parent->percent > 0
                                && $value->user->parent->proxy_container
                            ) {
                                $profit->proxy_amount = bcdiv(bcmul(strval($value->fee_amount), strval($value->user->parent->percent), 2), '100', 2);
                                if ($profit->proxy_amount > 0) {
                                    $profit->proxy = $value->user->parent->id;
                                    $profit->proxy_percent = $value->user->parent->percent;
                                    //解冻代理分润账户资金
                                    $proxy_container = $value->user->parent->proxy_container;
                                    $proxy_container->unfreeze($profit->proxy_amount);
                                }
                            }
//                            if ($profit->proxy_amount <= 0) {
//                                continue;
//                            }
                            //解冻代理资金
//                                $proxy_container = PayFactory::MasterContainer($value->user->parent->container->id);
                            if ($value->user->operator) {
                                $profit->operator = $value->user->operator->id;
                                $profit->fee_amount = bcsub($value->fee_amount, $profit->proxy_amount, 2);
//                        $profit->operator_percent = $value->id;
//                        $profit->operator_amount = $value->id;
                            }
                            //公司与代理分润为0时不记录分润 并且不发送提醒通知
                            if ($profit->fee_amount > 0 || $profit->proxy_amount > 0) {
                                if ($profit->save()) {
                                    //发送通知
                                    if ($profit->proxy_amount > 0) {
                                        \App\Admin\Controllers\NoticeController::send([$profit->proxy], 1, '', '', $profit->id);
                                    }
                                }
                            }
                        }
                    }
                    //解冻店铺茶水费资金
                    $shop_container = $transfer->shop->container;
                    if ($tip_amount > 0) {
                        if (!$shop_container->unfreeze($tip_amount)) {
                            Log::error('关闭交易，解冻店铺资金失败:' . '     shop container:' . $shop_container->id . ' frozen_balance:' . $shop_container->frozen_balance . '     unfreeze_amount:' . $transfer->tip_amount);
                            DB::rollBack();
                            continue;
                        }
                    }
                }
                //关闭交易容器
                $transfer->container->close();
                DB::commit();
                $success++;
            } catch (\Exception $e) {
                Log::info('$profit：' . $profit);
                Log::error('关闭交易失败：' . $e->getTraceAsString());
                DB::rollBack();
            }
        }
        if ($list->count() == $success) {
            return $this->json([], trans('trans.trans_closed_success'), 1);
        } else if ($success <= 0) {
            return $this->json([], trans('trans.trans_closed_failed'), 0);
        } else {
            return $this->json([], trans('trans.trans_closed_part_success'), 1);
        }
    }

    /**
     * @SWG\Post(
     *   path="/transfer/cancel",
     *   summary="取消交易",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="transfer_id",
     *     in="formData",
     *     description="交易ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return $this->json([], trans('trans.trans_not_exist'), 0);
        }
        if ($transfer->user_id != $user->id) {
            return $this->json([], trans('trans.trans_not_belong_user'), 0);
        }
        if ($transfer->record()->exists() || $transfer->tips()->exists()) {
            return $this->json([], trans('trans.trans_not_allow_to_cancel'), 0);
        }
        //删除交易用户关联关系
        TransferUserRelation::where('transfer_id', $transfer->id)->delete();
        //删除交易容器
        $transfer->container()->delete();
        //删除交易
        $transfer->delete();
        return $this->json([], trans('trans.trans_cancel_success'), 1);
    }
}
