<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Shop;
use App\Transfer;
use App\User;
use Dingo\Api\Http\Response;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Invoker\ParameterResolver\DefaultValueResolver;

class ShopController extends Controller
{

    private $limit = 20;

    //公会管理
    public function index(Request $request)
    {
        $manager_id = $request->input('manager_id');
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
        })->leftJoin('tip_record as tr','tr.transfer_id','=','t.id')
                ->with(['container','manager'])
            ->select( DB::raw($table_name.'.*'),
                DB::raw('COUNT(t.id) as transfer_cnt'), DB::raw('SUM(tfr.amount) as summary'),
                DB::raw('SUM(tfr.fee_amount) as fee_amount_cnt '),
                DB::raw('SUM(tr.amount) as tip_amount_cnt'));
        if($manager_id) {
            $listQuery->whereHas('manager', function ($query) use($manager_id) {
                $query->where('mobile',$manager_id);
            });
        }
        if($shop_id) {
            $listQuery->where($table_name.'.id', $shop_id);
            $countQuery->where($table_name.'.id', $shop_id);
        }
        if($shop_name) {
            $listQuery->where($table_name.'.name', 'like', '%'.$shop_name.'%');
            $countQuery->where($table_name.'.name', 'like', '%'.$shop_name.'%');
        }
        if($begin && $end) {
            $listQuery->where($table_name.'.created_at', '>=', $begin)->where($table_name.'.created_at', '<=', $end);
            $countQuery->where($table_name.'.created_at', '>=', $begin)->where($table_name.'.created_at', '<=', $end);
        }
        $listQuery->groupBy($table_name.'.id')->orderBy('tip_amount_cnt','DESC')->orderBy($table_name.'.id','DESC')->withCount('shop_user');

        $count = $countQuery->count();
        $list = $listQuery->paginate($this->limit);

        //未关闭任务
        $unclose_transfer = Shop::query()->withCount(['transfer' => function ($query) {
            $query->where('status','<>','3');
        }])->get();
        $unclose_cnt_list = [];
        if(!empty($unclose_transfer) && count($unclose_transfer)) {
            foreach ($unclose_transfer as $item) {
                $unclose_cnt_list[$item->id] = $item->transfer_count;
            }
        }

        $offset = ($request->page>1 ? $request->page-1 : 0 ) * $this->limit;
        $manager_id = $request->input('manager_id');
        $shop_id = $request->input('shop_id');
        $data = compact('list','count','date_time','manager_id','shop_id','shop_name','offset','unclose_cnt_list');
        return Admin::content(function (Content $content) use($data) {
            $content->body(view('admin/shop',$data));
            $content->header("公会管理");
        });
    }

    //公会详情
    public function details($shop_id)//公会id
    {
        $table_name = (new Shop)->getTable();
        $listQuery = Shop::leftJoin('transfer as t',function ($join) use($table_name) {
            $join->on( $table_name.'.id' ,'=' ,'t.shop_id' )
                ->where('t.status', '=', '3');
        })->leftJoin('transfer_record as tfr', function ($join) {
            $join->on('tfr.transfer_id', '=', 't.id')->where('tfr.stat', '=' , '2');
        })->with(['manager','users'])
            ->select( DB::raw($table_name.'.*'),
                DB::raw('COUNT(t.id) as transfer_cnt'), DB::raw('SUM(tfr.amount) as summary'),
                DB::raw('SUM(tfr.fee_amount) as fee_amount_cnt '),
                DB::raw('(SELECT SUM(amount) FROM tip_record WHERE tip_record.transfer_id = t.id ) as tip_amount_cnt'))
            ->where($table_name.'.id', $shop_id)->groupBy($table_name.'.id','t.id');
        $list = $listQuery->first();
        $data = compact('list');
        return Admin::content(function (Content $content) use($data) {
            $content->body(view('admin/shopDetail', $data));
            $content->header('公会详情');
        });
    }

    /*
     * 更新公会状态
     * 返回公会详情页面
     * */
    public function updates($shop_id,$status)
    {
        //只有管理员才能操作
        //只能在冻结和正常两种状态互切
        if(!Admin::user()->isRole('administrator')) {
            abort(404);
        }
        if(Shop::where('id',$shop_id)->update(['status'=>$status])) {
            return redirect('/admin/shop')->with('status', '更新成功！');
        } else {
            return redirect('/admin/shop')->with('status','更新失败！');
        }
    }

    public function delete($shop_id) {
        if(!Admin::user()->isRole('administrator')) {
            abort(404);
        }
        $shop = Shop::where('id',$shop_id)->withCount(['transfer' => function ($query) {
            $query->where('status','<>','3');
        }])->first();
        if($shop && $shop->transfer_count > 0) {
            abort(404,'该公会有任务未关闭，不能删除！');
        }
        if($shop->delete()) {
            return redirect('/admin/shop')->with('status', '删除成功！');
        } else {
            return redirect('/admin/shop')->with('status','删除失败！');
        }

    }

}
