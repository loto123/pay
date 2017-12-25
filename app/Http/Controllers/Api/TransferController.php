<?php

namespace App\Http\Controllers\Api;

use App\Pay\Model\PayFactory;
use App\PaypwdValidateRecord;
use App\Profit;
use App\Shop;
use App\TipRecord;
use App\Transfer;
use App\TransferRecord;
use App\TransferUserRelation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use JWTAuth;
use Skip32;
use Validator;

class TransferController extends Controller
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
     *     type="integer"
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
     *             type="integer",
     *             format="int32"
     *      )
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
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
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $shop = Shop::findByEnId($request->shop_id);
        if (!$shop) {
            return response()->json(['code' => 0, 'msg' => trans('trans.shop_not_exist'), 'data' => []]);
        }
        $wallet = PayFactory::MasterContainer();
        $wallet->save();
        $transfer = new Transfer();
        $transfer->shop_id = $shop->id;
        $transfer->user_id = $user->id;
        $transfer->price = $request->price;
        $transfer->comment = $request->input('comment', '');
        if ($shop->type == 0) {
            $transfer->tip_type = 1;
            $transfer->tip_amount = $shop->type_value;
        }
        if ($shop->type == 1) {
            $transfer->tip_type = 2;
            $transfer->tip_percent = $shop->type_value * 100;
        }
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
            return response()->json(['code' => 1, 'msg' => trans('trans.save_success'), 'data' => ['id' => $transfer->en_id()]]);
        } else {
            return response()->json(['code' => 0, 'msg' => trans('trans.save_failed'), 'data' => []]);
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
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
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
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $transferObj = Transfer::findByEnId($request->transfer_id);
        if (!$transferObj) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }

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
        }])->select('id', 'shop_id', 'user_id', 'price', 'amount', 'comment', 'status', 'tip_type')->first();

        //装填响应数据
        $transfer->id = $transfer->en_id();
        $transfer->shop_id = $transfer->shop->en_id();
        $transfer->user->id = $transfer->user->en_id();
        foreach ($transfer->record as $key => $record) {
            $transfer->record[$key]->user->id = $record->user->en_id();
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
        return response()->json(['code' => 1, 'msg' => 'ok', 'data' => $transfer]);
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
     *     type="integer"
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
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
        if ($user->balance < ($request->points * $transfer->price)) {
            return response()->json(['code' => 0, 'msg' => trans('trans.user_not_enough_money'), 'data' => []]);
        }
        return response()->json(['code' => 1, 'msg' => 'ok', 'data' => []]);
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
     *     type="integer"
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
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
        DB::beginTransaction();
        try {
            $record = new TransferRecord();
            $record->transfer_id = $transfer->id;
            $record->user_id = $user->id;
            $record->amount = $request->points * $transfer->price;
            $record->points = $request->points;
            $record->fee_amount = 0;
            //放钱
            if ($request->action == 'put') {
                if ($user->balance < $record->amount) {
                    return response()->json(['code' => 0, 'msg' => trans('trans.user_not_enough_money'), 'data' => []]);
                }
                //验证支付密码
                $today = date('Y-m-d');
                $times = $user->paypwd_record()->where('created_at', '>=', $today)->where('created_at', '<=', $today . '23:59:59')->count();
                if ($times >= config('pay_pwd_validate_times')) {
                    return response()->json(['code' => 0, 'msg' => trans('trans.user_check_pay_password_times_out'), 'data' => []]);
                }
                if (!Hash::check($request->pay_password, $user->pay_password)) {
                    //验证错误次数+1
                    $paypwdRecord = new PaypwdValidateRecord();
                    $paypwdRecord->user_id = $user->id;
                    $paypwdRecord->save();
                    return response()->json(['code' => 0, 'msg' => trans('trans.user_pay_password_error'), 'data' => []]);
                }
                $record->stat = 1;
                //用户减钱
//                $user->balance = $user->balance - $record->real_amount;
                //容器转账
                $user_container = PayFactory::MasterContainer($user->container->id);
                $transfer_container = PayFactory::MasterContainer($transfer->container->id);
                $pay_transfer = $user_container->transfer($transfer_container, $record->amount, 0, 0, 0);
                if (!$pay_transfer) {
                    return response()->json(['code' => 0, 'msg' => trans('trans.trade_failed'), 'data' => []]);
                }
                //红包加钱
                $transfer->amount = $transfer->amount + $record->amount;
                $record->real_amount = $record->amount * -1;
                $record->amount = $record->amount * -1;
            }
            //拿钱
            if ($request->action == 'get') {
                if ($transfer->amount < $record->amount) {
                    return response()->json(['code' => 0, 'msg' => trans('trans.not_enough_money'), 'data' => []]);
                }
                $record->stat = 2;
                //收茶水费
                $tips = 0;
                $profit_shares = [];
//                if ($transfer->tip_type == 2 && $transfer->tip_percent > 0) {
                if ($transfer->tip_percent > 0) {
                    $tips = $transfer->tip_percent * $record->amount / 100;
                    //红包茶水费金额增加
                    $transfer->tip_amount = $transfer->tip_amount + $tips;
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
                        $receiver = PayFactory::MasterContainer($transfer->shop->container->id);
                        $profit_shares[] = PayFactory::profitShare($receiver, $tip->amount, true);
                    }
                }
                //收手续费
                $proxy_fee = 0;
                if ($transfer->fee_percent) {
                    //手续费
                    $record->fee_amount = $record->amount * $transfer->fee_percent / 100;
                    //红包手续费金额
                    $transfer->fee_amount = $transfer->fee_amount + $record->fee_amount;
                    //代理分润
                    if ($user->parent) {
                        $user_receiver = PayFactory::MasterContainer($user->parent->container->id);
                        $proxy_fee = floor($record->fee_amount * $user->parent->percent) / 100;
                        $profit_shares[] = PayFactory::profitShare($user_receiver, $proxy_fee, true);
                    }
                }
                //实际获得
                $record->real_amount = $record->amount - $record->fee_amount - $tips;
                //用户加钱
//                $user->balance = $user->balance + $record->real_amount;
                //红包减钱
                $transfer->amount = $transfer->amount - $record->amount;
                //判断红包状态
                if ($transfer->amount == 0) {
                    $transfer->status = 2;
                }
                //容器转账
                $user_container = PayFactory::MasterContainer($user->container->id);
                $transfer_container = PayFactory::MasterContainer($transfer->container->id);
                $pay_transfer = $transfer_container->transfer($user_container, $record->amount - $tips, $record->fee_amount - $proxy_fee, 0, 0, $profit_shares);
                if (!$pay_transfer) {
                    return response()->json(['code' => 0, 'msg' => trans('trans.trade_failed'), 'data' => []]);
                }
            }
//            $user->save();
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
            return response()->json(['code' => 1, 'msg' => trans('trans.trade_success'), 'data' => []]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 0, 'msg' => $e->getTraceAsString(), 'data' => []]);
        }
        return response()->json(['code' => 0, 'msg' => trans('trans.trade_failed'), 'data' => []]);
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
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $record = TransferRecord::find($request->record_id);
        if (!$record) {
            return response()->json(['code' => 0, 'msg' => trans('trans.record_not_exist'), 'data' => []]);
        }
        if ($record->stat != 2) {
            return response()->json(['code' => 0, 'msg' => trans('trans.record_withdraw_error'), 'data' => []]);
        }
        $user = JWTAuth::parseToken()->authenticate();
        if ($record->user_id != $user->id) {
            return response()->json(['code' => 0, 'msg' => trans('trans.record_withdraw_user_error'), 'data' => []]);
        }
        $transfer = $record->transfer;
        if (!$transfer) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
        if ($user->balance < $record->amount) {
            return response()->json(['code' => 0, 'msg' => trans('trans.user_not_enough_money'), 'data' => []]);
        }
        DB::beginTransaction();
        try {
            //容器撤回
            $pay_transfer = $record->pay_transfer()->first();
            if ($pay_transfer->chargeback() != 1) {
                return response()->json(['code' => 0, 'msg' => trans('trans.withdraw_failed'), 'data' => []]);
            }
            //交易记录变为撤回状态
            $record->stat = 3;
            $record->save();
            //扣除商店茶水费
            $tip = $record->tip;
            if ($tip) {
//                $shop = $transfer->shop;
//                if (!$shop->isEmpty()) {
//                    return response()->json(['code' => 0, 'msg' => trans('trans.record_withdraw_error_3'), 'data' => []]);
//                }
//                if ($shop->frozen_balance < $tip->amount) {
//                    return response()->json(['code' => 0, 'msg' => trans('trans.record_withdraw_error_2'), 'data' => []]);
//                }
//                $shop->frozen_balance = $shop->frozen_balance - $tip->amount;
//                $shop->save();
                //删除茶水费记录
                TipRecord::where('id', $tip->id)->delete();
            }
            //用户余额增加
//                $user->balance = $user->balance + $record->amount;
//                $user->save();
            //红包余额增加
            $transfer->amount = $transfer->amount + $record->amount;
            $transfer->save();
            DB::commit();
            return response()->json(['code' => 0, 'msg' => trans('trans.withdraw_success'), 'data' => []]);
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return response()->json(['code' => 0, 'msg' => trans('trans.withdraw_failed'), 'data' => []]);
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
     *     type="integer"
     *   ),
     *    @SWG\Parameter(
     *     name="friend_id",
     *     in="formData",
     *     description="参与交易人",
     *     required=false,
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
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $transfer = Transfer::findByEnId($request->transfer_id);
        if ($transfer) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
        if (TransferUserRelation::where('transfer_id', $transfer->transfer_id)->where('user_id', $request->friend_id)->exists()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.notice_already_exists'), 'data' => []]);
        }
        DB::beginTransaction();
        try {
            foreach ($request->friend_id as $value) {
                $real_id = Skip32::decrypt("0123456789abcdef0123", $value);
                if (!$transfer->joiner()->where('user_id', $real_id)->exists()) {
                    $relation = new TransferUserRelation();
                    $relation->transfer_id = $transfer->transfer_id;
                    $relation->user_id = $real_id;
                    $relation->save();
                }
            }
            DB::commit();
            return response()->json(['code' => 1, 'msg' => trans('trans.notice_success'), 'data' => []]);
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return response()->json(['code' => 0, 'msg' => trans('trans.notice_failed'), 'data' => []]);
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
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function feeRecord(Request $request)
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
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $transferObj = Transfer::findByEnId($request->transfer_id);
        if (!$transferObj) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }

        $transfer = Transfer::where('id', $transferObj->id)->with(['user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }, 'tips' => function ($query) {
            $query->select('transfer_id', 'user_id', 'amount', 'created_at')->orderBy('created_at', 'DESC');
        }, 'tips.user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }])->select('id', 'user_id', 'price', 'amount', 'comment', 'status', 'tip_type')->first();

        //装填响应数据
        $transfer->id = $transfer->en_id();
        $transfer->user->id = $transfer->user->en_id();
        foreach ($transfer->tips as $key => $record) {
            $transfer->tips[$key]->user->id = $record->user->en_id();
            unset($transfer->tips[$key]->transfer_id);
            unset($transfer->tips[$key]->user_id);
        }
        unset($transfer->user_id);

        return response()->json(['code' => 1, 'msg' => 'ok', 'data' => $transfer]);
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
     *     type="integer"
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
        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
                'fee' => 'bail|required|numeric|between:1,99999',
                'action' => ['bail', 'required', Rule::in([0, 1])],
                'pay_password' => 'required_if:action,1',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }
        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->balance < $request->fee) {
            return response()->json(['code' => 0, 'msg' => trans('trans.user_not_enough_money'), 'data' => []]);
        }
        if ($request->action) {
            //验证支付密码
            $today = date('Y-m-d');
            $times = $user->paypwd_record()->where('created_at', '>=', $today)->where('created_at', '<=', $today . '23:59:59')->count();
            if ($times >= config('pay_pwd_validate_times')) {
                return response()->json(['code' => 0, 'msg' => trans('trans.user_check_pay_password_times_out'), 'data' => []]);
            }
            if (!Hash::check($request->pay_password, $user->pay_password)) {
                //验证错误次数+1
                $paypwdRecord = new PaypwdValidateRecord();
                $paypwdRecord->user_id = $user->id;
                $paypwdRecord->save();
                return response()->json(['code' => 0, 'msg' => trans('trans.user_pay_password_error'), 'data' => []]);
            }
            DB::beginTransaction();
            try {
                //容器转账
                $user_container = PayFactory::MasterContainer($user->container->id);
                $shop_container = PayFactory::MasterContainer($transfer->shop->container->id);
                $pay_transfer = $user_container->transfer($shop_container, $request->fee, 0, 0, 0);
                if (!$pay_transfer) {
                    return response()->json(['code' => 0, 'msg' => "111" . trans('trans.trade_failed'), 'data' => []]);
                }
                //减用户余额
//                $user->balance = $user->balance - $request->fee;
//                $user->save();
                //增加交易红包茶水费总额 交易红包茶水费状态改为已结清
                $transfer->tip_amount = $transfer->tip_amount + $request->fee;
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
                $record->save();
                DB::commit();
                return response()->json(['code' => 1, 'msg' => trans('trans.pay_fee_success'), 'data' => []]);
            } catch (\Exception $e) {
                DB::rollBack();
            }
            return response()->json(['code' => 0, 'msg' => trans('trans.pay_fee_failed'), 'data' => []]);
        }
        return response()->json(['code' => 1, 'msg' => 'ok', 'data' => []]);
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
     *     description="起始位置",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function record(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'status' => ['bail', 'required', Rule::in([0, 1, 2, 3])],
                'limit' => 'bail|integer',
                'offset' => 'bail|integer',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }
        $status = $request->status;
        $user = JWTAuth::parseToken()->authenticate();
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
        if ($request->limit && $request->offset) {
            $query->offset($request->offset)->limit($request->limit);
        }
        $list = $query->get();
        $data = [];
        foreach ($list as $key => $item) {
            $data[$key]['id'] = $item->id;
            $data[$key]['transfer_id'] = $item->transfer ? $item->transfer->en_id() : 0;
            $data[$key]['shop_name'] = $item->transfer && $item->transfer->shop ? $item->transfer->shop->name : '';
            $data[$key]['created_at'] = date('Y-m-d H:i:s', strtotime($item->created_at));
            $data[$key]['amount'] = $item->transfer ? $item->transfer->record()->where('user_id', $user->id)
                ->where('stat', '<>', 3)->where('stat', '<>', 0)->sum('amount') : 0;
            $data[$key]['makr'] = $item->mark;
        }
        return response()->json(['code' => 1, 'msg' => 'ok', 'data' => $data]);
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
                'mark' => 'bail|required|array',
                'dismark' => 'bail|required|array'
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        TransferUserRelation::whereIn('id', $request->mark)->update(['mark' => 1]);
        TransferUserRelation::whereIn('id', $request->dismark)->update(['mark' => 0]);

        return response()->json(['code' => 1, 'msg' => trans('trans.mark_success'), 'data' => []]);
    }

    /**
     * @SWG\Post(
     *   path="/transfer/close",
     *   summary="关闭交易",
     *   tags={"交易"},
     *   @SWG\Parameter(
     *     name="transfer_id",
     *     in="formData",
     *     description="交易ID",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     * )
     * @return \Illuminate\Http\Response
     */
    public function close(Request $request)
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
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
        if ($transfer->status != 2) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_closed_error'), 'data' => []]);
        }
        $user = JWTAuth::parseToken()->authenticate();
        if ($transfer->shop->manager != $user->id) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_closed_error_shop_manager'), 'data' => []]);
        }
        DB::beginTransaction();
        try {
            $transfer->status = 3;
            if ($transfer->save()) {
                //解冻店铺茶水费资金
                $shop_container = PayFactory::MasterContainer($transfer->shop->container->id);
                if (!$shop_container->unfreeze($transfer->tip_amount)) {
                    return response()->json(['code' => 0, 'msg' => trans('trans.trans_closed_failed'), 'data' => []]);
                }
//                $shop = $transfer->shop;
//                if ($shop) {
//                    $shop->frozen_balance = $shop->frozen_balance - $transfer->tip_amount;
//                    $shop->balance = $shop->balance + $transfer->tip_amount;
//                    $shop->save();
//                }
                //公司分润 代理分润 运营分润
                $records = $transfer->record()->where('stat', 2)->get();
                foreach ($records as $key => $value) {
                    $profit = new Profit();
                    $profit->record_id = $value->id;
                    $profit->user_id = $value->user_id;
                    $profit->fee_percent = $transfer->fee_percent;
                    if ($value->user->parent) {
                        $profit->proxy = $value->user->parent->id;
                        $profit->proxy_percent = $value->user->parent->percent;
                        $profit->proxy_amount = floor($value->fee_amount * $value->user->parent->percent) / 100;
                        //解冻代理资金
                        $proxy_container = PayFactory::MasterContainer($value->user->parent->container->id);
                        $proxy_container->unfreeze($profit->proxy_amount);
                    }
                    $profit->fee_amount = $value->fee_amount - $profit->proxy_amount;
                    if ($value->user->operator) {
                        $profit->operator = $value->user->operator->id;
//                        $profit->operator_percent = $value->id;
//                        $profit->operator_amount = $value->id;
                    }
                    $profit->save();
                }
                DB::commit();
                return response()->json(['code' => 1, 'msg' => trans('trans.trans_closed_success'), 'data' => []]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return response()->json(['code' => 0, 'msg' => trans('trans.trans_closed_failed'), 'data' => []]);
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
     *     type="integer"
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
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $transfer = Transfer::findByEnId($request->transfer_id);
        if (!$transfer) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->user_id != $user->id) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_belong_user'), 'data' => []]);
        }
        if ($transfer->record()->exists()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_allow_to_cancel'), 'data' => []]);
        }
        //删除交易用户关联关系
        TransferUserRelation::where('transfer_id', $transfer->id)->delete();
        //删除交易容器
        $transfer->container()->delete();
        //删除交易
        $transfer->delete();
        return response()->json(['code' => 1, 'msg' => trans('trans.trans_cancel_success'), 'data' => []]);
    }
}
