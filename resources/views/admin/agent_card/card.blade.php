<div class="container-fluid">
    <!--查询开始-->

    @if(session('status'))
        <div class="alert alert-info"> {{session('status')}}
        </div>
    @endif

    <div class="box box-primary">
        <div class="box-header">
            <form class="form-horizontal" method="post" action="/admin/agent_card/cards">
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">VIP卡号：</label>
                    <div class="col-sm-2">
                        <input type="text" name="card_id" id="card_id" class="form-control" placeholder="请输入卡号"
                               value="{{$card_id??''}}">
                    </div>

                    <label class="col-sm-1 control-label">用卡人ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="agent_id" id="agent_id" class="form-control" placeholder="请输入代理ID"
                               value="{{$agent_id??''}}">
                    </div>

                    <label class="col-sm-1 control-label">运营ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="operator_id" id="operator_id" class="form-control"
                               placeholder="请输入运营ID"
                               value="{{$operator_id??''}}">
                    </div>

                    <label class="col-sm-1 control-label">推广员ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="promoter_id" id="promoter_id" class="form-control"
                               placeholder="请输入推广员ID"
                               value="{{$promoter_id??''}}">
                    </div>
                    <label class="col-sm-1 control-label">使用状态：</label>
                    <div class="col-sm-2">
                        <select name="is_bound" id="is_bound">
                                <option value="">所有</option>
                            <option value="{{\App\Agent\Card::BOUND}}"
                                    @if (isset($is_bound) && $is_bound == \App\Agent\Card::BOUND)
                                    selected="selected"
                                    @endif
                            >已使用
                            </option>
                            <option value="{{\App\Agent\Card::UNBOUND}}"
                                    @if (isset($is_bound) && $is_bound == \App\Agent\Card::UNBOUND)
                                    selected="selected"
                                    @endif
                            >未使用
                            </option>
                            </select>
                    </div>
                    <label class="col-sm-1 control-label">是否冻结：</label>
                    <div class="col-sm-2">
                        <select name="is_frozen" id="is_frozen">
                            <option value="">所有</option>
                            <option value="{{\App\Agent\Card::FROZEN}}"
                                    @if (isset($is_frozen) && $is_frozen == \App\Agent\Card::FROZEN)
                                    selected="selected"
                                    @endif>已冻结
                            </option>
                            <option value="{{\App\Agent\Card::UNFROZEN}}"
                                    @if (isset($is_frozen) && $is_frozen == \App\Agent\Card::UNFROZEN)
                                    selected="selected"
                                    @endif>未冻结
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-1 control-label">起止时间：</label>
                    <div class="col-sm-2">
                        <input type="text" readonly name="date_time" id="reservation" class="form-control"
                               value="{{$date_time??''}}"/>
                    </div>
                    <span class="col-sm-2">
                        <button type="submit" class="btn btn-primary">查询</button>
                        <button type="button" class="btn btn-primary" onclick="data_export()">导出</button>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div class="box">
        <!--店铺统计表格开始-->
        <div class="box-body table-responsive no-padding">
            @if(isset($list) && !$list->isEmpty())
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>序号</th>
                        <th>卡号</th>
                        <th>运营</th>
                        <th>推广员</th>
                        <th>用卡人</th>
                        <th>状态</th>
                        <th>是否冻结</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($list as $key => $item)
                        <tr>
                            <td>{{$offset+$key+1}}</td>
                            <td>{{$item->mix_id()}}</td>
                            <td>
                                @if($promoter = $item->stock['operators'])
                                    <div class="user-panel clearfix">
                                        <div class="pull-left">
                                            <img src="{{$promoter['avatar']}}" width="40" height="40"
                                                 class="img-circle">
                                        </div>
                                        <div class="pull-left ml7">
                                            <p>{{$promoter['name']}}</p>
                                            <span>ID:<span>{{$promoter['username']}}</span></span>
                                        </div>
                                    </div>
                                @else
                                    无
                                @endif
                            </td>
                            <td>
                                @if($promoter = $item->promoter)
                                    <div class="user-panel clearfix">
                                        <div class="pull-left">
                                            <img src="{{$promoter['avatar']}}" width="40" height="40"
                                                 class="img-circle">
                                        </div>
                                        <div class="pull-left ml7">
                                            <p>{{$promoter['name']}}</p>
                                            <span>ID:<span>{{$promoter['mobile']}}</span></span>
                                        </div>
                                    </div>
                                @else
                                    无
                                @endif
                            </td>
                            <td>
                                @if($item->is_bound == $item::BOUND  && $owner = $item->owner_user)
                                    <div class="user-panel clearfix">
                                        <div class="pull-left">
                                            <img src="{{$owner['avatar']}}" width="40" height="40" class="img-circle">
                                        </div>
                                        <div class="pull-left ml7">
                                            <p>{{$owner['name']}}</p>
                                            <span>ID:<span>{{$owner['mobile']}}</span></span>
                                        </div>
                                    </div>
                                @else
                                    无
                                @endif
                            </td>
                            <td>{{$item->is_bound?'已使用':'未使用'}}</td>
                            <td>{{$item->is_frozen?'已冻结':'未冻结'}}</td>
                            <td>
                                <button><a href="card_trace/{{$item->mix_id()}}">流转记录</a></button>
                                @if($item->is_frozen==$item::UNFROZEN)
                                <button><a href="updates_card/{{$item->mix_id()}}">冻结</a></button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data text-muted text-center" style="font-size:24px;margin-top:20px;">暂无数据</p>
            @endif
        </div>
        <div>{{ $list->appends(compact('card_id','agent_id','operator_id','promoter_id',
            'is_bound','is_frozen','date_time'))->links() }}</div>
    </div>
