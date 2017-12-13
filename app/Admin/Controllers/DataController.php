<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2017/12/9
 * Time: 15:19
 */

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Profit;
use App\TipRecord;
use App\Transfer;
use App\TransferRecord;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    const PAGE_SIZE = 20;

    //收益统计
    public function profit(Request $request)
    {
        //交易总笔数
        $transfer_count = Transfer::count();
        //总收款
        $amount = TransferRecord::where('stat', 1)->sum('amount');
        //店铺分润
        $shop_amount = TipRecord::sum('amount');
        //茶水费
        $tip_amount = TipRecord::where('record_id', 0)->sum('amount');
        //代理分润
        $proxy_amount = Profit::sum('proxy_amount');
        //公司运营收入
        $company_amount = Profit::sum('fee_amount');
        $with = ['parent', 'operator'];
        $query = User::query();
        //用户ID
        $aid = $request->input('aid');
        if ($aid) {
            $query->where('id', $aid);
        }
        //推荐人ID
        $parent = $request->input('parent');
        if ($parent) {
            $query->where('parent_id', $parent);
        }
        //运营ID
        $operator = $request->input('operator');
        if ($operator) {
            $query->where('operator_id', $operator);
        }
        $date_time = $request->input('date_time');
        if (!empty($date_time)) {
            $date_time_arr = explode(' - ', $request->input('date_time'));
            $begin = $date_time_arr[0];
            $end = $date_time_arr[1] . ' 23:59:59';
            $with['transfer_record'] = function ($query) use ($begin, $end) {
                $query->where('created_at', '>=', $begin)->where('created_at', '<=', $end)->where('stat', 2);
            };
            $with['tips'] = function ($query) use ($begin, $end) {
                $query->where('created_at', '>=', $begin)->where('created_at', '<=', $end);
            };
            $with['output_profit'] = function ($query) use ($begin, $end) {
                $query->where('created_at', '>=', $begin)->where('created_at', '<=', $end);
            };
        } else {
            $with[] = 'transfer_record';
            $with[] = 'tips';
            $with[] = 'output_profit';
        }
        $list = $query->with($with)->sortByDesc(function ($item) {
            return $item->output_profit->sum('fee_amount');
        })->paginate(self::PAGE_SIZE);
        $data = compact('aid', 'date_time', 'operator', 'parent', 'list', 'transfer_count', 'amount', 'shop_amount', 'tip_amount', 'proxy_amount', 'company_amount');
        return Admin::content(function (Content $content) use ($data) {
            $content->body(view('admin/data/profit', $data));
            $content->header("收入统计");
        });
    }

    //交易管理
    public function transfer(Request $request)
    {
        $count = Transfer::count();
        $amount = TransferRecord::where('stat', 1)->sum('amount');
        $listQuery = Transfer::with(['shop', 'shop.user', 'record' => function ($query) {
            $query->where('stat', 1);
        }, 'tips'])->withCount('joiner');
        //店主ID
        $aid = $request->input('aid');
        if ($aid) {
            $listQuery->whereHas('shop.user', function ($query) use ($aid) {
                $query->where('id', $aid);
            });
        }
        //店铺ID
        $shop_id = $request->input('shop_id');
        if ($shop_id) {
            $listQuery->where('shop_id', $shop_id);
        }
        //店铺名称
        $shop_name = $request->input('shop_name');
        if ($shop_name) {
            $listQuery->whereHas('shop', function ($query) use ($shop_name) {
                $query->where('name', $shop_name);
            });
        }
        //交易ID
        $id = $request->input('id');
        if ($id) {
            $listQuery->where('id', $id);
        }
        $date_time = $request->input('date_time');
        if (!empty($date_time)) {
            $date_time_arr = explode(' - ', $request->input('date_time'));
            $begin = $date_time_arr[0];
            $end = $date_time_arr[1] . ' 23:59:59';
            $listQuery->where('created_at', '>=', $begin)->where('created_at', '<=', $end);
        }
        $list = $listQuery->orderBy('created_at', 'DESC')->paginate(self::PAGE_SIZE);
        $data = compact('aid', 'date_time', 'shop_id', 'shop_name', 'id', 'count', 'amount', 'list');
        return Admin::content(function (Content $content) use ($data) {
            $content->body(view('admin/data/transfer', $data));
            $content->header("交易管理");
        });
    }

    //交易详情
    public function detail($id)
    {
        $data = Transfer::with('user', 'record', 'record.user', 'shop', 'shop.user')->where('id', $id)->first();
        return Admin::content(function (Content $content) use ($data) {
            $content->body(view('admin/profit', $data));
            $content->header("交易详情");
        });
    }

    //关闭交易
    public function close($id)
    {
        $transfer = Transfer::find($id);
        if ($transfer->isEmpty()) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_not_exist'), 'data' => []]);
        }
        if ($transfer->status == 3) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_already_closed'), 'data' => []]);
        }
        if ($transfer->status != 2) {
            return response()->json(['code' => 0, 'msg' => trans('trans.trans_closed_error'), 'data' => []]);
        }
