<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;

class ExcelController extends Controller
{
    public function  shop(Request $request){
        $manager_id = $request->input('manager_id');
        $shop_id = $request->input('shop_id');
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
        $query = Shop::leftJoin('transfer as t',function ($join) use($table_name) {
            $join->on( $table_name.'.id' ,'=' ,'t.shop_id' )
                ->where('t.status', '=', '3');
        })->leftJoin('transfer_record as tfr', function ($join) {
            $join->on('tfr.transfer_id', '=', 't.id')->where('tfr.stat', '=' , '2');
        })->leftJoin('users as u', 'u.id', '=', $table_name .'.manager')
            ->select( DB::raw($table_name.'.*'),'u.id as manager_id', 'u.name as manager_name',
                DB::raw('COUNT(t.id) as transfer_cnt'), DB::raw('SUM(tfr.amount) as summary'),
                DB::raw('SUM(tfr.fee_amount) as fee_amount_cnt '),
                DB::raw('(SELECT SUM(amount) FROM tip_record WHERE tip_record.transfer_id = t.id ) as tip_amount_cnt'));
        if(!empty($manager_id)) {
            $query->where('u.id', $manager_id);
        }
        if(!empty($shop_id)) {
            $query->where($table_name.'.id', $shop_id);
        }
        if(!empty($shop_name)) {
            $query->where($table_name.'.name', $shop_name);
        }
        if($begin && $end) {
            $query->where($table_name.'.created_at', '>=', $begin)->where($table_name.'.created_at', '<=', $end);
        }
        $query->groupBy($table_name.'.id','t.id')->orderBy('tip_amount_cnt','DESC')->withCount('shop_user');

        $list = $query->get();
        $cellData = [
            ['排名','店铺ID','店铺名','店主ID','店主名','店铺会员数','店铺手续费率','已付平台交易费','交易笔数','总交易额','店铺收入','店铺余额']
        ];
        if (!empty($list) && count($list)>0) {
            foreach($list as $key => $value) {
                $cellData[] = [
                    $key+1,
                    $value->id,
                    $value->name,
                    $value->manager_id,
                    $value->manager_name,
                    $value->shop_user_count,
                    (int)$value->type_value . '%',
                    $value->fee_amount_cnt ?? 0,
                    $value->transfer_cnt,
                    $value->summary ?? 0,
                    $value->tip_amount_cnt ?? 0,
                    $value->balance,
                ];
            }
        }
        Excel::create('店铺管理',function($excel) use ($cellData){
            $excel->sheet('店铺管理', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');
    }
}
