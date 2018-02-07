<?php

namespace App\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use App\Agent\Card;
use App\Agent\CardStock;
use App\Http\Controllers\Controller;
use App\Shop;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Illuminate\Support\Facades\Log;

class ExcelController extends Controller
{
    public function shop(Request $request)
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
        if(!empty($manager_id)) {
            $listQuery->whereHas('manager', function ($query) use($manager_id) {
                $query->where('mobile',$manager_id);
            });
        }
        if(!empty($shop_id)) {
            $listQuery->where($table_name.'.id', $shop_id);
        }
        if(!empty($shop_name)) {
            $listQuery->where($table_name.'.name', 'like', '%'.$shop_name.'%');
        }
        if($begin && $end) {
            $listQuery->where($table_name.'.created_at', '>=', $begin)->where($table_name.'.created_at', '<=', $end);
        }
        $listQuery->groupBy($table_name.'.id')->orderBy('tip_amount_cnt','DESC')->orderBy($table_name.'.id','DESC')->withCount('shop_user');

        $list = $listQuery->get();
        $cellData = [
            ['排名','公会ID','公会名称','会长ID','会长昵称','公会会员数','公会佣金费率','已付平台手续费','任务笔数','总交易额','公会获得钻石','公会剩余钻石','公会状态','公会任务开启状态']
        ];
        if (!empty($list) && count($list) > 0) {
            foreach ($list as $key => $value) {
                $cellData[] = [
                    $key + 1,
                    Shop::encrypt($value->id),
                    $value->name,
                    $value->manager['mobile'],
                    $value->manager['name'],
                    $value->shop_user_count,
                    $value->fee . '%',
                    $value->fee_amount_cnt ?? 0,
                    $value->transfer_cnt,
                    $value->summary ?? 0,
                    $value->tip_amount_cnt ?? 0,
                    $value->container['balance'],
                    $value->status > 0 ? ($value->status == 1 ? '已解散' : '已冻结') : '正常',
                    $value->active ? '开启' : '关闭',
                ];
            }
        }
        Excel::create('店铺管理', function ($excel) use ($cellData) {
            $excel->sheet('店铺管理', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        })->export('xls');
    }