//        $user = JWTAuth::parseToken()->authenticate();
//        if($transfer->shop->manager != $user->id) {
//            return response()->json(['code' => 0, 'msg' => trans('trans.trans_closed_error_shop_manager'), 'data' => []]);
//        }
        DB::beginTransaction();
        try {
            $transfer->status = 3;
            if ($transfer->save()) {
                //解冻店铺茶水费资金
                $shop = $transfer->shop;
                if (!$shop->isEmpty()) {
                    $shop->frozen_balance = $shop->frozen_balance - $transfer->tip_amount;
                    $shop->balance = $shop->balance + $transfer->tip_amount;
                    $shop->save();
                }
                //公司分润 代理分润 运营分润
                $records = $transfer->record()->where('stat', 2)->get();
                foreach ($records as $key => $value) {
                    $profit = new Profit();
                    $profit->record_id = $value->id;
                    $profit->user_id = $value->user_id;
                    $profit->fee_percent = $transfer->fee_percent;
                    $profit->fee_amount = $value->fee_amount;
                    if ($value->user->proxy) {
                        $profit->proxy = $value->user->proxy->id;
                        $profit->proxy_percent = $value->user->proxy->percent;
                        $profit->proxy_amount = ($value->fee_amount * $value->user->proxy->percent) / 100;
                    }
                    if ($value->user->operator) {
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

    public function record(Request $request)
    {
        $count = TransferRecord::count();
        $get_amount = TransferRecord::where('stat', 2)->sum('amount');
        $put_amount = TransferRecord::where('stat', 1)->sum('amount');
        $listQuery = TransferRecord::with(['user', 'transfer', 'tip', 'transfer.shop', 'transfer.shop.user']);
        //用户ID
        $aid = $request->input('aid');
        if ($aid) {
            $listQuery->where('user_id', $aid);
        }
        //店铺ID
        $shop_id = $request->input('shop_id');
        if ($shop_id) {
            $listQuery->whereHas('transfer', function ($query) use ($shop_id) {
                $query->where('shop_id', $shop_id);
            });
        }
        //店主ID
        $owner_id = $request->input('owner_id');
        if ($owner_id) {
            $listQuery->whereHas('transfer.shop', function ($query) use ($owner_id) {
                $query->where('manager', $owner_id);
            });
        }
        //交易ID
        $id = $request->input('id');
        if ($id) {
            $listQuery->where('transfer_id', $id);
        }
        //状态
        $stat = $request->input('stat');
        if ($stat) {
            $listQuery->where('stat', $stat);
        }
        //起止时间
        $date_time = $request->input('date_time');
        if (!empty($date_time)) {
            $date_time_arr = explode(' - ', $request->input('date_time'));
            $begin = $date_time_arr[0];
            $end = $date_time_arr[1] . ' 23:59:59';
            $listQuery->where('created_at', '>=', $begin)->where('created_at', '<=', $end);
        }
        $list = $listQuery->orderBy('created_at', 'DESC')->paginate(self::PAGE_SIZE);
        $data = compact('aid', 'date_time', 'shop_id', 'owner_id', 'id', 'stat', 'count', 'get_amount', 'put_amount', 'list');
        return Admin::content(function (Content $content) use ($data) {
            $content->body(view('admin/record', $data));
            $content->header("支付流水");
        });
    }

}