</div>

<script language="JavaScript">
    function data_export() {
        var card_id = $("#card_id").val();
        var agent_id = $("#agent_id").val();
        var operator_id = $("#operator_id").val();
        var promoter_id = $("#promoter_id").val();
        var is_bound = $("#is_bound").val();
        var is_frozen = $("#is_frozen").val();
        var date_time = $("#date_time").val();
        var form = $("<form></form>");
        form.attr('style', 'display:none');
        form.attr('method', 'post');
        form.attr('action', '/admin/excel/cards');
        var input1 = $('<input />');
        input1.attr('type', 'hidden');
        input1.attr('name', 'card_id');
        input1.val(card_id);
        var input2 = $('<input />');
        input2.attr('type', 'hidden');
        input2.attr('name', 'agent_id');
        input2.val(agent_id);
        var input3 = $('<input />');
        input3.attr('type', 'hidden');
        input3.attr('name', '_token');
        input3.val(LA.token);
        var input4 = $('<input />');
        input4.attr('type', 'hidden');
        input4.attr('name', 'operator_id');
        input4.val(operator_id);
        var input5 = $('<input />');
        input5.attr('type', 'hidden');
        input5.attr('name', 'promoter_id');
        input5.val(promoter_id);
        var input6 = $('<input />');
        input6.attr('type', 'date_time');
        input6.attr('name', 'date_time');
        input6.val(date_time);
        var input7 = $('<input />');
        input7.attr('type', 'hidden');
        input7.attr('name', 'is_bound');
        input7.val(is_bound);
        var input8 = $('<input />');
        input8.attr('type', 'hidden');
        input8.attr('name', 'is_frozen');
        input8.val(is_frozen);
        $('body').append(form);
        form.append(input1);
        form.append(input2);
        form.append(input3);
        form.append(input4);
        form.append(input5);
        form.append(input6);
        form.append(input7);
        form.append(input8);
        form.submit();
        form.remove();
    }

    $(document).ready(function () {
        $('#reservation').daterangepicker(null, function (start, end, label) {
        });
    });
</script>
