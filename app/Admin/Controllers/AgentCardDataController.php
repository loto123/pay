<?php

namespace App\Admin\Controllers;

use App\Admin as AdminUser;
use App\Agent\Card;
use App\Agent\CardDistribution;
use App\Agent\CardStock;
use App\Agent\CardType;
use App\Agent\CardUse;
use App\Http\Controllers\Controller;
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
        $card_type = $request->card_type;
        if(!empty($operator_username)) {
            $operators = AdminUser::where('username',$operator_username)->first();
            if(empty($operators)) {
                $_error = '用户不存在';
            }
        }

        $card_stock_query = CardStock::where('operator',Admin::user()->id)->where('state',CardStock::SALE);
        $card_stock_count = $card_stock_query->count();

        $card_type_list = CardType::query()->select('id','name')->get();
        if(empty($card_type_list)) {
            $_error = '没有可选的卡片，请先添加卡片类型';
        }

        $current_card_cnt = 0;
        if(!empty($card_type)) {
            if(!CardType::find($card_type)) {
                $_error = '该卡片类型不存在';
            }
            $current_card_cnt = $card_stock_query->whereHas('card',function($query) use ($card_type) {
                $query->where('card_type',$card_type);
            })->count();
        }

        $data = isset($_error)?compact('_error','operator_username','card_type_list')
            :compact('operators','operator_username','card_type_list','card_type','card_stock_count','current_card_cnt');

        return Admin::content(function (Content $content) use ($data) {
            $content->header("添加VIP卡给运营");
            $content->body(view('admin.agent_card.operator', $data));
        });
    }

    //创建VIP卡
    public function create_agent_card(Request $request) {
        $card_type = $request->card_type;
        $num = $request->num;
        $operator_username = $request->operator_username;

        //判断卡类型是否存在
        $agent_card_type = CardType::find($card_type);
        if(empty($agent_card_type)) {
            return response()->json(['code' => -1,'msg' => '不存在此类型的卡','data' => []]);
        };
        //运营是否存在，可开卡数是否足够
        $operators = AdminUser::where('username',$operator_username)->first();
        if(empty($operators)) {
            return response()->json(['code' => -1,'msg' => '该用户不存在','data' => []]);
        }

        if(CardStock::where('operator',$operators->id)->count() + $num
            > config('max_agent_card','50')) {
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
        $request_promoter = $request->promoter;
        $card_type = $request->card_type;

        $card_type_list = CardType::query()->select('id','name')->get();
        if(empty($card_type_list)) {
            $_error = '没有可选的卡片，请先添加卡片类型';
        }

        if(!empty($card_type) && !CardType::find($card_type)) {
            $_error = '该卡片类型不存在';
        }

        if(!empty($request_promoter)) {
            $promoter = User::where('mobile',$request_promoter)->withCount(['promoter_cards'=>function($query){
                $query->where('is_bound',Card::UNBOUND);
            }])->first();

            if(empty($promoter) || !$promoter->isPromoter()) {
                $_error = '该推广员不存在';
            } else {
                $promoter_current_card_cnt = Card::where('promoter_id',$promoter->id)
                    ->where('is_bound',Card::UNBOUND)->where('card_type',$card_type)->count();
                $card_stock_query = CardStock::where('operator',Admin::user()->id)->where('state',CardStock::SALE)->with('card');
                $card_stock = $card_stock_query->get();
                $sale_card_cnt = $card_stock->count();

                if(empty($sale_card_cnt)) {
                    $_error = '您没有可用的VIP卡';
                }

                $operator_card_cnt = $card_stock_query->whereHas('card',function($query) use ($card_type) {
                    $query->where('card_type',$card_type);
                })->count();
            }

        }

        $data= isset($_error) ? compact('_error','request_promoter','card_type_list')
            : compact('promoter','operator','sale_card_cnt','request_promoter','card_type','card_type_list',
                'operator_card_cnt','promoter_current_card_cnt');
        return Admin::content(function (Content $content) use($data){
            $content->header("添加VIP卡给推广员");
            $content->body(view('admin.agent_card.promoter', $data));
        });
    }

    //给推广员添加VIP卡
    public function send_card_to_promoter(Request $request)
    {
        $request_promoter = $request->promoter;
        $num = $request->num;
        $card_type = $request->card_type;

        //卡数目
        $card_stock_query = CardStock::where('operator',Admin::user()->id)
            ->where('state',CardStock::SALE)->whereHas('card',function($query) use($card_type) {
                $query->where('card_type',$card_type);
            });
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
            DB::table((new Card())->getTable())->whereIn('id',$card_ids)->update([
                'owner' => $promoter->id,
                'promoter_id' => $promoter->id,
            ]);
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

        $query = CardStock::query()->with(['distributions.promoter', 'allocate_bys', 'operators', 'card']);
        //运营只能看到自己的
        if(!Admin::user()->can('create_agent_card')) {
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
            $query = $query->whereHas('distributions', function ($query) use ($promoter_id) {
                $query->whereHas('promoter', function ($query) use ($promoter_id) {
                    $query->where('mobile', $promoter_id);
                });
            });
        }

        if(!empty($card_id)) {
            $query = $query->where('card_id',$en_card_id);
        }

        if(!empty($begin) && !empty($end)) {
            $query = $query->where('created_at','>=',$begin)->where('created_at','<=',$end);
        }

        $count = $query->count();
        $list = $query->paginate($this->limit);
        $offset = ($request->page>1 ? $request->page-1 : 0 ) * $this->limit;
        $data = compact('count','offset','list','allocate_id','operator_id','card_id','promoter_id','date_time');
        return Admin::content(function (Content $content) use ($data) {
            $content->header("拨卡记录");
            $content->body(view('admin.agent_card.card_record', $data));
        });
    }

    //VIP卡查询
    public function cards(Request $request)
    {
        $card_id = $request->card_id;
        $agent_id = $request->agent_id;
        $operator_id = $request->operator_id;
        $promoter_id = $request->promoter_id;
        $is_bound = $request->is_bound;
        $is_frozen = $request->is_frozen;
        $date_time = $request->date_time;
        if (!empty($date_time)) {
            $date_time_arr = explode(' - ', $date_time);
            $begin = $date_time_arr[0];
            $end = $end = $date_time_arr[1] . ' 23:59:59';
        }

        $query = Card::query()->with(['owner_user', 'stock.operators', 'promoter']);
        //运营只能看到自己的
        if(!Admin::user()->can('create_agent_card')) {
            $query = $query->whereHas('stock.operators', function ($query) {
                $query->where('id', Admin::user()->id);
            });
        }

        if (!empty($card_id)) {
            $query = $query->where('id', (new Card())->recover_id($card_id));
        }

        if (!empty($agent_id)) {
            $query = $query->whereHas('owner_user', function ($query) use ($agent_id) {
                $query->where('mobile', $agent_id);
            })->where('is_bound', Card::BOUND);
        }

        if (!empty($operator_id)) {
            $query = $query->whereHas('stock.operators', function ($query) use ($operator_id) {
                $query->where('username', $operator_id);
            });
        }

        if (!empty($promoter_id)) {
            $query = $query->whereHas('promoter', function ($query) use ($promoter_id) {
                $query->where('mobile', $promoter_id);
            });
        }

        if (!empty($is_bound)) {
            $query = $query->where('is_bound', $is_bound);
        }

        if (!empty($is_frozen)) {
            $query = $query->where('is_frozen', $is_frozen);
        }

        if (!empty($begin) && !empty($end)) {
            $query = $query->where('created_at', '>=', $begin)->where('created_at', '<=', $end);
        }

        $count = $query->count();
        $list = $query->paginate($this->limit);
        $offset = ($request->page > 1 ? $request->page - 1 : 0) * $this->limit;


        $data = compact('list','count','offset','card_id','agent_id','operator_id','promoter_id','is_bound','is_frozen');
        return Admin::content(function (Content $content) use ($data) {
            $content->header("VIP卡查询");
            $content->body(view('admin.agent_card.card', $data));
        });
    }

    /*
     * 冻结vip卡
     * type 1:冻结（默认），0：解冻
     * */
    public function updates_card($card_id,$type=1)
    {
        $redirect_url = '/admin/agent_card/cards';
        $type_list = [1,0];
        if(!in_array($type,$type_list)) {
            return redirect($redirect_url)->with('status', '请求有误！');
        }

        $card = Card::find(Card::recover_id($card_id));
        if(empty($card)) {
            return redirect($redirect_url)->with('status', '该VIP卡不存在！');
        }
        if($type==1) {//冻结
            if($card->is_bound == $card::UNBOUND) {
                return redirect($redirect_url)->with('status', '未出售的卡不能冻结！');
            }
            if($card->is_frozen == $card::FROZEN) {
                return redirect($redirect_url)->with('status', '该卡已冻结！');
            }
            $card->is_frozen = $card::FROZEN;
        } else {//解冻
            if($card->is_frozen != $card::FROZEN) {
                return redirect($redirect_url)->with('status', '未冻结的卡不能解冻！');
            }
            $card->is_frozen = $card::UNFROZEN;
        }

        if ($card->save()) {
            return redirect($redirect_url)->with('status', '成功！');
        } else {
            return redirect($redirect_url)->with('status', '操作失败！');
        }

    }

    public function card_trace($card_id)
    {
        $card = Card::where('id',Card::recover_id($card_id))
            ->with('stock','card_use','stock.distributions','card_use','card_use.fromUser','card_use.toUser')
            ->first();
        $allocate_bys = AdminUser::find($card->stock['allocate_by']);
        $operators = AdminUser::find($card->stock['operator']);
        $card_use = $card->card_use;
        $promoter = User::find($card->stock['distributions']['to_promoter']);

        $list[] = [
            'from'=> $allocate_bys,
            'to' => $operators,
            'created_at' => $card->stock['created_at']
        ];
        if($operators && $promoter) {
            $list[] = [
                'from'=> $operators,
                'to' => $promoter,
                'created_at' => $card->stock['distributions']['created_at']
            ];
        }

        if($card_use) {
            foreach ($card_use as $item) {
                $list[] =  [
                    'from'=> $item->fromUser,
                    'to' => $item->toUser,
                    'created_at' => $item->created_at
                ];
            }
            krsort($list);
        }
        $data = compact('list','card_id');
        return Admin::content(function (Content $content) use ($data) {
            $content->header("流转记录");
            $content->body(view('admin.agent_card.card_trace', $data));
        });
    }


}