    public function user(Request $request)
    {
        $cellData = [
            ['编号', '用户id', '用户名称', '身份', '任务笔数', '剩余钻石', '收益', '拿钻', '交钻', '上级运营id', '上级运营名称', '上级代理', '支付渠道']
        ];

        $user_table = (new User)->getTable();
        $query = User::leftJoin('transfer_record as tfr', 'tfr.user_id', '=', $user_table . '.id')
            ->with(['roles', 'operator'])
            ->select($user_table . '.*',
                DB::raw('ABS(SUM( CASE WHEN stat=1 THEN amount ELSE 0 END)) AS payment'),
                DB::raw('ABS(SUM( CASE WHEN stat=2 THEN real_amount ELSE 0 END)) AS profit'),
                DB::raw('COUNT(tfr.id) AS transfer_count'));
        if ($request->user_id) {
            $query->where($user_table . '.mobile', $request->user_id);
        }
        if ($request->parent_id) {
            $query->whereHas('parent',function ($query) use($request) {
                $query->where('mobile',$request->parent_id);
            });
        }
        if ($request->operator_id) {
            $query->join('admin_users as au','au.id','=',$user_table.'.operator_id')->where('au.username', $request->operator_id);
        }
        if ($request->role > 0) {
            $query->whereHas('roles', function ($query) use ($request) {
                $query->where('id', $request->role);
            });
        }
        if ($request->channel_id) {
            $query->where($user_table . '.channel_id', $request->channel_id);
        }
        $query = $query->groupBy($user_table . '.id')->orderBy('transfer_count','DESC')->orderBy($user_table.'.id')->get();

        if (!empty($query) && count($query) > 0) {
            foreach ($query as $item) {
                $role_name = '';
                if (!empty($item->roles) && count($item->roles) > 0) {
                    foreach ($item->roles as $_role) {
                        $role_name .= $_role->display_name . '/';
                    }
                    $role_name = rtrim($role_name,'/');
                }
                $cellData[] = [
                    User::encrypt($item->id),
                    $item->mobile,
                    $item->name,
                    $role_name,
                    $item->transfer_count??0,
                    $item->balance,
                    ($item->profit - $item->payment)??0,
                    $item->profit??0,
                    $item->payment??0,
                    $item->operator['username'],
                    $item->operator['name'],
                    $item->parent['mobile'],
                    $item->channel_id,
                ];
            }
        }

        Excel::create('用户管理', function ($excel) use ($cellData) {
            $excel->sheet('用户管理', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        })->export('xls');
    }

    //用户统计数据导出
    public function dataUser(Request $request)
    {
        $cellData = [
            ['排名', '用户id', '用户昵称', '身份', '上级代理id', '上级代理昵称', '上级运营账号', '上级运营昵称', '交易总额', '交易笔数',
                '收款', '付款', '余额', '已付平台手续费', '直属用户数', '直属代理数', '代理业绩', '代理分润收益']
        ];
        $date_time = $request->input('date_time');
        $begin = $end = '';
        if (!empty($date_time)) {
            $date_time_arr = explode(' - ', $request->input('date_time'));
            $begin = $date_time_arr[0];
            $end = $date_time_arr[1] . ' 23:59:59';
        }

        $listQuery = User::with(['roles', 'parent', 'operator',
            'transfer_record' => function ($query) use ($begin, $end) {
                $query->where('stat', '>', 0)->where('stat', '<>', 3);
                if ($begin && $end) {
                    $query->where('transfer_record.created_at', '>=', $begin)
                        ->where('transfer_record.created_at', '<=', $end);
                }
            },
            'output_profit' => function ($query) use ($begin, $end) {
                if ($begin && $end) {
                    $query->where('profit_record.created_at', '>=', $begin)->where('profit_record.created_at', '<=', $end);
                }
            }
        ])
            ->withCount(['child_proxy', 'child_user'])
            ->leftJoin('transfer_record', function ($join) use ($begin, $end) {
                $join->on('users.id', '=', 'transfer_record.user_id');
                $join->where('stat', '>', 0)->where('stat', '<>', 3);
                if ($begin && $end) {
                    $join->where('transfer_record.created_at', '>=', $begin)->where('transfer_record.created_at', '<=', $end);
                }
            })
            ->leftJoin('profit_record', function ($join) use ($begin, $end) {
                $join->on('users.id', '=', 'profit_record.proxy');
                if ($begin && $end) {
                    $join->where('profit_record.created_at', '>=', $begin)->where('profit_record.created_at', '<=', $end);
                }
            })
            ->addSelect(DB::raw('sum(abs(transfer_record.amount)) as trans_amount'))
            ->addSelect(DB::raw('sum(profit_record.proxy_amount) as profit_proxy_amount'), DB::raw('sum(profit_record.fee_amount) as proxy_fee_amount'))
            ->groupBy('users.id');
        //用户ID
        $aid = $request->input('aid');
        if ($aid) {
            $listQuery->where('users.id', $aid);
        }
        //上级代理ID
        $parent = $request->input('parent');
        if ($parent) {
            $listQuery->where('users.parent_id', $parent);
        }
        //运营ID
        $operator = $request->input('operator');
        if ($operator) {
            $listQuery->where('users.operator_id', $operator);
        }
        //role 身份
        $role = $request->input('role');
        if ($role) {
            $listQuery->whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role);
            });
        }
        //排序方式
        $orderby = $request->input('orderby', 'trans_amount');
        if ($orderby) {
            $listQuery->orderBy($orderby, 'DESC');
        }
        $list = $listQuery->orderBy('created_at', 'DESC')->get();
        foreach ($list as $key => $item) {
            $roles = '';
            foreach ($item->roles as $k => $v) {
                $roles .= $v->display_name . '\r\n';
            }
            $cellData[] = [
                $key + 1,
                $item->id,
                $item->name,
                $roles,
                $item->proxy ? $item->proxy->id : '无',
                $item->proxy ? $item->proxy->name : '无',
                $item->operator ? $item->operator->id : '无',
                $item->operator ? $item->operator->name : '无',
                $item->trans_amount ?? 0,
                $item->transfer_record->count(),
                $item->transfer_record->where('stat', 2)->sum('amount'),
                abs($item->transfer_record->where('stat', 1)->sum('amount')),
                $item->balance,
                $item->output_profit->sum('fee_amount'),
                $item->child_user_count,
                $item->child_proxy_count,
                $item->proxy_fee_amount ?? 0,
                $item->profit_proxy_amount ?? 0
            ];
        }
        Excel::create('用户统计', function ($excel) use ($cellData) {
            $excel->sheet('用户统计', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        })->export('xls');
    }

    //收益统计数据导出
    public function dataProfit(Request $request)
    {
        $cellData = [
            ['排名', '用户id', '用户昵称', '上级代理id', '上级代理昵称', '上级运营账号', '上级运营昵称', '收款笔数', '收款金额',
                '店铺分润', '代理分润', '运营业绩']
        ];
        $with = ['parent', 'operator'];
        $query = User::query();
        //用户ID
        $aid = $request->input('aid');
        if ($aid) {
            $query->where('users.id', $aid);
        }
        //推荐人ID
        $parent = $request->input('parent');
        if ($parent) {
            $query->where('users.parent_id', $parent);
        }
        //运营ID
        $operator = $request->input('operator');
        if ($operator) {
            $query->where('users.operator_id', $operator);
        }
        $date_time = $request->input('date_time');
        $begin = '';
        $end = '';
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
        $list = $query->with($with)->leftJoin('profit_record', function ($join) use ($begin, $end) {
            $join->on('users.id', '=', 'profit_record.user_id');
            if ($begin && $end) {
                $join->where('profit_record.created_at', '>=', $begin);
                $join->where('profit_record.created_at', '<=', $end);
            }
        })
            ->select('users.*', DB::raw('SUM(profit_record.fee_amount) as fee_amount_total'))
            ->orderBy('fee_amount_total', 'DESC')->groupBy('users.id')->get();
        foreach ($list as $key => $item) {
            $cellData[] = [
                $key + 1,
                $item->id,
                $item->name,
                $item->proxy ? $item->proxy->id : '无',
                $item->proxy ? $item->proxy->name : '无',
                $item->operator ? $item->operator->id : '无',
                $item->operator ? $item->operator->name : '无',
                $item->transfer_record->count(),
                $item->transfer_record->sum('amount'),
                $item->tips->sum('amount'),
                $item->output_profit->sum('proxy_amount'),
                $item->output_profit->sum('fee_amount')
            ];
        }
        Excel::create('收益统计', function ($excel) use ($cellData) {
            $excel->sheet('收益统计', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        })->export('xls');
    }

    //VIP卡-拨卡记录
    public function card_record(Request $request)
    {
        $cellData = [['序号', '卡类型','制卡人', '制卡人ID', '拨卡人', '拨卡人ID', '卡号', '收卡人', '收卡人ID', '拨卡时间']];
        $allocate_id = $request->allocate_id;
        $operator_id = $request->operator_id;
        $card_id = $request->card_id;
        $en_card_id = (new Card())->recover_id($card_id);
        $promoter_id = $request->promoter_id;
        $date_time = $request->date_time;
        $card_type = $request->card_type;
        $begin = '';
        $end = '';
        if (!empty($date_time)) {
            $date_time_arr = explode(' - ', $date_time);
            $begin = $date_time_arr[0];
            $end = $end = $date_time_arr[1] . ' 23:59:59';
        }

        $query = CardStock::query()->with(['distributions.promoter', 'allocate_bys', 'operators', 'card', 'card.type']);
        //运营只能看到自己的
        if (!Admin::user()->can('create_agent_card') && Admin::user()->can('operate_agent_card')) {
            $query = $query->where('operator', Admin::user()->id);
        }

        if (!empty($allocate_id)) {
            $query = $query->whereHas('allocate_bys', function ($query) use ($allocate_id) {
                $query->where('username', $allocate_id);
            });
        }

        if (!empty($operator_id)) {
            $query = $query->whereHas('operators', function ($query) use ($operator_id) {
                $query->where('username', $operator_id);
            });
        }

        if (!empty($promoter_id)) {
            $query = $query->whereHas('distributions', function ($query) use ($promoter_id) {
                $query->whereHas('promoter', function ($query) use ($promoter_id) {
                    $query->where('mobile', $promoter_id);
                });
            });
        }

        if (!empty($card_id)) {
            $query = $query->where('card_id', $en_card_id);
        }

        if (!empty($begin) && !empty($end)) {
            $query = $query->where('created_at', '>=', $begin)->where('created_at', '<=', $end);
        }

        if(!empty($card_type)) {
            $query = $query->whereHas('card', function($query) use($card_type) {
                $query->where('card_type',$card_type);
            });
        }

        $list = $query->get();
        if (!empty($list)) {
            foreach ($list as $key => $item) {
                $cellData[] = [
                    $key + 1,
                    $item->card['type']['name'],
                    $item->allocate_bys ? $item->allocate_bys['name'] : '无',
                    $item->allocate_bys ? $item->allocate_bys['username'] : '无',
                    $item->operators ? $item->operators['name'] : '无',
                    $item->operators ? $item->operators['username'] : '无',
                    $item->card->mix_id(),
                    $item->distributions ? $item->distributions['promoter']['name'] : '无',
                    $item->distributions ? $item->distributions['promoter']['mobile'] : '无',
                    $item->created_at
                ];
            }
        }
        Excel::create('拨卡记录', function ($excel) use ($cellData) {
            $excel->sheet('拨卡记录', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        })->export('xls');
    }

    //VIP卡-查询
    public function cards(Request $request)
    {
        $cellData = [['序号','卡类型','卡号', '运营', '运营ID', '推广员', '推广员ID', '用卡人', '用卡人ID', '状态', '是否冻结']];
        $card_id = $request->card_id;
        $agent_id = $request->agent_id;
        $operator_id = $request->operator_id;
        $promoter_id = $request->promoter_id;
        $is_bound = $request->is_bound;
        $is_frozen = $request->is_frozen;
        $date_time = $request->date_time;
        $card_type = $request->card_type;

        if (!empty($date_time)) {
            $date_time_arr = explode(' - ', $date_time);
            $begin = $date_time_arr[0];
            $end = $end = $date_time_arr[1] . ' 23:59:59';
        }

        $query = Card::query()->with(['owner_user', 'stock.operators', 'promoter', 'type']);

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

        if(!empty($card_type)) {
            $query = $query->where('card_type',$card_type);
        }

        $list = $query->get();
        if (!empty($list)) {
            foreach ($list as $key => $item) {
                $item_owner_name = '无';
                $item_owner_id = '无';
                $item_operator_name = '无';
                $item_operator_id = '无';
                if ($item->is_bound == $item::BOUND && $owner = $item->owner_user) {
                    $item_owner_name = $owner['name'];
                    $item_owner_id = $owner['mobile'];
                }
                if (!empty($item->stock) && isset($item->stock['operators'])) {
                    $item_operator_name = $item->stock['operators']['name'];
                    $item_operator_id = $item->stock['operators']['username'];
                }
                $cellData[] = [
                    $key + 1,
                    $item->type['name'],
                    $item->mix_id(),
                    $item_operator_name,
                    $item_operator_id,
                    $item->promoter ? $item->promoter['name'] : '无',
                    $item->promoter ? $item->promoter['mobile'] : '无',
                    $item_owner_name,
                    $item_owner_id,
                    $item->is_bound ? '已使用' : '未使用',
                    $item->is_frozen ? '已冻结' : '未冻结'
                ];
            }
        }
        Excel::create('VIP卡查询', function ($excel) use ($cellData) {
            $excel->sheet('VIP卡查询', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        })->export('xls');
    }
}
