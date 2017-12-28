<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Shop;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Invoker\ParameterResolver\DefaultValueResolver;

class ShopController extends Controller
{

    private $limit = 20;

    //店铺管理
    public function index(Request $request)
    {
        $manager_id = User::decrypt($request->input('manager_id'));
        $shop_id = Shop::decrypt($request->input('shop_id'));
        $shop_name = $request->input('shop_name');
        $date_time = $request->input('date_time');
        $begin = '';
        $end = '';
        if (!empty($date_time)) {
            $date_time_arr = explode(' - ', $date_time);
            $begin = $date_time_arr[0];
            $end = $end = $date_time_arr[1] . ' 23:59:59';
        }

        $table_name = (new Shop)->getTable();
        $countQuery = Shop::query();
        $listQuery = Shop::leftJoin('transfer as t',function ($join) use($table_name) {
            $join->on( $table_name.'.id' ,'=' ,'t.shop_id' )
                ->where('t.status', '=', '3');
        })->leftJoin('transfer_record as tfr', function ($join) {
            $join->on('tfr.transfer_id', '=', 't.id')->where('tfr.stat', '=' , '2');
        })->with(['container','manager'])
            ->select( DB::raw($table_name.'.*'),
                DB::raw('COUNT(t.id) as transfer_cnt'), DB::raw('SUM(tfr.amount) as summary'),
                DB::raw('SUM(tfr.fee_amount) as fee_amount_cnt '),
                DB::raw('(SELECT SUM(amount) FROM tip_record WHERE tip_record.transfer_id = t.id ) as tip_amount_cnt'));
        if(!empty($manager_id)) {
            $listQuery->where('u.id', $manager_id);
        }
        if(!empty($shop_id)) {
            $listQuery->where($table_name.'.id', $shop_id);
            $countQuery->where($table_name.'.id', $shop_id);
        }
        if(!empty($shop_name)) {
            $listQuery->where($table_name.'.name', $shop_name);
            $countQuery->where($table_name.'.name', $shop_name);
        }
        if($begin && $end) {
            $listQuery->where($table_name.'.created_at', '>=', $begin)->where($table_name.'.created_at', '<=', $end);
            $countQuery->where($table_name.'.created_at', '>=', $begin)->where($table_name.'.created_at', '<=', $end);
        }
        $listQuery->groupBy($table_name.'.id','t.id')->orderBy('tip_amount_cnt','DESC')->withCount('shop_user');

        $count = $countQuery->count();
        $list = $listQuery->paginate($this->limit);
        $offset = ($request->page>1 ? $request->page-1 : 0 ) * $this->limit;
        $data = compact('list','count','date_time','manager_id','shop_id','shop_name','offset');
        return Admin::content(function (Content $content) use($data) {
            $content->body(view('admin/shop',$data));
            $content->header("店铺管理");
        });
    }

    //店铺详情
    public function details($shop_id)//店铺id
    {
        $table_name = (new Shop)->getTable();
        $listQuery = Shop::leftJoin('transfer as t',function ($join) use($table_name) {
            $join->on( $table_name.'.id' ,'=' ,'t.shop_id' )
                ->where('t.status', '=', '3');
        })->leftJoin('transfer_record as tfr', function ($join) {
            $join->on('tfr.transfer_id', '=', 't.id')->where('tfr.stat', '=' , '2');
        })->with('manager')
            ->select( DB::raw($table_name.'.*'),
                DB::raw('COUNT(t.id) as transfer_cnt'), DB::raw('SUM(tfr.amount) as summary'),
                DB::raw('SUM(tfr.fee_amount) as fee_amount_cnt '),
                DB::raw('(SELECT SUM(amount) FROM tip_record WHERE tip_record.transfer_id = t.id ) as tip_amount_cnt'))
            ->where($table_name.'.id', $shop_id)->groupBy($table_name.'.id','t.id');
        $list = $listQuery->first();

        $user_table = (new User)->getTable();
        $users_arr = User::leftJoin('shop_users as su', 'su.user_id', '=', $user_table.'.id')->where('su.shop_id', '=', $shop_id)->select()->get();
        $data = compact('list','users_arr');
        return Admin::content(function (Content $content) use($data) {
            $content->body(view('admin/shopDetail', $data));
            $content->header('店铺详情');
        });
    }

    public function updates(Request $request)
    {
        $status = $request->input('status');
        $shop_id = $request->input('shop_id');
        //这里可能需要判断后台登录者的角色，是否具有权限
        if (isset($status)) {
            Shop::where('id',$shop_id)->update(['status'=>'2']);
        } else {
            Shop::where('id',$shop_id)->update(['status'=>'0']);
        }
        return redirect('/admin/shop/detail/'.$shop_id);
    }

}
