<?php

namespace App\Admin\Controllers;

use App\Admin as AdminUser;
use App\Agent\Card;
use App\Agent\CardStock;
use App\Agent\CardType;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentCardDataController extends Controller
{
    use ModelForm;

    //查询运营
    public function operate(Request $request)
    {
        $operators = [];
        $operator_username = $request->operator_username;
        if(!empty($operator_username)) {
            $operators = AdminUser::where('username',$operator_username)->withCount('agent_card')->first();
            if(empty($operators) || !$operators->isRole('operator')) {
                $_error = '运营不存在';
            }
            $card_type = CardType::query()->select('id','name')->get();
            if(empty($card_type)) {
                $_error = '没有可选卡种，请添加卡片类型';
            }

        }
        $data = compact('operators','_error','operator_username','card_type');
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
            return response()->json([
                'code' => -1,
                'msg' => '您没有操作权限',
                'data' => []
            ]);
        }
        //判断卡类型是否存在
        $agent_card_type = CardType::find($card_type);
        if(empty($agent_card_type)) {
            return response()->json([
                'code' => -1,
                'msg' => '不存在此类型的卡',
                'data' => []
            ]);
        };
        $card_expired = $agent_card_type->valid_days>0 ? date('Y-m-d H:i:s',strtotime("+{$agent_card_type->valid_days} days")) : NULL;
        //运营是否存在，可开卡数是否足够
        $operators = AdminUser::where('username',$operator_username)->whereHas('roles',function($query) {
            $query->where('slug','operator');
        })->first();
        if(empty($operators)) {
            return response()->json([
                'code' => -1,
                'msg' => '该用户不是运营',
                'data' => []
            ]);
        }
        if(CardStock::where('operator',$operators->id)->count() + $num
            > config('admin.max_agent_card','50')) {
            return response()->json([
                'code' => -1,
                'msg' => '开卡数目超出上限',
                'data' => []
            ]);
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
            return response()->json([
                'code' => -1,
                'msg' => '数据有误',
                'data' => []
            ]);
        }
        //添加记录到运营的卡库存
        DB::beginTransaction();
        try {
            DB::table((new CardStock())->getTable())->insert($card_stock_data);
        }   catch (\Exception $e){
            DB::rollBack();
            Card::destroy($card_id_arr);
            return response()->json([
                'code' => -1,
                'msg' => '请求失败',
                'data' => []
            ]);
        }
        DB::commit();
        return response()->json([
            'code' => 0,
            'msg' => '请求成功',
            'data' => []
        ]);
    }

}
