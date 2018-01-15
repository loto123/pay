<?php

namespace App\Admin\Controllers;

use App\Admin as AdminUser;
use App\Agent\Card;
use App\Agent\CardDistribution;
use App\Agent\CardStock;
use App\Agent\CardType;
use App\Http\Controllers\Controller;
use App\Pay\IdConfuse;
use App\User;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgentCardDataController extends Controller
{
    use ModelForm;
    private $limit = 20;

    //查询运营
    public function operate(Request $request)
    {
        $operators = [];
        $operator_username = $request->operator_username;

        if(!empty($operator_username)) {
            $operators = AdminUser::where('username',$operator_username)->withCount('card_stock')->first();
            if(empty($operators) || !$operators->isRole('operator')) {
                $_error = '运营不存在';
            }
            $card_type = CardType::query()->select('id','name')->get();
            if(empty($card_type)) {
                $_error = '没有可选的卡片，请先添加卡片类型';
            }

        }

        $data = isset($_error)?compact('_error','operator_username'):compact('operators','operator_username','card_type');
        return Admin::content(function (Content $content) use ($data) {
            $content->header("添加VIP卡");
            $content->body(view('admin.agent_card.operator', $data));
        });
    }

    //创建VIP卡
    public function create_agent_card(Request $request) {
        $card_type = $request->card_type;
        $num = $request->num;
        $operator_username = $request->operator_username;

        //判断权限
        if(!Admin::user()->can('create_agent_card')) {
            return response()->json(['code' => -1,'msg' => '您没有操作权限','data' => []]);
        }

        //判断卡类型是否存在
        $agent_card_type = CardType::find($card_type);
        if(empty($agent_card_type)) {
            return response()->json(['code' => -1,'msg' => '不存在此类型的卡','data' => []]);
        };
        $card_expired = $agent_card_type->valid_days>0 ? date('Y-m-d H:i:s',strtotime("+{$agent_card_type->valid_days} days")) : NULL;

        //运营是否存在，可开卡数是否足够
        $operators = AdminUser::where('username',$operator_username)->whereHas('roles',function($query) {
            $query->where('slug','operator');
        })->first();
        if(empty($operators)) {
            return response()->json(['code' => -1,'msg' => '该用户不是运营','data' => []]);
        }

        if(CardStock::where('operator',$operators->id)->count() + $num
            > config('admin.max_agent_card','50')) {
            return response()->json(['code' => -1,'msg' => '开卡数目超出上限','data' => []]);
        }

        //生成卡
        $time = date('Y-m-d H:i:s');
        $card_stock_data = [];
        $card_id_arr = [];
        for($i=0;$i<$num;$i++) {
            //生成VIP卡
            $card_id = DB::table((new Card())->getTable())->insertGetId([
                'card_type' => $card_type,
                'created_at' => $time,
                'expired_at' => $card_expired,
            ]);
            //记录card_id
            $card_id_arr[] = $card_id;
            //准备运营卡库存的数据
            $card_stock_data[] = [
                'operator' => $operators->id,
                'allocate_by' => Admin::user()->id,
                'card_id' => $card_id,
                'state' => 0,
                'created_at' => $time,
            ];
        }

        if(empty($card_id_arr) || empty($card_stock_data)) {
            return response()->json(['code' => -1,'msg' => '数据有误','data' => []]);
        }

        //添加记录到运营的卡库存
        DB::beginTransaction();
        try {
            DB::table((new CardStock())->getTable())->insert($card_stock_data);
        }   catch (\Exception $e){
            DB::rollBack();
            Card::destroy($card_id_arr);
            return response()->json(['code' => -1,'msg' => '请求失败','data' => []]);
        }
        DB::commit();

        return response()->json(['code' => 0,'msg' => '成功为'.$operator_username.'添加'.$num.'张卡','data' => []]);
    }

    public function promoter(Request $request)
    {
        if(!Admin::user()->isRole('operator')) {
            $_error = '没有操作权限';
        }

        $request_promoter = $request->promoter;
        if(!empty($request_promoter)) {
            $promoter = User::where('mobile',$request_promoter)->first();
            $sale_card_cnt = CardStock::where('operator',Admin::user()->id)->where('state',CardStock::SALE)->count();
            if(empty($promoter) || !$promoter->isPromoter()) {
                $_error = '该推广员不存在';
            }
            if(empty($sale_card_cnt)) {
                $_error = '您没有可用的VIP卡';
            }
        }

        $data= isset($_error)?compact('_error','request_promoter'):compact('promoter','operator','sale_card_cnt','request_promoter');
        return Admin::content(function (Content $content) use($data) {
            $content->header("添加VIP卡");
            $content->body(view('admin.agent_card.promoter', $data));
        });
    }

    //给推广员添加VIP卡
    public function send_card_to_promoter(Request $request)
    {
        $request_promoter = $request->promoter;
        $num = $request->num;
        //操作权限
        if(!Admin::user()->isRole('operator')) {
            return response()->json(['code' => -1,'msg' => '没有操作权限','data' => []]);
        }

        //卡数目
        $card_stock_query = DB::table((new CardStock())->getTable())->where('operator',Admin::user()->id)
            ->where('state',CardStock::SALE);
        $sale_card_cnt = $card_stock_query->count();
        if($sale_card_cnt < $num) {
            return response()->json(['code' => -1,'msg' => '余量不足','data' => []]);
        }

        //推广员
        $promoter = User::where('mobile',$request_promoter)->first();
        if(empty($promoter) || !$promoter->isPromoter()) {
            return response()->json(['code' => -1,'msg' => '该推广员不存在','data' => []]);
        }

        //更新运营库存中对应卡的state,更新VIP卡的owner，添加记录到运营分销表
        //准备数据
        $card_stock = $card_stock_query->limit($num)->get();
        $card_ids = [];
        $card_stock_id = [];
        $distributions = [];
        foreach ($card_stock as $item) {
            $card_ids[] = $item->card_id;
            $card_stock_id[] = $item->id;
            $distributions[] = [
                'stock_id' => $item->id,
                'to_promoter' => $promoter->id
            ];
        }
        if(empty($card_ids) || empty($card_stock_id) ||empty($distributions)) {
            return response()->json(['code' => -1,'msg' => '数据异常','data' => []]);
        }

        //更新记录
        DB::beginTransaction();
        try{
            DB::table((new CardStock())->getTable())->whereIn ('id',$card_stock_id)->update([
                'state' => CardStock::SOLD,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            DB::table((new Card())->getTable())->whereIn('id',$card_ids)->update(['owner'=>$promoter->id]);
            DB::table((new CardDistribution())->getTable())->insert($distributions);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => -1,'msg' => '操作失败','data' => []]);
        }
        DB::commit();

        return response()->json(['code' => 0,'msg' => '成功为'.$request_promoter.'添加'.$num.'张卡','data' => []]);

    }

    //拨卡记录
    public function card_record(Request $request)
    {
        $allocate_id = $request->allocate_id;
        $operator_id = $request->operator_id;
        $card_id = $request->card_id;
        $en_card_id = (new Card())->recover_id($card_id);
        $promoter_id = $request->promoter_id;
        $date_time = $request->date_time;
        $begin = '';
        $end = '';
        if (!empty($date_time)) {
            $date_time_arr = explode(' - ', $date_time);
            $begin = $date_time_arr[0];
            $end = $end = $date_time_arr[1] . ' 23:59:59';
        }

        $query = CardStock::query()->with(['distributions.promoter','allocate_bys','operators','card']);
        //运营只能看到自己的
        if(!Admin::user()->can('create_agent_card') && Admin::user()->isRole('operator')) {
            $query = $query->where('operator',Admin::user()->id);
        }
        if(!empty($allocate_id)) {
            $query = $query->whereHas('allocate_bys',function($query) use($allocate_id) {
                $query->where('username',$allocate_id);
            });
        }
        if(!empty($operator_id)) {
            $query = $query->whereHas('operators',function($query) use($operator_id) {
                $query->where('username',$operator_id);
            });
        }
        if(!empty($promoter_id)) {
            $query = $query->whereHas('distributions',function($query) use($promoter_id){
                $query->whereHas('promoter',function ($query) use($promoter_id) {
                    $query->where('mobile',$promoter_id);
                });
            });
        }
        if(!empty($card_id)) {
            $query = $query->where('card_id',$en_card_id);
        }
        if(!empty($begin) && !empty($end)) {
            $query = $query->where('created_at','>=',$begin)->where('created_at','<=',$end);
        }
        $list = $query->select()->get();
        $data = compact('list','allocate_id','operator_id','card_id','promoter_id','date_time');
        return Admin::content(function (Content $content) use ($data) {
            $content->header("拨卡记录");
            $content->body(view('admin.agent_card.card_record', $data));
        });
    }

    //VIP卡查询
    public function cards(Request $request)
    {
        $data = [];
        return Admin::content(function (Content $content) use ($data) {
            $content->header("VIP卡查询");
            $content->body(view('admin.agent_card.card', $data));
        });
    }

}
