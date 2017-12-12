<?php

namespace App\Http\Controllers\Api;

use App\Profit;
use App\Shop;
use App\TipRecord;
use App\Transfer;
use App\TransferRecord;
use App\TransferUserRelation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use JWTAuth;
use Validator;
use QrCode;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware("jwt.auth");
    }

    //发起交易
    public function create(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(),
            [
                'shop_id' => 'bail|required',
                'price' => 'bail|required|numeric|between:0.1,99999',
                'comment' => 'size:200'
            ],
            [
                'required' => trans('trans.required'),
                'numeric' => trans('trans.numeric'),
                'between' => trans('trans.between'),
                'size' => trans('trans.size')
            ]
        );

        if ($validator->fails()) {
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $shop = Shop::find($request->shop_id);
        if ($shop->isEmpty()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.shop_not_exist'), 'data' => []]);
        }
        $transfer = new Transfer();
        $transfer->shop_id = $request->shop_id;
        $transfer->user_id = $user->id;
        $transfer->price = $request->price;
        $transfer->comment = $request->comment;
        if ($shop->type == 0) {
            $transfer->tip_type = 1;
            $transfer->tip_amount = $shop->type_value;
        }
        if ($shop->type == 1) {
            $transfer->tip_type = 2;
            $transfer->tip_percent = $shop->type_value * 100;
        }
        $transfer->fee_percent = config('platform_fee_percent');

        if ($transfer->save()) {
            return response()->json(['code' => 1, 'msg' => trans('trans.save_success'), 'data' => $transfer]);
        } else {
            return response()->json(['code' => 0, 'msg' => trans('trans.save_failed'), 'data' => []]);
        }
    }

    //交易详情
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

        $transfer = Transfer::where('id', $request->transfer_id)->withCount('joiner')->with(['user' => function ($query) {
            $query->select('name', 'avatar');
        }, 'record' => function ($query) {
            $query->select('id', 'amount', 'real_amount', 'stat', 'created_at')->orderBy('created_at', 'DESC');
        }, 'record.user' => function ($query) {
            $query->select('name', 'avatar');
        }, 'joiner.user' => function ($query) {
            $query->select('name', 'avatar');
        }])->select('id', 'price', 'amount', 'comment', 'status', 'tip_type')->first();

        if ($transfer->isEmpty()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        } else {
            return response()->json(['code' => 1, 'msg' => 'ok', 'data' => $transfer->toArray()]);
        }
    }

    //交易
    public function trade(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
                'points' => 'bail|required|integer|between:1,99999',
                'action' => ['bail', 'required', Rule::in(['put', 'get'])],
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

        $transfer = Transfer::find($request->transfer_id);
        if ($transfer->isEmpty()) {
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
                $record->stat = 1;
                $record->real_amount = $record->amount;
                //用户减钱
                $user->balance = $user->balance - $record->real_amount;
                //红包加钱
                $transfer->amount = $transfer->amount + $record->amount;
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
                if ($transfer->tip_type == 2 && $transfer->tip_percent > 0) {
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
                    $shop = Shop::find($transfer->shop_id);
                    $shop->frozen_balance = $shop->frozen_balance + $tips;
                    $shop->save();
                }
                //收手续费
                if ($transfer->fee_percent) {
                    //手续费
                    $record->fee_amount = $record->amount * $transfer->fee_percent / 100;
                    //红包手续费金额
                    $transfer->fee_amount = $transfer->fee_amount + $record->fee_amount;
                }
                //实际获得
                $record->real_amount = $record->amount - $record->fee_amount - $tips;
                //用户加钱
                $user->balance = $user->balance + $record->real_amount;
                //红包减钱
                $transfer->amount = $transfer->amount - $record->amount;
                //判断红包状态
                if ($transfer->amount == 0) {
                    $transfer->status = 2;
                }
            }
            $user->save();
            $transfer->save();
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
            return response()->json(['code' => 0, 'msg' => trans('trans.trade_failed'), 'data' => []]);
        }
    }

    //撤回
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
        if ($record->isEmpty()) {
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
        if ($transfer->isEmpty()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
        DB::beginTransaction();
        try {
            //交易记录变为撤回状态
            $record->stat = 3;
            $record->save();
            //扣除商店茶水费
            $tip = $record->tip;
            if (!$tip->isEmpty()) {
                $shop = $transfer->shop;
                if ($shop->isEmpty()) {
                    return response()->json(['code' => 0, 'msg' => trans('trans.record_withdraw_error_3'), 'data' => []]);
                }
                if ($shop->frozen_balance < $tip->amount) {
                    return response()->json(['code' => 0, 'msg' => trans('trans.record_withdraw_error_2'), 'data' => []]);
                }
                $shop->frozen_balance = $shop->frozen_balance - $tip->amount;
                $shop->save();
                //用户余额增加
                $user->balance = $user->balance + $record->amount;
                $user->save();
                //删除茶水费记录
                TipRecord::where('id', $tip->id)->delete();
            }
            return response()->json(['code' => 0, 'msg' => trans('trans.withdraw_success'), 'data' => []]);
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return response()->json(['code' => 0, 'msg' => trans('trans.withdraw_failed'), 'data' => []]);
    }

    //通知
    public function notice(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
                'friend_id' => 'bail|required',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }

        $transfer = Transfer::find($request->transfer_id);
        if ($transfer->isEmpty()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
        if (TransferUserRelation::where('transfer_id', $transfer->transfer_id)->where('user_id', $request->friend_id)->exists()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.notice_already_exists'), 'data' => []]);
        }
        $relation = new TransferUserRelation();
        $relation->transfer_id = $transfer->transfer_id;
        $relation->user_id = $request->friend_id;
        if ($relation->save()) {
            return response()->json(['code' => 1, 'msg' => trans('trans.notice_success'), 'data' => []]);
        } else {
            return response()->json(['code' => 0, 'msg' => trans('trans.notice_failed'), 'data' => []]);
        }
    }

    //茶水费记录
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

        $list = TipRecord::with(['user' => function ($query) {
            $query->select('name', 'avatar');
        }])->where('transfer_id', $request->transfer_id)
            ->select('amount', 'created_at')
            ->orderBy('created_at', 'DESC')->get();

        return response()->json(['code' => 1, 'msg' => 'ok', 'data' => $list]);
    }

    //缴纳茶水费
    public function payFee(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'transfer_id' => 'bail|required',
                'fee' => 'bail|required|numeric|between:1,99999',
            ],
            [
                'required' => trans('trans.required'),
            ]
        );

        if ($validator->fails()) {
            return response()->json(['code' => 0, 'msg' => $validator->errors()->first(), 'data' => []]);
        }
        $transfer = Transfer::find($request->transfer_id);
        if ($transfer->isEmpty()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
//        if ($transfer->tip_type != 1) {
//            return response()->json(['code' => 0, 'msg' => trans('trans.trans_tip_type_error'), 'data' => []]);
//        }
//        if ($transfer->tip_status == 1) {
//            return response()->json(['code' => 0, 'msg' => trans('trans.trans_tip_status_error'), 'data' => []]);
//        }
//        if ($request->fee < $transfer->tip_amount) {
//            return response()->json(['code' => 0, 'msg' => trans('trans.trans_tip_fee_error'), 'data' => []]);
//        }
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->balance < $request->fee) {
            return response()->json(['code' => 0, 'msg' => trans('trans.user_not_enough_money'), 'data' => []]);
        }
        DB::beginTransaction();
        try {
            //减用户余额
            $user->balance = $user->balance - $request->fee;
            $user->save();
            //增加交易红包茶水费总额 交易红包茶水费状态改为已结清
            $transfer->tip_amount = $request->fee;
            $transfer->tip_status = 1;
            $transfer->save();
            //增加店铺余额
            $shop = Shop::find($transfer->shop_id);
            $shop->frozen_balance = $shop->frozen_balance + $request->fee;
            $shop->save();
            //增加茶水费记录
            $record = new TipRecord();
            $record->shop_id = $transfer->shop_id;
            $record->transfer_id = $transfer->id;
            $record->user_id = $user->id;
            $record->amount = $request->fee;
            $record->record_id = 0;
            $record->save();
            return response()->json(['code' => 1, 'msg' => trans('trans.pay_fee_success'), 'data' => []]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 0, 'msg' => trans('trans.pay_fee_failed'), 'data' => []]);
        }
    }

    //交易记录
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
        })->with(['transfer' => function($query) {
            $query->select('transfer_id');
        }, 'transfer.record' => function($query) {
            $query->sum('amount');
        }, 'transfer.shop' => function($query) {
            $query->select('id','name');
        }])->select('id', 'transfer_id', 'created_at', 'mark')->orderBy('created_at', 'DESC');
        if ($request->limit && $request->offset) {
            $query->offset($request->offset)->limit($request->limit);
        }
        $list = $query->get();
        return response()->json(['code' => 1, 'msg' => 'ok', 'data' => $list]);
    }

    //标记
    public function mark(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'record_id' => 'bail|required',
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

        TransferRecord::whereIn('id', $request->mark)->update(['mark' => 1]);
        TransferRecord::whereIn('id', $request->dismark)->update(['mark' => 0]);

        return response()->json(['code' => 1, 'msg' => trans('trans.mark_success'), 'data' => []]);
    }

    //关闭交易
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

        $transfer = Transfer::find($request->transfer_id);
        if ($transfer->isEmpty()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
        if ($transfer->status != 2) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_closed_error'), 'data' => []]);
        }
        $user = JWTAuth::parseToken()->authenticate();
        if($transfer->shop->manager != $user->id) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_closed_error_shop_manager'), 'data' => []]);
        }
        DB::beginTransaction();
        try {
            $transfer->status = 3;
            if ($transfer->save()) {
                //解冻店铺茶水费资金
                $shop = $transfer->shop;
                if(!$shop->isEmpty()) {
                    $shop->frozen_balance = $shop->frozen_balance - $transfer->tip_amount;
                    $shop->balance = $shop->balance + $transfer->tip_amount;
                    $shop->save();
                }
                //公司分润 代理分润 运营分润
                $records = $transfer->record()->where('stat',2)->get();
                foreach($records as $key => $value) {
                    $profit = new Profit();
                    $profit->record_id = $value->id;
                    $profit->user_id = $value->user_id;
                    $profit->fee_percent = $transfer->fee_percent;
                    $profit->fee_amount = $value->fee_amount;
                    if($value->user->proxy) {
                        $profit->proxy = $value->user->proxy->id;
                        $profit->proxy_percent = $value->user->proxy->percent;
                        $profit->proxy_amount = ($value->fee_amount * $value->user->proxy->percent) / 100;
                    }
                    if($value->user->operator) {
                        $profit->operator = $value->user->operator->id;
//                        $profit->operator_percent = $value->id;
//                        $profit->operator_amount = $value->id;
                    }
                    $profit->save();
                }
                return response()->json(['code' => 1, 'msg' => trans('trans.trans_closed_success'), 'data' => []]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
        return response()->json(['code' => 0, 'msg' => trans('trans.trans_closed_failed'), 'data' => []]);
    }

    //取消交易
    public function cancel(Request $request) {
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
        $transfer = Transfer::find($request->transfer_id);
        if ($transfer->isEmpty()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->user_id != $user->id) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_belong_user'), 'data' => []]);
        }
        if ($transfer->record()->exists()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_allow_to_cancel'), 'data' => []]);
        }
        //删除交易
        Transfer::where('id',$request->transfer_id)->delete();
        //删除交易用户关联关系
        TransferUserRelation::where('transfer_id',$request->transfer_id)->delete();
        return response()->json(['code' => 1, 'msg' => trans('trans.trans_cancel_success'), 'data' => []]);
    }
}
