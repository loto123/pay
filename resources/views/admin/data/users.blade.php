<div class="container-fluid">

    <!--查询开始-->
    <div class="box box-primary">
        <div class="box-body">
            <form class="form-horizontal" method="post" action="{{Request::url()}}" pjax-container>
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">用户ID：</label>

                    <div class="col-sm-2">
                        <input type="text" id="aid" name="aid" class="form-control" placeholder="请输入用户ID"
                               value="{{$aid}}">
                    </div>
                    <label class="col-sm-1 control-label">上级代理ID：</label>

                    <div class="col-sm-2">
                        <input type="text" id="parent" name="parent" class="form-control" placeholder="请输入上级代理ID"
                               value="{{$parent}}">
                    </div>
                    <label class="col-sm-1 control-label">上级运营ID：</label>

                    <div class="col-sm-2">
                        <input type="text" id="operator" name="operator" class="form-control" placeholder="请输入上级运营ID"
                               value="{{$operator}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label">身份：</label>

                    <div class="col-sm-2">
                        <select class="form-control" id="role" name="role">
                            <option value="0">全部</option>
                            @foreach($roles as $key => $item)
                                <option value="{{$item->id}}" {{isset($role) && $role == $item->id ? 'selected' : ''}}>{{$item->display_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="col-sm-1 control-label">排序方式：</label>

                    <div class="col-sm-2">
                        <select class="form-control" id="orderby" name="orderby">
                            <option value="trans_amount" {{isset($orderby) && $orderby == 'trans_amount' ? 'selected="selected"' : ''}}>
                                交易总额从多到少
                            </option>
                            <option value="proxy_fee_amount" {{isset($orderby) && $orderby == 'proxy_fee_amount' ? 'selected="selected"' : ''}}>
                                代理业绩从多到少
                            </option>
                            {{--<option value="3" {{isset($orderby) && $orderby == 3 ? 'selected="selected"' : ''}}>已售卡数从多到少</option>--}}
                        </select>
                    </div>
                    <label class="col-sm-1 control-label">起止时间：</label>

                    <div class="col-sm-2">
                        <input type="text" readonly name="date_time" id="reservation" class="form-control"
                               value="{{$date_time}}"/>
                    </div>
                    <span class="col-sm-3">
                        <button type="submit" class="btn btn-primary">查询</button>
                        <button type="button" class="btn btn-primary" onclick="data_export()">导出</button>
                    </span>
                </div>
            </form>
        </div>
    </div>
    <!--查询结束-->
    <div class="row">
        <div class="col-md-6">
            <div class="box-body box">
                <div class="lead">用户总数:<span class="text-yellow">{{$user_count}}</span></div>
                <div class="lead">今日新增:<span class="text-yellow">{{$user_new}}</span></div>
                <div class="lead">推广员总数:<span class="text-yellow">{{$promoter_count}}</span></div>
                <div class="lead">今日新增:<span class="text-yellow">{{$promoter_new}}</span></div>
                <div class="lead">代理总数:<span class="text-yellow">{{$proxy_count}}</span></div>
                <div class="lead">今日新增:<span class="text-yellow">{{$proxy_new}}</span></div>
            </div>
        </div>
    </div>
    <!--表格开始-->
    <div class="box">
        <div class="box-body table-responsive no-padding">
            @if($list && $list->count() > 0)
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>排名</th>
                        <th>用户</th>
                        <th>身份</th>
                        <th>上级代理</th>
                        <th>上级运营</th>
                        <th>交易总额</th>
                        <th>交易笔数</th>
                        <th>拿钻</th>
                        <th>交钻</th>
                        <th>剩余钻石</th>
                        <th>已付平台手续费</th>
                        <th>直属用户数</th>
                        <th>直属代理数</th>
                        <th>代理业绩</th>
                        <th>代理分润收益</th>
                        {{--<th>持有VIP卡数</th>--}}
                        {{--<th>已售VIP卡数</th>--}}
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($list as $key => $item)
                        <tr>
                            <td>{{$key + 1}}</td>
                            <td>
                                <div class="user-panel clearfix">
                                    <div class="pull-left">
                                        <img src="{{$item->avatar}}" width="40" height="40" class="img-circle">
                                    </div>
                                    <div class="pull-left ml7">
                                        <p>{{$item->name}}</p>
                                        <span>ID:<span
                                                    class="text-yellow">{{$item->mobile}}{{$item->status ? '（已封号）' : ''}}</span></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @foreach ($item->roles as $k => $v)
                                    <span class="text-yellow">{{$v->display_name}}</span>
                                @endforeach
                            </td>
                            @if($item->parent)
                                <td>
                                    <div class="user-panel clearfix">
                                        <div class="pull-left">
                                            <img src="{{$item->parent->avatar}}" width="40" height="40"
                                                 class="img-circle">
                                        </div>
                                        <div class="pull-left ml7">
                                            <p>{{$item->parent->name}}</p>
                                            <span>ID:<span class="text-yellow">{{$item->parent->mobile}}{{$item->parent->status ? '（已封号）' : ''}}</span></span>
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td>无</td>
                            @endif
                            @if($item->operator)
                                <td>
                                    <div class="user-panel clearfix">
                                        <div class="pull-left">
                                            <img src="{{$item->operator->avatar}}" width="40" height="40" class="img-circle">
                                        </div>
                                        <div class="pull-left ml7">
                                            <p>{{$item->operator->name}}</p>
                                            <span>ID:<span class="text-yellow">{{$item->operator->username}}{{$item->operator->status ? '（已封号）' : ''}}</span></span>
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td>无</td>
                            @endif
                            <td>
                                {{
                                    $item->transfer_record->sum(function ($list) {
                                        return abs($list['amount']);
                                    })
                                }}
                            </td>
                            {{--<td>{{$item->trans_amount or 0}}</td>--}}
                            <td>{{$item->transfer_record->count()}}</td>
                            <td>{{$item->transfer_record->where('stat',2)->sum('amount')}}</td>
                            <td>{{abs($item->transfer_record->where('stat',1)->sum('amount'))}}</td>
                            <td>{{$item->balance}}</td>
                            <td>{{$item->output_profit->sum('fee_amount')}}</td>
                            <td>{{$item->child_user_count}}</td>
                            <td>{{$item->child_proxy_count}}</td>
                            <td>{{$item->proxy_profit->sum('fee_amount')}}</td>
                            <td>{{$item->proxy_profit->sum('proxy_amount')}}</td>
                            {{--<td>{{$item->proxy_fee_amount or 0}}</td>--}}
                            {{--<td>{{$item->profit_proxy_amount or 0}}</td>--}}
                            {{--<td>持有VIP卡数</td>--}}
                            {{--<td>已售VIP卡数</td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="noData-container">
                    暂无数据
                </div>
            @endif
        </div>
        <!--表格结束-->
        <div>{{ $list->appends(compact('aid', 'date_time', 'parent', 'operator', 'role', 'orderby'))->links() }}</div>
    </div>
</div>
<script type="text/javascript">
    function data_export() {
        var aid = $("#aid").val();
        var parent = $("#parent").val();
        var operator = $("#operator").val();
        var role = $("#role").val();
        var orderby = $("#orderby").val();
        var date_time = $("#reservation").val();
        var form = $("<form></form>");
        form.attr('style', 'display:none');
        form.attr('method', 'post');
        form.attr('action', '/admin/excel/data/user');
        var input1 = $('<input />');
        input1.attr('type', 'hidden');
        input1.attr('name', 'aid');
        input1.val(aid);
        var input2 = $('<input />');
        input2.attr('type', 'hidden');
        input2.attr('name', 'date_time');
        input2.val(date_time);
        var input3 = $('<input />');
        input3.attr('type', 'hidden');
        input3.attr('name', '_token');
        input3.val(LA.token);
        var input4 = $('<input />');
        input4.attr('type', 'hidden');
        input4.attr('name', 'parent');
        input4.val(parent);
        var input5 = $('<input />');
        input5.attr('type', 'hidden');
        input5.attr('name', 'operator');
        input5.val(operator);
        var input6 = $('<input />');
        input6.attr('type', 'hidden');
        input6.attr('name', 'role');
        input6.val(role);
        var input7 = $('<input />');
        input7.attr('type', 'hidden');
        input7.attr('name', 'orderby');
        input7.val(orderby);
        $('body').append(form);
        form.append(input1);
        form.append(input2);
        form.append(input3);
        form.append(input4);
        form.append(input5);
        form.append(input6);
        form.append(input7);
        form.submit();
        form.remove();
    }

    $(document).ready(function () {
        $('#reservation').daterangepicker(null, function (start, end, label) {
        });
    });
</script>
