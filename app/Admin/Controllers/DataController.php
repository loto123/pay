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

class DataController extends Controller
{
    const PAGE_SIZE = 20;

    //收益统计
    public function profit(Request $request)
    {
        $transfer_count = Transfer::count();
        $amount = TransferRecord::where('stat', 1)->sum('amount');
        $shop_amount = TipRecord::sum('amount');
        $tip_amount = TipRecord::where('record_id', 0)->sum('amount');
        $proxy_amount = Profit::sum('proxy_amount');
        $company_amount = Profit::sum('fee_amount');
        $with = ['parent', 'operator'];
        $query = User::query();
        $aid = $request->input('aid');
        if ($aid) {
            $query->where('id', $aid);
        }
        $parent = $request->input('parent');
        if ($parent) {
            $query->where('parent_id', $parent);
        }
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
        $list = $query->with($with)->paginate(self::PAGE_SIZE);
        $data = compact('aid', 'date_time', 'operator', 'parent', 'list', 'transfer_count', 'amount', 'shop_amount', 'tip_amount', 'proxy_amount', 'company_amount');
        return Admin::content(function (Content $content) use ($data) {
            $content->body(view('admin/profit', $data));
            $content->header("收入统计");
        });
    }

    //交易管理
    public function transfer(Request $request) {
        $query = Transfer::with('shop','');
        $aid = $request->input('aid');
        if ($aid) {
            $query->where('id', $aid);
        }
        $parent = $request->input('parent');
        if ($parent) {
            $query->where('parent_id', $parent);
        }
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
        }
    }
}